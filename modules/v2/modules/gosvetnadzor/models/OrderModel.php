<?php

namespace app\modules\v2\modules\gosvetnadzor\models;

use app\common\components\inform\events\PrimaryViolationOrderEvent;
use app\common\components\inform\events\SecondaryViolationOrderEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\events\PrimaryArvEvent;
use app\common\components\inform\events\SecondaryArvEvent;
use app\common\components\inform\SubscriptionService;
use app\models\db\ContactTypes;
use app\models\db\Order;
use app\models\db\OrderType;
use app\models\db\Violation;
use app\models\db\ViolationToARV;
use yii\web\BadRequestHttpException;

class OrderModel
{
    /**
     * @param int $id_violation
     * @param string $number
     * @param string $date_to
     * @return Order|array
     * @throws BadRequestHttpException
     */
    public function create(int $id_violation, string $number, string $date_order, string $date_to)
    {
        /** @var Violation $violation */
        if (!$violation = Violation::find()->where(['id_violation' => $id_violation])->one()) {
            throw new BadRequestHttpException('Не найдено нарушения с указанным номером');
        }
        if ($violation->state !== Violation::STATE_IN_WORK) {
            throw new BadRequestHttpException("Невозможно создать предписания для нарушение не в статусе 'В работе'");
        }
        // В случае если уже существует Предписание с таким номером - передаём его id фронту для перехода на карточку
        // соответствующего Предписания
        /** @var Order $order */
        if ($order = Order::find()->where(['number' => $number])->one()) {
            return ['id_violation' => $order->violation->id_violation];
        }
        if (Order::find()->where("id_violation = ".$id_violation." AND date_to >= now()")->count() > 0) {
            throw new BadRequestHttpException("Невозможно создать повторное предписание до истечения предшествующего");
        }

        $order = new Order();

        $order->id_violation = $id_violation;
        $order->number = $number;
        $order->date_order = $date_order;
        $order->date_to = $date_to;

        $type = Order::find()->where(['id_violation' => $id_violation])->count() > 0
            ? OrderType::TYPE_SECONDARY
            : OrderType::TYPE_PRIMARY;

        $order->id_type = OrderType::find()->where(['name' => $type])->one()->id_type;

        if (!$order->save()) {
            $errors = $order->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании предписания' : implode("\n", array_unique(array_values($errors))));
        }

        return $order;
    }

    /**
     * @param string $number
     * @param int $id_ARV
     * @param string $date_ARV
     * @return ViolationToARV
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function makeARV(string $number, int $id_ARV, string $date_ARV)
    {
        /** @var Order $order */
        if (!$order = Order::find()->where(['number' => $number])->one()) {
            throw new BadRequestHttpException('Не найдено предписание с указанным номером');
        }
        // TODO: временно убираем проверку YII_DEBUG окружения, чтобы позволить выставлять АПН тем же днём, что и предписание
        if (!YII_DEBUG && strtotime($order->date_to) > strtotime((new \DateTime())->format('Y-m-d'))) {
            throw new BadRequestHttpException('Невозможно создать АПН для не истёкшего предписания');
        }
        /** @var Violation $violation */
        $violation = Violation::find()->where(['id_violation' => $order->id_violation])->one();
        // Для АПН по вторичному предписанию - установим значение из Нарушения, вместо передаваемого фронтом
        if ($violation->id_ARV) {
            $id_ARV = $violation->id_ARV;
        }

        $violationToARV = new ViolationToARV();

