<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.09.19
 * Time: 17:18
 */

namespace app\commands;

use app\common\components\visitSearchDelete\VisitSearchDeleteByServiceNumber;
use app\common\models\VisitStatus;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\TmpPetOwners;
use app\models\db\TmpPets;
use app\models\db\Visits;
use app\models\db\VisitPets;
use app\modules\admin\helpers\VisitStatusHelper;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class CleanVisitsController extends Controller
{
    /**
     * @var bool Не спрашивать подтверждения
     */
    public $force;

    /**
     * @var bool Подавить вывод
     */
    public $quiet;

    public function actionDeleteVisit($service_number)
    {
        $visit = new VisitSearchDeleteByServiceNumber();
        $visit->search($service_number);
        echo Console::prompt("Удалить запись? (Осторожно! Действие нельзя отменить) [Y/n]", [
            'required' => true,
            'validator' => function ($input, &$error) use ($visit, $service_number) {
                $validYes = ['Y', 'y', 'Д', 'д', '+', '1', 'да', 'yes'];
                $validNo = ['n', 'н', '-', '0', 'нет', 'no'];
                if (in_array($input, $validYes, $strict = TRUE)) {
                    $visit->delete($service_number);
                    Console::output(Console::ansiFormat("Запись успешно удалена", [
                        Console::FG_GREEN, Console::BOLD
                    ]));
                    return true;
                } else if (in_array($input, $validNo, $strict = TRUE)) {
                    return true;
                } else {
                    $error = 'Некорректный ответ';
                    return false;
                }
            }
        ]);
    }

    /**
     * Удаляет не взятые в работу приёмы, созданные для неавторизованных пользователей mos.ru
     *
     * @param string $age Возраст приёмов, подлежащих удалению. Примеры
     * '10 days'
     * '1 week 1 day'
     * 'P1M'            // 1 month
     * 'P10D3H'         // 10 days 3 hours
     */
    public function actionDeleteOldVisitsForUnauthMosruClients(string $age = null)
    {
        $options = $this->getOptionValues($this->action->id);

        if (null === $age) {
            $age = ArrayHelper::getValue(\Yii::$app->params, 'deleteOldVisitsForUnauthMosruClients.visitAge', null);
            if (null === $age) {
                $this->error('Не указан возраст приёмов для удаления');
                return false;
            }
        }
        if (!$interval = \DateInterval::createFromDateString($age)) {
            $this->error("Не могу обработать значение возраста: ${age}");
            return false;
        }

        $ageAgo = (new \DateTimeImmutable())->sub($interval);
        $petTable = Pets::tableName();
        $query = Visits::find()
            ->alias('v')
            ->select([
                'v.id',
                'v.id_pet',
                'v.id_owner',
                'p.id_pet_tmp',
                'o.id_pet_owner_tmp',
            ])
            ->joinWith(['owner o'], false)
            // Что-то странное с этой связью при записи с mos.ru: таблица VisitsPets не используется
            ->leftJoin("{$petTable} p", 'v.id_pet = p.id')
            ->where([
                'and',
                //['v.is_for_unauth_client' => true],
                [
                    'or',
                    ['v.status' => VisitStatus::CANCELED],
                    [
                        'and',
                        ['v.status' => VisitStatus::NEW],
                        ['<', 'v.created_at', $ageAgo->format('Y-m-d H:i:s')],
                    ],
                ],
            ])
            ->andWhere(['not', ['v.id_owner' => null]])
        ;
        $visits = $query->asArray()->all();

        if (!$options['quiet']) {
            Console::output('Приёмов найдено: ' . count($visits));
        }
        if (!count($visits)) {
            return true;
        }

        if (!$options['force']) {
            $proceed = Console::confirm('Удалить приёмы и связанные с ними данные владельцев и питомцев? (Осторожно! Действие нельзя отменить) [y/N]', false);
            if (!$proceed) {
                return true;
            }
        }

        $deleted['visits'] = 0;
        $deleted['owners'] = 0;
        $deleted['tmpOwners'] = 0;
        $deleted['pets'] = 0;
        $deleted['tmpPets'] = 0;
        try {
            \Yii::$app->db->transaction(function () use ($visits, &$deleted) {
                // 1. Обновляем Приёмы
                $visitsIds = array_map(function ($v) { return $v['id']; }, $visits);
                //$deleted['visits'] = Visits::deleteAll(['id' => $visitsIds]);

                Visits::updateAll(['status' => VisitStatus::CANCELED, 'id_pet' => null, 'id_owner' => null], ['IN', 'id', $visitsIds]);
                VisitPets::updateAll(['id_pet' => null], ['IN', 'id_visit', $visitsIds]);

                // 2. Удаляем Питомцев
                $petsIds = array_map(function ($v) { return $v['id_pet']; }, $visits);
                $deleted['pets'] = Pets::deleteAll(['id' => $petsIds]);
                $tmpPetsIds = array_map(function ($v) { return $v['id_pet_tmp']; }, $visits);
                $deleted['tmpPets'] = TmpPets::deleteAll(['id' => $tmpPetsIds]);

                // 3. Удаляем Владельцев
                $ownersIds = array_map(function ($v) { return $v['id_owner']; }, $visits);
                $deleted['owners'] = PetOwners::deleteAll(['id' => $ownersIds]);
                $tmpOwnersIds = array_map(function ($v) { return $v['id_pet_owner_tmp']; }, $visits);
                $deleted['tmpOwners'] = TmpPetOwners::deleteAll(['id' => $tmpOwnersIds]);
            });
        } catch (\Throwable $e) {
            $this->error("Ошибка при удалении: {$e}");

            return false;
        }

        if (!$options['quiet']) {
            Console::output('Удалены:');
            Console::output(join(PHP_EOL, [
                "Приёмы: {$deleted['visits']}",
                "Владельцы (/вр.): {$deleted['owners']}/{$deleted['tmpOwners']}",
                "Питомцы (/вр.): {$deleted['pets']}/{$deleted['tmpPets']}",
            ]));
        }

        return true;
    }

    public function actionFinishOldVisits(string $age = null)
    {
        $options = $this->getOptionValues($this->action->id);

        if (null === $age) {
            $age = ArrayHelper::getValue(\Yii::$app->params, 'deleteOldVisitsForUnauthMosruClients.visitAge', null);
            if (null === $age) {
                $this->error('Не указан возраст приёмов для удаления');
                return false;
            }
        }
        //TODO: новый интервал
        if (!$interval = \DateInterval::createFromDateString($age)) {
            $this->error("Не могу обработать значение возраста: ${age}");
            return false;
        }

        $ageAgo = (new \DateTimeImmutable())->sub($interval);
        $query = Visits::find()
            ->alias('v')
            ->select([
                'v.id',
            ])
            ->where([
                'and',
                ['v.status' => VisitStatus::IN_WORK],
                [
                    'and',
                    ['<', 'v.created_at', $ageAgo->format('Y-m-d H:i:s')],
                ],
            ])
        ;
        $visits = $query->asArray()->all();

        if (!$options['quiet']) {
            Console::output('Приёмов найдено: ' . count($visits));
        }
        if (!count($visits)) {
            return true;
        }

        if (!$options['force']) {
            $proceed = Console::confirm('Завершить старые приёмы взятые в работу? (Осторожно! Действие нельзя отменить) [y/N]', false);
            if (!$proceed) {
                return true;
            }
        }

        $updated['visits'] = 0;

        try {
            \Yii::$app->db->transaction(function () use ($visits, &$deleted) {
                $visitsIds = array_map(function ($v) { return $v['id']; }, $visits);

                $updated['visits'] = Visits::updateAll(['status' => VisitStatus::FINISHED], ['IN', 'id', $visitsIds]);
            });
        } catch (\Throwable $e) {
            $this->error("Ошибка при сохранении: {$e}");

            return false;
        }

        if (!$options['quiet']) {
            // Console::output('Закрыты:');
            Console::output(join(PHP_EOL, [
                //"Приёмы: {$updated['visits']}",
                "Приёмы завершены",
            ]));
        }

        return true;
    }

    // Проставить новым приёмам(не взятым в работу) за прошедшие дни - неявка по инициативе владельца
    public function actionSetTimeoutOldVisits()
    {
        $age1 = date('Y-m-d H:i:s');
        $age2 = date('Y-m-d H:i:s', strtotime($age1) + 1);

        $query = Visits::find()
            ->alias('v')
            ->select(['v.id'])
            ->where(['v.status' => VisitStatus::NEW])
            ->andWhere('time_range  < tsrange(\'' . $age1 . '\', \'' . $age2 . '\')');

        $visits = $query->asArray()->all();

        $updated['visits'] = 0;

        try {
            \Yii::$app->db->transaction(function () use ($visits, &$updated) {
                $visitsIds = array_map(function ($v) { return $v['id']; }, $visits);

                $updated['visits'] = Visits::updateAll([
                    'status' => VisitStatus::TIMEOUT,
                    'cancel_initiator' => 'OWNER',
                    'change_reason' => 'ABSENSE',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => '0'
                ], ['IN', 'id', $visitsIds]);
            });
        } catch (\Throwable $e) {
            $this->error("Ошибка при сохранении: {$e}");
            return false;
        }

        Console::output(join(PHP_EOL, [
            "Приёмы: {$updated['visits']}",
            "Приёмы завершены",
        ]));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function options($actionId)
    {
        if ('delete-old-visits-for-unauth-mosru-clients' == $actionId) {
            return [
                'help',
                'force',
                'quiet',
            ];
        }else if('finish-old-visits' == $actionId) {
            return [
                'help',
                'force',
                'quiet',
            ];
        }
    }

    private function error($err, $quiet = false)
    {
        if ($quiet) {
            \Yii::error($err, $this->getroute());
        } else {
            Console::error($err);
        }
        return false;
    }
}
