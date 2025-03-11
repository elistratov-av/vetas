<?php

namespace app\commands;

use app\common\components\inform\events\NewsletterReceptionEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\models\NewsletterInfoStatus;
use app\common\models\VisitStatus;
use app\models\db\Contacts;
use app\models\db\NewsletterInfo;
use app\models\db\NewsletterReception;
use app\models\db\ServiceTypes;
use app\models\db\ShiftType;
use app\models\db\Visits;
use app\modules\v2\modules\newsletter\models\NewsletterInfoModel;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Command;
use yii\db\Exception;
use yii\db\Query;

class NotificationController extends Controller
{
    const BEFORE_VISIT_NOTIFY_RANGE = 15;
    const VETERINARIAN_TITLE = 'Телеветеринария';

    //за минуту до начала
    public function actionNotifyUpcomingVisit()
    {
        $visits = $this->getVisitsStartsInMinutes();
        $statement = $this->prepareStatement();

        foreach ($visits as $visit) {
            if (count($visit->services) === 0) {
                continue;
            }
            $service = $visit->services[0];
            $link = Visits::TELEVET_SPECIALIST_LINK . $visit->guid_video;

            $content = "Подключитесь по ссылке $link для оказания услуги $service->name";
            echo 'send';
            $this->execStatement($statement, [
                ':title' => self::VETERINARIAN_TITLE,
                ':text' => $content,
                ':id_visit' => $visit->id,
                ':id_user' => $visit->getSpecialists()->all()[0]->id_user,
                ':created_by' => null,
                ':created_at' => date("Y-m-d H:i:s")
            ]);
        }
    }

    // за 15 минут до начала
    public function actionNotifyAboutToStartVisit()
    {
        $visits = $this->getVisitsStartsInMinutes(self::BEFORE_VISIT_NOTIFY_RANGE);
        $statement = $this->prepareStatement();

        /** @var Visits $visit */
        foreach ($visits as $visit) {
            if (count($visit->services) === 0) {
                continue;
            }

            $service = $visit->services[0];
            $visitDateTimeString = substr($visit->time_range, 2, 19);
            $visitTimeString = substr($visitDateTimeString, 11, 5);
            $content = "В $visitTimeString запланировано оказание услуги $service->name";
            $this->execStatement($statement, [
                ':title' => self::VETERINARIAN_TITLE,
                ':text' => $content,
                ':id_visit' => $visit->id,
                ':id_user' => $visit->getSpecialists()->all()[0]->id_user,
                ':created_by' => null,
                ':created_at' => date("Y-m-d H:i:s")
            ]);
        }
    }

    /**
     * @return int
     */
    public function actionNewsletterInfo(): int
    {
        $now = date('Y-m-d H:i:s');
        $newsletterInfoModel = new NewsletterInfoModel();

        $newsletters = NewsletterInfo::find()
            ->onCondition('(mailing_date < \'' . $now . '\')')
            ->andWhere(['status' => NewsletterInfoStatus::NOT_SENT])
            ->all();

        foreach ($newsletters as $newsletter) {
            $newsletterInfoModel->send($newsletter);
        }

        return ExitCode::OK;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function actionNewsletterReception(): int
    {
        $today = date('Y-m-d H:i:s');
        $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));

        $newsLetterReceptions = NewsletterReception::find()->where(['status' => true])->all();

        foreach ($newsLetterReceptions as $newsLetterReception) {

            if (!$organization = $newsLetterReception->getOrganization()->one()) {
                continue;
            }

            $visits = Visits::find()
                ->onCondition('((LOWER(time_range)::date) BETWEEN \'' . $today . '\' AND \'' . $tomorrow . '\')')
                ->andWhere(['id_organization' => $organization->id])
                ->andWhere(['status' => VisitStatus::NEW])
                ->all();

            foreach ($visits as $visit) {
                if ((new Query())
                    ->from('notifications')
                    ->where(['id_visit' => $visit->id])->count()) {
                    continue;
                }

                \Yii::$app->db->createCommand()->insert('public.notifications', [
                    'id_visit' => $visit->id,
                    'id_user' => 0,
                ])->execute();

                $contacts = Contacts::find()
                    ->where(['contacts.entity_type' => 'pet_owner'])
                    ->andWhere(['contacts.entity_id' => $visit->id_owner])
                    ->all();
                if (!empty($contacts)) {
                    \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new NewsletterReceptionEvent([
                        'visit' => $visit,
                        'contacts' => $contacts,
                        'newsletterReception' => $newsLetterReception,
                    ]));
                }
            }
        }

        return ExitCode::OK;
    }

    private function getVisitsStartsInMinutes($minutes = 0): array
    {
        $from = new \DateTime();
        $from->modify("+$minutes minutes");

        $to = clone $from;
        $to->modify("+1 minutes");

        /** @var Visits $visit */
        $visits = Visits::find()
            ->leftJoin('visits_specialists vs', 'vs.id_visit = visits.id')
            ->leftJoin('visits_gov_services vgs', 'vgs.id_visit = visits.id')
            ->leftJoin('gov_services gs', 'gs.id = vgs.id_service')
            ->leftJoin('shift_type st', 'st.id = visits.channel')
            ->andWhere(['visits.status' => [VisitStatus::NEW, VisitStatus::CHANGED]])
            ->andWhere(['gs.id_service_type' => ServiceTypes::TYPE_TELE_VETERINARY])
            ->andWhere(['>', 'lower(visits.time_range)', $from->format('Y-m-d H:i:s')])
            ->andWhere(['<', 'lower(visits.time_range)', $to->format('Y-m-d H:i:s')])
            ->andWhere(['st.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])
            ->orderBy(['lower(visits.time_range)' => SORT_DESC])
            ->all();

        if (!$visits) {
            return [];
        }

        return $visits;
    }


    private function execStatement($statement, $values)
    {
        try {
            $statement->bindValues($values)->execute();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \Yii::error("Error while create veterinarian notify: '$message'");
        }
    }

    private function prepareStatement(): Command
    {
        return \Yii::$app->db->createCommand('
            INSERT into public.notifications
                (title, 
                 text,
                 id_visit,
                 id_user,
                 created_by,
                 created_at)
            VALUES (:title,
                    :text,
                    :id_visit,
                    :id_user,
                    :created_by,
                    :created_at)'
        );
    }
}
