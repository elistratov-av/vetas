<?php

namespace app\commands;

use app\common\helpers\YandexHelper;
use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\FiasAddresses;
use app\models\db\odopm\OdopmArea;
use app\models\db\odopm\OdopmDistrict;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class OdopmController
 * @package app\commands
 */
class OdopmController extends Controller
{
    /**
     * @return \app\common\components\odopm\ODOPMService
     */
    protected function getService()
    {
        return \Yii::$app->odopmService;
    }

    /**
     * @return int
     */
    public function actionInit()
    {
        try {
            $service = $this->getService();
            $service->cleanupAllTables();

            Console::output(Console::ansiFormat('Получение списка всех каталогов', [Console::FG_YELLOW, Console::BOLD]));
            $service->getCatalogList();

            Console::output(Console::ansiFormat('Получение спецификаций по каталогам', [Console::FG_YELLOW, Console::BOLD]));
            $service->getCatalogSpec();

            Console::output(Console::ansiFormat('Получение справочника административных округов', [Console::FG_YELLOW, Console::BOLD]));
            $service->getCatalogAreas();

            Console::output(Console::ansiFormat('Получение справочника районов', [Console::FG_YELLOW, Console::BOLD]));
            $service->getCatalogDistricts();

            Console::output(Console::ansiFormat('Обновление кодов БТИ административных округов', [Console::FG_YELLOW, Console::BOLD]));
            $this->runAction('update-areas-codes');

            Console::output(Console::ansiFormat('Обновление кодов БТИ справочника районов', [Console::FG_YELLOW, Console::BOLD]));
            $this->runAction('update-district-codes');

            Console::output(Console::ansiFormat('Все справочники (действия, состояния записей, дни недели, действия над записями) кроме округов и районов', [Console::FG_YELLOW]));
            $service->getReferences();

            Console::output(Console::ansiFormat('Получение организаций из ОДОПМ', [Console::FG_YELLOW, Console::BOLD]));
            $service->getOrganizations();

            Console::output(Console::ansiFormat('Получение данных каталога', [Console::FG_YELLOW, Console::BOLD]));
            $service->getCatalogItems();

            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Получение и сохранение списка всех каталогов из ОДОПМ
     * @return int
     */
    public function actionGetCatalogList()
    {
        try {
            Console::output(Console::ansiFormat('Получение списка всех каталогов', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->getCatalogList();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * связующая таблица спецификации атрибутов во всех каталогах
     */
    public function actionGetCatalogSpecs()
    {
        try {
            Console::output(Console::ansiFormat('Выгрузка организаций из ОДОПМ', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->getCatalogSpec();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Получение справочника административных округов
     * @return int
     */
    public function actionGetCatalogAreas()
    {
        try {
            Console::output(Console::ansiFormat('Получение справочника административных округов', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->getCatalogAreas();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Получение справочника районов
     * @return int
     */
    public function actionGetCatalogDistricts()
    {
        try {
            Console::output(Console::ansiFormat('Получение справочника районов', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->getCatalogDistricts();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * все справочники (действия, состояния записей, дни недели, действия над записями) кроме округов и районов
     */
    public function actionGetReferences()
    {
        try {
            Console::output(Console::ansiFormat('Все справочники (действия, состояния записей, дни недели, действия над записями) кроме округов и районов', [Console::FG_YELLOW]));
            $this->getService()->getReferences();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Получение организаций из ОДОПМ
     */
    public function actionGetOrganizations()
    {
        try {
            Console::output(Console::ansiFormat('Получение организаций из ОДОПМ', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->getOrganizations();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Получение данных каталога (телефоны, расписания)
     */
    public function actionGetCatalogItems()
    {
        try {
            Console::output(Console::ansiFormat('Получение данных каталога', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->getCatalogItems();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @param int $id ID каталога
     * @param int $debug
     * @return int
     */
    public function actionSend($id, $debug = null)
    {
        try {
            Console::output(Console::ansiFormat('Выгрузка организаций в ОДОПМ (каталог ' . $id . ')', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->sendOrganizations($id);
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            if ($debug == 1) {
                Console::output($e->getTraceAsString());
            }
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @param int $debug
     * @return int
     */
    public function actionSendAll($debug = null)
    {
        try {
            Console::output(Console::ansiFormat('Выгрузка всех организаций в ОДОПМ', [Console::FG_YELLOW, Console::BOLD]));
            $this->getService()->sendAllOrganizations();
            return ExitCode::OK;
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            if ($debug == 1) {
                Console::output($e->getTraceAsString());
            }
            \Yii::error($e, 'odopm');
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @return int
     */
    public function actionUpdateAreasCodes()
    {
        /** @var \app\models\db\Areas $areas */
        $areas = Areas::find()
            ->all();

        foreach ($areas as $area) {
            $btiCode = (new Query())
                ->select('bti_code')
                ->from(OdopmArea::tableName())
                ->where(['ilike', 'name', $area->name])
                ->scalar();
            if ($btiCode === false) {
                continue;
            }
            if ($area->bti_code === $btiCode) {
                continue;
            }
            $area->bti_code = $btiCode;
            $area->save(true, ['bti_code', 'updated_at']);
        }

        return ExitCode::OK;
    }

    /**
     * @return int
     */
    public function actionUpdateDistrictCodes()
    {
        /** @var \app\models\db\Districts $districts */
        $districts = Districts::find()
            ->all();

        foreach ($districts as $district) {
            $btiCode = (new Query())
                ->select('bti_code')
                ->from(OdopmDistrict::tableName())
                ->where(['ilike', "replace(name, 'ё', 'е')", $district['name']])
                ->scalar();
            if ($btiCode === false) {
                continue;
            }
            if ($district->bti_code === $btiCode) {
                continue;
            }
            $district->bti_code = $btiCode;
            $district->save(true, ['bti_code', 'updated_at']);
        }

        return ExitCode::OK;
    }

    /**
     * Заполнение координат организации, если в таблице организаций отсутствуют координаты
     * @return int
     */
    public function actionSeedCoordinates()
    {
        $organizationsAddresses = (new Query())
            ->select('o.id AS id_organization, o.latitude, o.longitude, fa.full_address, fa.lat, fa.lon')
            ->from(Organizations::tableName() . ' o')
            ->leftJoin(FiasAddresses::tableName() . ' fa', 'o.id_fias_address = fa.id')
            ->leftJoin(OrgTypes::tableName() . ' ot', 'ot.id = o.id_org_type')
            ->andWhere(['=', 'ot.is_tech', false])
            ->andWhere([
                'or',
                ['o.latitude' => null],
                ['o.latitude' => ''],
                ['o.longitude' => null],
                ['o.longitude' => ''],
            ])
            ->all();

        foreach ($organizationsAddresses as $address) {
            $lat = null;
            $lon = null;
            if (empty($address['full_address'])) {
                Console::output('Empty address for organization ID ' . $address['id_organization']);
                continue;
            }
            if (!empty($address['lat']) && !empty($address['lon'])) {
                $lat = $address['lat'];
                $lon = $address['lon'];
            } else {
                $yandexResponse = YandexHelper::geocodeErrorMute($address['full_address']);
                if (!empty($yandexResponse)) {
                    $lat = ArrayHelper::getValue($yandexResponse, 'latitude');
                    $lon = ArrayHelper::getValue($yandexResponse, 'longitude');
                }
            }
            if (!empty($lat) && !empty($lon)) {
                \Yii::$app->db->createCommand()
                    ->update(
                        Organizations::tableName(),
                        [
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'updated_at' => date('Y-m-d H:i:s')
                        ],
                        ['id' => $address['id_organization']]
                    )
                    ->execute();
                Console::output('Updated organigation ID ' . $address['id_organization'] . ': lat ' . $lat . ', lon ' . $lon);
            }
        }

        return ExitCode::OK;
    }

    /**
     * @param int $idCatalog
     * @param int $globalId
     * @return int
     * @throws \Exception
     */
    public function actionRemove($idCatalog, $globalId = null)
    {
        if (!empty($globalId)) {
            $globalId = [$globalId];
        }

        $this->getService()->removeOrganizations($idCatalog, $globalId);

        return ExitCode::OK;
    }
}
