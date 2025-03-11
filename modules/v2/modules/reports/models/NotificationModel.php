<?php

namespace app\modules\v2\modules\reports\models;

use app\common\components\inform\events\ReadyResearchEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\SpkService;
use app\common\components\inform\SubscriptionService;
use app\models\db\ContactTypes;
use app\models\db\Files;
use app\models\db\Reports;
use app\models\db\Visits;
use app\models\ReportsHelper;
use yii\web\BadRequestHttpException;

class NotificationModel
{
    /**
     * @param $id_visit
     * @return array
     * @throws BadRequestHttpException
     * @throws \app\common\components\inform\InformException
     */
    public function check($id_visit)
    {
        $visit = $this->getVisit($id_visit);
        if (!$visit->owner->hasSubscriptions()) {
            return [
                'available' => false,
                'reason' => 'Владелец не подписан на уведомления'
            ];
        }
        if (!$this->visitHasFileReports($visit)) {
            return [
                'available' => false,
                'reason' => 'В данном приеме нет сохраненных отчетов для услуг типа "Лабораторные исследования"'
            ];
        }

        return [
            'available' => true
        ];
    }

    /**
     * @param $id_visit
     * @return bool
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function send($id_visit)
    {
        $visit = $this->getVisit($id_visit);
        if (!$visit->owner->hasSubscriptions()) {
            throw new BadRequestHttpException('Владелец не подписан на уведомления');
        }

        if (!$this->visitHasFileReports($visit)) {
            throw new BadRequestHttpException('В данном приеме нет сохраненных отчетов для услуг типа "Лабораторные исследования"');
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($visit->owner);
        /** @var SpkService $spkService */
        $spkService = \Yii::$app->spkService;
        $reports = $this->getReports($visit);
        $researchTypes = [];
        $params = [
            'visit' => $visit,
            'contacts' => $contacts,
            'id_visit' => $visit->id,
        ];
        foreach ($reports as $report) {
            if (!$reportName = ReportsHelper::getAttribute($report['cod'])) {
                //throw new BadRequestHttpException('Неизвестный тип отчета для отправки');
                continue;
            }

            if (isset($params[$reportName])) {
                continue;
            }
            $researchTypes[] = (ReportsHelper::getName($report['cod'])) ?: $report['name'];
            $params[$reportName] = $spkService->makeFileUrl($report['path']);
        }
        $params['researchTypes'] = implode(', ', $researchTypes);

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new ReadyResearchEvent($params));

        return true;
    }

    /**
     * @param $id
     * @return Visits
     * @throws BadRequestHttpException
     */
    protected function getVisit($id)
    {
        if (!$visit = Visits::findOne(['id' => $id])) {
            throw new BadRequestHttpException("Прием не найден");
        }

        return $visit;
    }

    /**
     * @param Visits $visit
     * @return bool
     */
    protected function visitHasFileReports(Visits $visit)
    {
        return $this->getQuery($visit->id)
            ->exists();
    }

    /**
     * @param Visits $visit
     * @return array
     */
    protected function getReports(Visits $visit)
    {
        return $this->getQuery($visit->id)
            ->asArray()
            ->all();
    }

    /**
     * @param $id
     * @return \yii\db\ActiveQuery
     */
    protected function getQuery($id)
    {
        $sql = <<<SQL
select distinct
    files.id, files.path, files.entity_id, files.entity_type, visits_gov_services.id_visit, gov_services.name, gov_services.cod, files.created_at
from files
join visits_gov_services on visits_gov_services.id = files.entity_id
join gov_services on gov_services.id = visits_gov_services.id_service
join gov_services_reports on gov_services_reports.id_service = visits_gov_services.id_service
join reports on reports.id = gov_services_reports.id_report
where 
    visits_gov_services.id_visit = :id_visit and 
    files.entity_type = 'visits_gov_service'
    and reports.sending = true
order by files.created_at desc nulls last
SQL;
        return Files::findBySql($sql, ['id_visit' => $id]);
    }
}