        $violationToARV->id_violation = $violation->id_violation;
        $violationToARV->id_ARV = $id_ARV;
        $violationToARV->number = $number;
        $violationToARV->date_ARV = $date_ARV;
        $violationToARV->id_order = $order->id_order;
        $violationToARV->created_by = \Yii::$app->user->id;

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($violationToARV->save()) {
                if (!$violation->id_ARV) {
                    $violation->id_ARV = $id_ARV;
                    $violation->save();
                }
                $transaction->commit();
            } else {
                $errors = $violationToARV->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании АПН' : implode("\n", array_unique(array_values($errors))));
            }
        } catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }

        return $violationToARV;
    }

    /**
     * @param $id_order
     * @param $fileIds
     * @return bool
     * @throws BadRequestHttpException
     */
    public function notifyOrder($id_order, $fileIds) {
        /** @var Order $order */
        if (!$order = Order::find()->where(['id_order' => $id_order])->one()) {
            throw new BadRequestHttpException('Не найдено предписание с указанным номером');
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($order->violation->owner, [ ContactTypes::TYPE_EMAIL ]);
        $type = $order->order_type->name;
        if (!empty($contacts)) {
            $notificationHistory = $this->logViolationHistory($contacts, $order->violation, 'предписании', $order->order_type->name);
            if ($type === OrderType::TYPE_PRIMARY) {
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new PrimaryViolationOrderEvent([
                    'contacts' => $contacts,
                    'violation' => $order->violation,
                    'id_visit' => $order->violation->id_visit,
                    'id_pet' => $order->violation->id_pet,
                    'order' => $order,
                    'author' => \Yii::$app->user->identity,
                    'files_token' => $fileIds ? $this->generateFilesToken($order->id_violation) : null,
                    'attached_files_ids' => $fileIds,
                    'notification_history' => $notificationHistory,
                ]));
            }
            if ($type === OrderType::TYPE_SECONDARY) {
                $primaryOrder = Order::find()->where(['id_violation' => $order->id_violation, 'id_type' => 1])->one();
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new SecondaryViolationOrderEvent([
                    'contacts' => $contacts,
                    'violation' => $order->violation,
                    'id_visit' => $order->violation->id_visit,
                    'id_pet' => $order->violation->id_pet,
                    'order' => $order,
                    'primaryOrder' => $primaryOrder,
                    'author' => \Yii::$app->user->identity,
                    'files_token' => $fileIds ? $this->generateFilesToken($order->id_violation) : null,
                    'attached_files_ids' => $fileIds,
                    'notification_history' => $notificationHistory,
                ]));
            }
            return true;
        }
        throw new BadRequestHttpException('У владельца животного отсутствуют контакты');
    }

    /**
     * @param $id_arv
     * @param $text
     * @param $fileIds
     * @return bool
     * @throws BadRequestHttpException
     */
    public function notifyArv($id_arv, $text, $fileIds)
    {
        /** @var ViolationToARV $arv */
        if (!$arv = ViolationToARV::find()->where(['id' => $id_arv])->one()) {
            throw new BadRequestHttpException('Не найдено АПН с указанным номером');
        }
        $contacts = SubscriptionService::getOwnerSubscriptions($arv->violation->owner, [ ContactTypes::TYPE_EMAIL ]);
        $type = $arv->order->order_type->name;
        if (!empty($contacts)) {
            $notificationHistory = $this->logViolationHistory($contacts, $arv->violation, 'АПН', $arv->order->order_type->name);
            if ($type === OrderType::TYPE_PRIMARY) {
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new PrimaryArvEvent([
                    'contacts' => $contacts,
                    'violation' => $arv->violation,
                    'author' => \Yii::$app->user->identity,
                    'arv' => $arv,
                    'text' => $text,
                    'files_token' => $fileIds ? $this->generateFilesToken($arv->id_violation) : null,
                    'attached_files_ids' => $fileIds,
                    'id_pet' => $arv->violation->id_pet,
                    'id_visit' => $arv->violation->id_visit,
                    'notification_history' => $notificationHistory,
                ]));
            }
            if ($type === OrderType::TYPE_SECONDARY) {
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new SecondaryArvEvent([
                    'contacts' => $contacts,
                    'violation' => $arv->violation,
                    'author' => \Yii::$app->user->identity,
                    'arv' => $arv,
                    'text' => $text,
                    'files_token' => $fileIds ? $this->generateFilesToken($arv->id_violation) : null,
                    'attached_files_ids' => $fileIds,
                    'id_pet' => $arv->violation->id_pet,
                    'id_visit' => $arv->violation->id_visit,
                    'notification_history' => $notificationHistory,
                ]));
            }
            return true;
        }
        throw new BadRequestHttpException('У владельца животного отсутствуют контакты');
    }

    /**
     * @param integer $id_violation
     * @return string
     */
    private function generateFilesToken($id_violation)
    {
        return md5($id_violation . microtime());
    }

    private function logViolationHistory(array $contacts, Violation $violation, $notificationType, $type)
    {
        $type = $type === OrderType::TYPE_PRIMARY ? 'первичном' : 'вторичном';

        $notificationHistory = [];

        foreach ($contacts as $contact) {
            switch ($contact->contact_type->type) {
                case ContactTypes::TYPE_PHONE:
                    $description = "Владельцу отправлено уведомление о $type $notificationType на телефон:" . $contact->name;
                    break;

                case ContactTypes::TYPE_EMAIL:
                    $description = "Владельцу отправлено уведомление о $type $notificationType на email: " . $contact->name;
                    break;

                default:
                    continue;
            }

            $violationHistory = (new ViolationHistoryModel)
                ->addRecordAboutSendNotify($violation, $description);

            $notificationHistory[$contact->id] = $violationHistory->id_change;
        }

        return $notificationHistory;
    }
}
