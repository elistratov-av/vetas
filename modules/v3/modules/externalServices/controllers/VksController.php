<?

namespace app\modules\v3\modules\externalServices\controllers;

use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 * Класс VksController.
 *
 * Этот класс предназначен для работы с сервисом ВКС.
 *
 * @package app\modules\v3\modules\externalServices\controllers
 */

class VksController extends BaseController
{

    /**
     * Отдает информацию по приему по его GUID.
     *
     * Метод отдает GUID, date, time, duration, organiztion, specialist_fullname, service по GUID.
     *
     * @param string $guid Идентификатор файла.
     * @return object Возвращает объект с данными визита или message в случае ошибки.
     */
    public static function actionIndex(string $guid)
    {
        $visit = (new Query())
            ->select(
                [
                    "v.guid_video AS sid_guid",
                    "DATE(v.start_dttm) AS date",
                    "TO_CHAR(v.start_dttm, 'HH24:MI:SS') AS slot",
                    "v.duration",
                    "o.short_name as organiztion",
                    "u.fullname as specialist_fullname",
                    "STRING_AGG(gs.name, ', ') AS service"
                ]
            )
            ->from('visits AS v')
            ->leftJoin('organizations AS o', 'o.id = v.id_organization')
            ->leftJoin('visits_specialists AS vs', 'vs.id_visit = v.id')
            ->leftJoin('specialists AS s', 's.id = vs.id_specialist')
            ->leftJoin('users AS u', 'u.id = s.id_user')
            ->leftJoin('visits_gov_services AS vgs', 'vgs.id_visit = v.id')
            ->leftJoin('gov_services AS gs', 'gs.id = vgs.id_service')
            ->where(['v.guid_video' => $guid])
            ->groupBy(['v.guid_video', 'v.start_dttm', 'v.duration', 'o.short_name', 'u.fullname'])
            ->one();
        if (!$visit) throw new NotFoundHttpException('Не найдено приема с указанным GUID', 404);
        return $visit;
    }
}
