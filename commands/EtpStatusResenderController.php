<?php

namespace app\commands;

use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status1050_1;
use app\modules\soap\models\etp\status\Status116999_1;
use app\modules\soap\models\etp\status\Status8021_1;
use app\modules\soap\models\etp\status\Status8021_2;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\di\Instance;

class EtpStatusResenderController extends Controller
{

    public function actionIndex()
    {

        $visits = Yii::$app->db->createCommand("WITH statuses AS (SELECT visit_id, id, etp_status,
        ROW_NUMBER() OVER (PARTITION BY visit_id ORDER BY id DESC) AS rn
        FROM etp.status_log),
        final_statuses AS (SELECT id, visit_id, etp_status FROM statuses WHERE rn = 1 )
        SELECT v.id, fs.etp_status AS status, v.created_at FROM public.visits AS v
        LEFT JOIN final_statuses AS fs ON fs.visit_id = v.id
        WHERE v.status IN ('N', 'W', 'T', 'C') AND fs.etp_status IN ('1050', '8021', '10090', '1169', '10190', '10091', '10191') ORDER BY v.id DESC")->queryAll();
        $queue = Yii::$app->get('soap_queue_v2');
        foreach ($visits as $visit) {
            switch ($visit['etp_status']) {
                case '1050':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status1050_1::CODE]]));
                    break;
                case '10091':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status1050_1::CODE]]));
                    break;
                case '10191':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status8021_2::CODE]]));
                    break;
                case '8021':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status8021_1::CODE]]));
                    break;
                case '10090':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status1050_1::CODE]]));
                    break;
                case '1169':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status116999_1::CODE]]));
                    break;
                case '10190':
                    $queue->push(json_encode(['visit_id' => $visit['id'], 'etp_status' => [Status8021_2::CODE]]));
                    break;
            }
        }
        return ExitCode::OK;
    }
}
