<?php

namespace app\common\components\odopm;

use app\common\components\jcpSign\JcpSignService;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\odopm\OdopmArea;
use app\models\db\odopm\OdopmAttributesSpecification;
use app\models\db\odopm\OdopmCatalogs;
use app\models\db\odopm\OdopmCatalogsItem;
use app\models\db\odopm\OdopmDistrict;
use app\models\db\odopm\OdopmOrganization;
use app\models\db\odopm\OdopmOrganizationsActions;
use app\models\db\odopm\OdopmReferences;
use app\models\db\Organizations;
use Ramsey\Uuid\Uuid;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class ODOPMService
 * @package app\common\components\odopm
 */
class ODOPMService extends Component
{
    const
        EVERY_90_DAYS = 'Ежеквартально',
        AREAS_CATALOG_ID = 60,
        DISTRICTS_CATALOG_ID = 61,

        ACTION_ADD = 'added',
        ACTION_UPDATE = 'modified',
        ACTION_DELETE = 'deleted';

    /**
     * @var string
     */
    public $environment;
    /**
     * @var string
     */
    public $wsdl;
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $password;
    /**
     * @var bool
     */
    public $signRequests = false;

    /**
     * @var ODOPMSoapClient
     */
    private $client;
    /**
     * @var array
     */
    private $config;

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        if (!file_exists(__DIR__ . '/constants.php')) {
            throw new InvalidConfigException("Не найден файл constants.php");
        }

        $config = require_once __DIR__ . '/constants.php';

        switch ($this->environment) {
            case 'test':
                $this->config = $config['test'];
                break;
            case 'prod':
                $this->config = $config['prod'];
                break;
            default:
                throw new InvalidConfigException("Неверное значение environment");
        }

        $this->client = new ODOPMSoapClient($this->wsdl, [
            'soap_version' => SOAP_1_1,
            'compression' => SOAP_COMPRESSION_GZIP,
            'exceptions' => true,
            'trace' => 1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => @stream_context_create(array(
                'http' => array(
                    'header' =>
                        "username: {$this->username}\r\n" .
                        "password: {$this->password}\r\n",
                ),
            )),
        ]);
    }

    /**
     * Получение списка всех каталогов
     * @throws \Throwable
     */
    public function getCatalogList()
    {
        $result = $this->client->getCatalogList();

        if (!isset($result->ehdCatalogs) || !isset($result->ehdCatalogs->ehdCatalog)) {
            throw new \Exception("Не удалось получить список каталогов из ОДОПМ");
        }
        try {
            foreach ((array)$result->ehdCatalogs->ehdCatalog as $item) {
                if (!$catalog = OdopmCatalogs::findOne(['id_odopm' => $item->id])) {
                    Console::output(Console::ansiFormat("Сохранение каталога {$item->fullName}", [Console::FG_YELLOW]));
                    $catalog = new OdopmCatalogs();
                    $catalog->id_odopm = $item->id;
                } else {
                    Console::output(Console::ansiFormat("Обновление каталога {$item->fullName}", [Console::FG_YELLOW]));
                }

                $catalog->name = $item->fullName;
                $catalog->period = (string)($item->period == self::EVERY_90_DAYS ? 90 : 7);
                $catalog->save();
            }

            $this->setParentCatalogs();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), 'odopm');
            throw $e;
        }
    }

    /**
     * Получение справочника административных округов
     * @throws \Throwable
     */
    public function getCatalogAreas()
    {
        $data = [
            'dictionaryId' => self::AREAS_CATALOG_ID,
        ];

        $result = $this->client->getDictItemV2($data);

        $output = [];
        $countRow = 0;
        foreach ($result->ehdDictionaryItemsV2 as $items) {
            foreach ($items as $attribute) {
                $output[$countRow]['name'] = $attribute->name;
                $output[$countRow]['bti_code'] = $attribute->id;
                $countRow++;
            }
        }

        try {
            \Yii::$app->db->transaction(function () use ($output) {
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(OdopmArea::tableName(), [
                        'name',
                        'bti_code',
                    ], $output)
                    ->execute();
            });
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), 'odopm');
            throw $e;
        }
    }

    /**
     * Получение справочника районов
     * @throws \Exception
     * @throws \Throwable
     */
    public function getCatalogDistricts()
    {
        $output = [];
        $countRow = 0;

        $data = [
            'dictionaryId' => self::DISTRICTS_CATALOG_ID,
        ];

        $result = $this->client->getDictItem($data);
        foreach ($result->ehdDictionaryItems as $items) {
            foreach ($items as $attribute) {
                $output[$countRow]['name'] = $attribute->name;
                $output[$countRow]['bti_code'] = $attribute->id;
                $output[$countRow]['area_bti_code'] = $attribute->parent_id;
                $countRow++;
            }
        }

        try {
            \Yii::$app->db->transaction(function () use ($output) {
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(OdopmDistrict::tableName(), [
                        'name',
                        'bti_code',
                        'area_bti_code',
                    ], $output)
                    ->execute();
            });
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), 'odopm');
            throw $e;
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    private function setParentCatalogs()
    {
        $childCatalogs = $this->config['childCatalogs'];
        $db = \Yii::$app->db;
        //проставляем родительские связи для вет учереждений
        $vetParent = OdopmCatalogs::findOne(['id_odopm' => $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID']]);
        if ($vetParent !== null) {
            $db->createCommand()
                ->update(
                    OdopmCatalogs::tableName(),
                    ['parent_id' => $vetParent->id],
                    ['in', 'id_odopm', [$childCatalogs['VET_PHONES_ID'], $childCatalogs['VET_SCHEDULE_ID']]]
                )
                ->execute();
        }

        //проставляем родительские связи для мест учета трупов
        $corpsParent = OdopmCatalogs::findOne(['id_odopm' => $this->config['parentCatalogs']['CORPSES_STATION_ID']]);
        if ($corpsParent !== null) {
            $db->createCommand()
                ->update(
                    OdopmCatalogs::tableName(),
                    ['parent_id' => $corpsParent->id],
                    ['in', 'id_odopm', [$childCatalogs['CORPSES_PHONES_ID'], $childCatalogs['CORPSES_SCHEDULE_ID']]]
                )
                ->execute();
        }

        //проставляем родительские связи для пунктов регистрации
        $regParent = OdopmCatalogs::findOne(['id_odopm' => $this->config['parentCatalogs']['REGISTRATION_STATION_ID']]);
        if ($regParent !== null) {
            $db->createCommand()
                ->update(
                    OdopmCatalogs::tableName(),
                    ['parent_id' => $regParent->id],
                    ['in', 'id_odopm', [$childCatalogs['REGISTRATION_PHONES_ID'], $childCatalogs['REGISTRATION_SCHEDULE_ID']]]
                )
                ->execute();
        }

        //проставляем родительские связи для пунктов вакцинации
        $vaccineParent = OdopmCatalogs::findOne(['id_odopm' => $this->config['parentCatalogs']['VACCINATION_STATION_ID']]);
        if ($vaccineParent !== null) {
            $db->createCommand()
                ->update(
                    OdopmCatalogs::tableName(),
                    ['parent_id' => $vaccineParent->id],
                    ['in', 'id_odopm', [$childCatalogs['VACCINATION_PHONES_ID'], $childCatalogs['VACCINATION_SCHEDULE_ID']]]
                )
                ->execute();
        }
    }

    /**
     * Получение организаций из ОДОПМ
     */
    public function getOrganizations()
    {
        try {
            $this->cleanupTable(OdopmOrganization::tableName());

            foreach ($this->config['parentCatalogs'] as $idCatalog) {
                $data = [
                    'idCatalog' => $idCatalog,
                    'hideDeleted' => false,
                    'start' => 0,
                    'end' => 50,
                ];
                $result = $this->client->getCatalogItems($data);
                $items = (array)$result->ehdCatalogItemsset->ehdCatalogItem;
                if (empty($items)) {
                    continue;
                }

                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    foreach ($items as $item) {
                        $attributes = $this->mapOdopmOrganizationAttributes($item, $idCatalog);
                        if (empty($attributes)) {
                            continue;
                        }
                        $model = new OdopmOrganization($attributes);
                        if (!$model->save()) {
                            \Yii::error(var_export($model->getErrorSummary(true), true), 'odopm');
                            $transaction->rollBack();
                            throw new \Exception('Ошибка при сохранении организации');
                        }
                    }
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    \Yii::error($e->getMessage(), 'odopm');
                    throw $e;
                }
                $transaction->commit();
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), 'odopm');
            throw $e;
        }
    }

    /**
     * @param array $responseData
     * @param int   $idCatalog
     * @return array
     */
    private function mapOdopmOrganizationAttributes($responseData, $idCatalog)
    {
        $attributes = [];
        if (empty($responseData->ehdCatalogAttr) || !is_array($responseData->ehdCatalogAttr)) {
            return $attributes;
        }

        $map = array_flip(OdopmMappingHelper::$odopmAttributeNamesMapping);

        foreach ($responseData->ehdCatalogAttr as $entry) {
            $attributeName = ArrayHelper::getValue($map, $entry->tehName);
            if (empty($attributeName)) {
                continue;
            }
            if ($attributeName == 'public_services_available') {
                $attributes[$attributeName] = ($entry->value == 3);
            } else {
                $attributes[$attributeName] = $entry->value;
            }
        }

        $attributes['id_odopm_catalog'] = $idCatalog;
        $attrType = $this->mapOdopmOrganizationType($idCatalog);
        $attributes[$attrType] = true;

        return $attributes;
    }

    /**
     * @param int   $idCatalog
     * @return string
     */
    private function mapOdopmOrganizationType($idCatalog)
    {
        switch ($idCatalog) {
            case $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID']:
                return 'vet_organization';
            case $this->config['parentCatalogs']['CORPSES_STATION_ID']:
                return 'reseption_corpses';
            case $this->config['parentCatalogs']['REGISTRATION_STATION_ID']:
                return 'pet_registration';
            case $this->config['parentCatalogs']['VACCINATION_STATION_ID']:
                return 'free_vaccination';
            default:
                throw new InvalidConfigException("Неизвестный каталог");
        }
    }

    /**
     * Получение данных каталога (телефоны, расписания)
     * @todo - refactor!!!
     * @throws \yii\db\Exception
     */
    public function getCatalogItems()
    {
        $phonesCatalogs = [
            $this->config['childCatalogs']['VET_PHONES_ID'],
            $this->config['childCatalogs']['REGISTRATION_PHONES_ID'],
            $this->config['childCatalogs']['CORPSES_PHONES_ID'],
            $this->config['childCatalogs']['VACCINATION_PHONES_ID'],
        ];

        $schedulesCatalogs = [
            $this->config['childCatalogs']['VET_SCHEDULE_ID'],
            $this->config['childCatalogs']['REGISTRATION_SCHEDULE_ID'],
            $this->config['childCatalogs']['CORPSES_SCHEDULE_ID'],
            $this->config['childCatalogs']['VACCINATION_SCHEDULE_ID'],
        ];

        $output = [];
        $countRow = 0;
        $type = null;
        $orgArray = [];

        //для организаций
        $organizations = OdopmOrganization::find()->asArray()->all();
        foreach ($organizations as $organization) {
            $orgArray[] = [
                'global_id' => $organization['global_id'],
                'id_catalog' => $organization['id_odopm_catalog'],
                'entity_type' => OdopmCatalogsItem::TYPE_ORGANIZATION,
                'entity_id' => $organization['system_object_id'],
            ];
        }
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(OdopmCatalogsItem::tableName(), [
                'global_id',
                'id_catalog',
                'entity_type',
                'entity_id',
            ], $orgArray)
            ->execute();

        //для дочерних вложенных каталогов
        foreach ($this->config['childCatalogs'] as $name => $childCatalogId) {
            $data = [
                'idCatalog' => $childCatalogId,
                'start' => 1,
                'end' => 50,
                'hideDeleted' => false,
            ];
            $response = $this->client->getCatalogItems($data);
            //дробим ответ на записи каталогов
            foreach ($response->ehdCatalogItemsset as $item) {
                //дробим запись на отдельные атрибуты
                foreach ($item as $attributes) {
                    foreach ($attributes->ehdCatalogAttr as $attributeClass) {
                        $output[$countRow][$attributeClass->tehName] = $attributeClass->value;
                    }
                    $countRow++;
                }
            }

            //если вложенный каталог - телефоны
            if (in_array($childCatalogId, $phonesCatalogs)) {
                foreach ($output as $oneCatalogItemPhone) {
                    $parentObject = OdopmCatalogsItem::findOne(['global_id' => $oneCatalogItemPhone['global_object_id']]);

                    $phone = preg_replace("/[^0-9]/", "", $oneCatalogItemPhone['PublicPhone']);
                    $entityId = Contacts::find()->select('id')
                        ->where(['name' => $phone])->scalar();

                    $phonesArray[] = [
                        'object_id' => ($parentObject) ? $parentObject->id : null,
                        'id_catalog' => $childCatalogId,
                        'entity_type' => OdopmCatalogsItem::TYPE_CONTACT,
                        'entity_id' => ($entityId) ? $entityId : null,
                        'global_id' => $oneCatalogItemPhone['global_id'],
                    ];
                }
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(OdopmCatalogsItem::tableName(), [
                        'object_id',
                        'id_catalog',
                        'entity_type',
                        'entity_id',
                        'global_id',
                    ], $phonesArray)
                    ->execute();
                $phonesArray = [];
            }

            //если вложенный каталог - расписание
            if (in_array($childCatalogId, $schedulesCatalogs)) {
                foreach ($output as $oneCatalogItemDay) {
                    // у них на тестовом Dayofweek, на проде DayOfWeek
                    $dayFieldName1 = 'DayOfWeek';
                    $dayFieldName2 = 'Dayofweek';
                    if (array_key_exists($dayFieldName1, $oneCatalogItemDay)) {
                        $dayFieldName = $dayFieldName1;
                    } elseif (array_key_exists($dayFieldName2, $oneCatalogItemDay)) {
                        $dayFieldName = $dayFieldName2;
                    } else {
                        continue;
                    }
                    $parentObject = OdopmCatalogsItem::findOne(['global_id' => $oneCatalogItemDay['global_object_id']]);
                    $orgId = OdopmOrganization::find()
                        ->select('system_object_id')
                        ->where(['global_id' => $oneCatalogItemDay['global_object_id']])
                        ->scalar();
                    $entityId = $orgId . $oneCatalogItemDay[$dayFieldName];

                    $daysArray[] = [
                        'object_id' => ($parentObject) ? $parentObject->id : null,
                        'id_catalog' => $childCatalogId,
                        'entity_type' => OdopmCatalogsItem::TYPE_SCHEDULE,
                        'entity_id' => ($entityId) ? $entityId : null,
                        'global_id' => $oneCatalogItemDay['global_id'],
                    ];
                }
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(OdopmCatalogsItem::tableName(), [
                        'object_id',
                        'id_catalog',
                        'entity_type',
                        'entity_id',
                        'global_id',
                    ], $daysArray)
                    ->execute();
                $daysArray = [];
            }
            $output = [];
        }
    }

    /**
     * Получение спецификаций по каталогам
     * @throws \Exception
     */
    public function getCatalogSpec()
    {
        /** @var OdopmCatalogs[] $catalogs */
        $catalogs = OdopmCatalogs::find()->all();

        foreach ($catalogs as $catalog) {
            Console::output(Console::ansiFormat("Получение спецификации для каталога {$catalog->name}", [Console::FG_YELLOW]));
            $data = [
                'idCatalog' => $catalog->id_odopm,
            ];

            $result = $this->client->getCatalogSpec($data);

            if (!isset($result->ehdAttrSpec) || !isset($result->ehdAttrSpec->ehdCommonAttribute)) {
                throw new \Exception("Не удалось получить спецификацию каталога {$catalog->name} из ОДОПМ", [Console::FG_RED]);
            }

            if ($result->ehdAttrSpec->count == 0) {
                Console::output(Console::ansiFormat("Пустой список аттрибутов", [Console::FG_PURPLE]));
                continue;
            }

            foreach ($result->ehdAttrSpec->ehdCommonAttribute as $item) {
                if (!$spec = OdopmAttributesSpecification::findOne(['attribute_id' => $item->id, 'id_catalog' => $catalog->id])) {
                    Console::output(Console::ansiFormat("Сохранение аттрибута {$item->name}"));
                    $spec = new OdopmAttributesSpecification();
                    $spec->id_catalog = $catalog->id;
                    $spec->attribute_id = $item->id;
                } else {
                    Console::output(Console::ansiFormat("Обновление аттрибута {$item->name}"));
                }

                $spec->type_id = $item->typeId;
                $spec->name = $item->name;
                $spec->type = $item->type;
                $spec->is_primary = $item->isPrimaryKey;
                $spec->is_edit = $item->isEdit;
                $spec->is_required = $item->isReq;
                $spec->field_mask = $item->fieldMask;
                $spec->tech_name = $item->tehName;
                $spec->max_length = $item->maxLength;
                $spec->max_length_decimal = $item->maxLengthDecimal;
                $spec->dictionary_id = $item->dictId;
                $spec->ref_catalog_id = $item->refCatalog;
                $spec->is_deleted = $item->isDeleted;
                $spec->is_tmp_deleted = $item->isDeletedTmp;
                $spec->is_multi = $item->isMulti;

                $spec->save(false);
            }
        }
    }

    /**
     * Все справочники (действия, состояния записей, дни недели, действия над записями) кроме округов и районов
     */
    public function getReferences()
    {
        foreach ($this->config['dictionaries'] as $key => $referenceId) {
            $data = [
                'dictionaryId' => $referenceId,
            ];

            $result = $this->client->getDictItem($data);
            $countRow = 0;

            foreach ($result->ehdDictionaryItems as $items) {
                foreach ($items as $attribute) {
                    $output[$countRow]['id_reference'] = $referenceId;
                    $output[$countRow]['id_value'] = $attribute->id;
                    $output[$countRow]['name'] = $attribute->name;
                    $countRow++;
                }
            }

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(OdopmReferences::tableName(), [
                        'id_reference',
                        'id_value',
                        'name',
                    ], $output)
                    ->execute();
                $transaction->commit();
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage(), 'odopm');
                throw $e;
            }
            $output = [];
        }
    }

    /**
     * Выгрузка организаций в ОДОПМ
     * @param int $idCatalog
     * @throws \Exception
     */
    public function sendOrganizations($idCatalog)
    {
        $orgIds = $this->groupOrganizationsByAction($idCatalog);

        foreach ($orgIds as $action => $ids) {
            $xml = $this->generateXml($idCatalog, $action, $ids);
            //убираем кавычки из json с координатами
            $data = str_replace("&quot;", '"', $xml);
            $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ehd="http://ehd.mos.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <ehd:setDataIn>
        <xml>
        <![CDATA[
        {$data}
        ]]>
        </xml>
      </ehd:setDataIn>
   </soapenv:Body>
</soapenv:Envelope>
XML;

            try {
                $response = $this->client->__doRequest($this->signRequests ? $this->signXml($xml) : $xml, $this->wsdl, 'setDataIn', SOAP_1_1);
                \Yii::info($response, 'odopm');
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage(), 'odopm');
                Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));

                //print_R($client->__getLastRequestHeaders());
                return;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function sendAllOrganizations()
    {
        foreach ($this->config['parentCatalogs'] as $idCatalog) {
            Console::output(Console::ansiFormat('Выгрузка организаций в ОДОПМ (каталог ' . $idCatalog . ')', [Console::FG_YELLOW, Console::BOLD]));
            $this->sendOrganizations($idCatalog);
        }
    }

    /**
     * Удаление организаций из ОДОПМ
     * @param int $idCatalog
     * @param array $globalIds
     * @throws \Exception
     */
    public function removeOrganizations($idCatalog, $globalIds = null)
    {
        if (empty($globalIds)) {
            $globalIds = (new Query())
                ->select('global_id')
                ->from(OdopmOrganization::tableName())
                ->where(['id_odopm_catalog' => $idCatalog])
                ->andWhere([
                    'or',
                    ['!=', 'entry_state_id', 2],
                    ['entry_state_id' => null]
                ])
                ->andWhere([
                    'not in',
                    'system_object_id',
                    (new Query())
                        ->select('id')
                        ->from(Organizations::tableName()),
                ])
                ->column();
        }

        if (empty($globalIds)) {
            Console::error(Console::ansiFormat('Нет организаций для удаления из ОДОПМ', [Console::FG_YELLOW]));
            return;
        }

        $action = self::ACTION_DELETE;

        $xml = $this->generateXml($idCatalog, $action, $globalIds);

        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ehd="http://ehd.mos.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <ehd:setDataIn>
        <xml>
        <![CDATA[
        {$xml}
        ]]>
        </xml>
      </ehd:setDataIn>
   </soapenv:Body>
</soapenv:Envelope>
XML;

        try {
            $response = $this->client->__doRequest($this->signRequests ? $this->signXml($xml) : $xml, $this->wsdl, 'setDataIn', SOAP_1_1);
            \Yii::info($response, 'odopm');
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), 'odopm');
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
        }
    }

    /**
     * @param string $xml
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function signXml($xml)
    {
        /** @var JcpSignService $jcp */
        $jcp = \Yii::$app->get('jcpSign');

        return $jcp->signOdopm($xml);
    }

    /**
     * подготовка выборки организаций в системе
     * @param int $idCatalog
     * @return array
     * @throws \Exception
     */
    private function groupOrganizationsByAction($idCatalog)
    {
        switch ($idCatalog) {
            case $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID']:
                $condition = null;
                break;
            case $this->config['parentCatalogs']['CORPSES_STATION_ID']:
                $condition = ['reseption_corpses' => true];
                break;
            case $this->config['parentCatalogs']['VACCINATION_STATION_ID']:
                $condition = ['free_vaccination' => true];
                break;
            case $this->config['parentCatalogs']['REGISTRATION_STATION_ID']:
                $condition = ['pet_registration' => true];
                break;
            default:
                throw new \Exception("Неизвестный каталог");
        }

        $result = [];

        // Определение в сущностях organizations, contacts записей о ГВО, контактах ГВО которые должны быть в обновляемых каталогах
        // исключить приюты, исключить некомитетские организации, исключить комитет
        $root = Organizations::findOne(['id' => Organizations::GOS_ROOT_ID]);

        $vetasOrgsQuery = $root->prepareTreeQuery(false, true)
            ->innerJoin('contacts', 'contacts.entity_id = organizations.id')
            ->innerJoin('contact_types', 'contacts.id_contact_type = contact_types.id')
            ->andWhere([
                'or',
                ['not', ['latitude' => null]],
                ['not', ['longitude' => null]]
            ])
            ->andWhere(['contacts.entity_type' => (string)Contacts::ENTITY_TYPE_ORGANIZATION])
            ->andWhere(['contact_types.type' => ContactTypes::TYPE_PHONE]);
        if (!empty($condition)) {
            $vetasOrgsQuery->andWhere($condition);
        }

        //Определение записей уже имеющихся в обновляемых каталогах ЕГАС ОДОПМ
        $odopmOrgQuery = OdopmCatalogsItem::find()
            ->select('entity_id')
            ->where([
                'id_catalog' => $idCatalog,
                'entity_type' => OdopmCatalogsItem::TYPE_ORGANIZATION,
            ]);

        $vetasOrgIds = $vetasOrgsQuery->select('organizations.id')
            ->column();
        $vetasOrgIds = array_unique($vetasOrgIds);

        $odopmOrgIds = $odopmOrgQuery->column();

        $insertArray = array_diff($vetasOrgIds, $odopmOrgIds);
        $updateArray = array_intersect($vetasOrgIds, $odopmOrgIds);

        if (!empty($insertArray)) {
            $result[self::ACTION_ADD] = $insertArray;
        }
        if (!empty($updateArray)) {
            $result[self::ACTION_UPDATE] = $updateArray;
        }

        return $result;
    }

    /**
     * Генерируем набор данных
     *
     * @param int    $idCatalog
     * @param string $action
     * @param array  $ids
     * @return string
     * @throws \Exception
     */
    private function generateXml($idCatalog, $action, $ids)
    {
        //получаем массив данных
        /** @var \app\common\components\xmlGenerator\XmlGenerator $generator */
        $generator = \Yii::$app->get('xmlGenerator');

        $data = ($action == self::ACTION_DELETE)
            ? $this->generateDeleteDataArray($idCatalog, $action, $ids)
            : $this->generateInsertDataArray($idCatalog, $action, $ids);
        $xmlString = $generator->generateOdopm($data);

        return $xmlString;
    }

    /**
     * @param int    $idCatalog
     * @param string $action
     * @param array  $ids
     * @return array
     * @throws \Exception
     */
    private function generateInsertDataArray($idCatalog, $action, $ids)
    {
        /** @var OdopmCatalogs $catalog */
        $catalog = OdopmCatalogs::findOne(['id_odopm' => $idCatalog]);

        if (!$catalog) {
            throw new \Exception("Каталог не найден");
        }

        $items = $this->prepareItems($action, $catalog->id_odopm, $ids);

        $result = [
            [
                'tag' => 'message',
                'content' => [
                    [
                        'tag' => 'id',
                        'content' => Uuid::uuid4()->toString(),
                    ],
                    [
                        'tag' => 'catalog',
                        'elements' => $items,
                    ],
                ],
            ],
        ];

        $phonesInnerCatalogsWrapped = $this->getInnerPhoneCatalog($idCatalog, $action, $ids);
        foreach ($phonesInnerCatalogsWrapped as $phonesInnerCatalogs) {
            array_push($result[0]['content'], $phonesInnerCatalogs);
        }

        $scheduleInnerCatalogsWrapped = $this->getInnerScheduleCatalog($idCatalog, $action, $ids);

        foreach ($scheduleInnerCatalogsWrapped as $scheduleInnerCatalogs) {
            foreach ($scheduleInnerCatalogs as $scheduleInnerCatalog) {
                array_push($result[0]['content'], $scheduleInnerCatalog);
            }
        }

        return $result;
    }

    /**
     * @param string $action
     * @param int    $idCatalog
     * @param array  $ids
     * @return array
     */
    private function prepareItems($action, $idCatalog, $ids)
    {
        $result = [];
        $codes = [];

        foreach ($ids as $id) {
            $geoContent = $this->getGeoData($id);
            $idCode = ($action == self::ACTION_ADD) ? $this->generateIdCode($idCatalog, $codes) : null;
            $data = $this->prepareAttributeValues($idCatalog, $action, $id, $idCode);
            if (empty($data)) {
                continue;
            }

            $result[] = //elements
                [//0
                    'tag' => 'item',
                    'attributes' => [
                        'action' => $action,
                    ],
                    'elements' => [//elements
                        [//0
                            'tag' => 'categories',
                            'elements' => [
                                [
                                    'tag' => 'category',
                                    'attributes' => ["nameHier" => 'nameHierarchy'],
                                    'content' => $idCatalog,

                                ],
                            ],
                        ],
                        [
                            'tag' => 'data',
                            'elements' => $data,
                        ],
                        [
                            'tag' => 'geodata',
                            'content' => $geoContent,
                        ],
                    ],
                ];
        }

        return $result;
    }

    /**
     * @param int    $idCatalog
     * @param string $action
     * @param int    $idVetas
     * @param int    $idCode
     * @return array|null
     */
    private function prepareAttributeValues($idCatalog, $action, $idVetas, $idCode = null)
    {
        $result = [];
        $mappedAttributes = OdopmMappingHelper::getMappingAttributesArray($idCatalog);
        if ($action == self::ACTION_ADD) {
            //отсекаем global_id
            unset($mappedAttributes['global_id']);
        }

        $query = Organizations::find()
            ->select(
                'organizations.id as id, 
                organizations.name as name,
                organizations.short_name as short_name,
                organizations.inn as inn, 
                organizations.kpp as kpp,
                organizations.ogrn as ogrn,
                areas.bti_code as id_area,
                districts.bti_code as id_district,
                fias_addresses.full_address as address,
                organizations.comment,
                organizations.clarification_schedule,
                organizations.capital_structure, 
                organizations.chief_name, 
                organizations.chief_position, 
                organizations.public_services_available'
            )
            ->leftJoin('fias_addresses', 'fias_addresses.id = organizations.id_fias_address')
            ->leftJoin('areas', 'areas.id =  fias_addresses.id_area')
            ->leftJoin('districts', 'districts.id = fias_addresses.id_district')
            ->where(['organizations.id' => $idVetas])
            ->asArray();

        if ($action == self::ACTION_ADD) {
            $query->addSelect(new Expression('NULL as id_code_odopm'));
        }

        if ($action == self::ACTION_UPDATE) {
            $query->leftJoin(
                OdopmOrganization::tableName() . ' odopm_organizations',
                'system_object_id = organizations.id and id_odopm_catalog = :id_catalog',
                ['id_catalog' => $idCatalog]
            );
            $query->addSelect(new Expression(
                    'odopm_organizations.global_id as global_id,
                    odopm_organizations.id_code as id_code_odopm'
                )
            );
        }

        $organization = $query->one();

        if ($organization === null) {
            // @todo - return null and log error?
            throw new \Exception('Organization not found ' . $idVetas);
        }
        if (empty($organization['id_area']) || empty($organization['id_district'])) {
            // @todo - log error?
            return null;
        }

        foreach (['name', 'short_name'] as $field) {
            $organization[$field] = $this->fixOrgName($organization[$field], $field, $idCatalog);
        }

        if ($idCatalog != $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID']) {
            OdopmMappingHelper::$attributeNamesMapping['capital_structure'] = 'SignOfCapitalStructure';
        } else {
            OdopmMappingHelper::$attributeNamesMapping['chief_name'] = 'ChiefName';
            OdopmMappingHelper::$attributeNamesMapping['chief_position'] = 'ChiefPosition';
            OdopmMappingHelper::$attributeNamesMapping['public_services_available'] = 'PublicServicesAvailable';
        }

        //проставляем id для каталога и признак ключа для атрибутов
        foreach ($mappedAttributes as $odopmName => $odopmFieldId) {
            $serviceFields = [
                'EntryState',
                'EntryAddReason',
                'EntryChangeReason',
                'EntryDeleteReason',
            ];
            if (in_array($odopmName, $serviceFields, true)) {
                $value = $this->findServiceFieldValue($odopmName, $organization['id']);
                if (empty($value)) {
                    continue;
                }
            } elseif ($odopmName == 'RespDepartment') {
                $value = ($organization['short_name'] == 'ГБУ «Мосветстанция»') ? 4 : 3;
            } else {
                $vetasName = array_search($odopmName, OdopmMappingHelper::$attributeNamesMapping);
                if ($vetasName === false) {
                    continue;
                }

                if ($vetasName == 'id_code_odopm' && $action == self::ACTION_ADD) {
                    $value = $idCode;
                } elseif ($vetasName == 'public_services_available') {
                    $value = ($organization[$vetasName] == true) ? 3 : 4;
                } else {
                    $value = $organization[$vetasName];
                }
            }

            if ($action == self::ACTION_ADD && $odopmFieldId == -2) {
                $pk = 'true';
            } elseif ($action == self::ACTION_UPDATE && $odopmFieldId == -1) {
                $pk = 'true';
            } else {
                $pk = 'false';
            }

            $type = OdopmAttributesSpecification::find()
                ->alias('oas')
                ->leftJoin(OdopmCatalogs::tableName() . ' oc', 'oc.id = oas.id_catalog')
                ->select('type')
                ->where(['oc.id_odopm' => $idCatalog])
                ->andWhere(['attribute_id' => $odopmFieldId])
                ->scalar();

            $result[] = [
                'tag' => 'attribute',
                'attributes' => [
                    'field_id' => $odopmFieldId,
                    'type' => $type,
                    'pk' => $pk,
                ],
                'elements' => [
                    [
                        'tag' => 'values',
                        'elements' => [
                            [
                                'tag' => 'value',
                                'attributes' => ["occurrence" => 0],
                                'content' => $value,
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $result;
    }

    /**
     * @param int $id
     * @return false|string
     */
    private function getGeoData($id)
    {
        $organizationCordinate = Organizations::find()
            ->select('latitude as y, longitude as x')
            ->where(['id' => $id])
            ->asArray()
            ->one();
        $organizationCordinate['x'] = (float)$organizationCordinate['x'];
        $organizationCordinate['y'] = (float)$organizationCordinate['y'];
        $geocontent = json_encode($organizationCordinate);

        return $geocontent;
    }

    /**
     * @param int   $idCatalog
     * @param int   $action
     * @param array $ids
     * @return array
     */
    private function getInnerPhoneCatalog($idCatalog, $action, $ids)
    {
        $catalogAttributeRelation = [
            $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID'] => [
                'parent' => $this->config['parentAttributes']['VET_PARENT_ATTR_PHONE'],
                'child' => $this->config['childAttributes']['VET_CHILD_ATTR_PHONE']],

            $this->config['parentCatalogs']['CORPSES_STATION_ID'] => [
                'parent' => $this->config['parentAttributes']['CORPSES_PARENT_ATTR_PHONE'],
                'child' => $this->config['childAttributes']['CORPSES_CHILD_ATTR_PHONE']],

            $this->config['parentCatalogs']['REGISTRATION_STATION_ID'] => [
                'parent' => $this->config['parentAttributes']['REGISTRATION_PARENT_ATTR_PHONE'],
                'child' => $this->config['childAttributes']['REGISTRATION_CHILD_ATTR_PHONE']],

            $this->config['parentCatalogs']['VACCINATION_STATION_ID'] => [
                'parent' => $this->config['parentAttributes']['VACCINATION_PARENT_ATTR_PHONE'],
                'child' => $this->config['childAttributes']['VACCINATION_CHILD_ATTR_PHONE']],
        ];
        $items = $this->preparePhoneItems($catalogAttributeRelation, $action, $idCatalog, $ids);

        return $items;
    }

    /**
     * @param array  $catalogAttributeRelation
     * @param string $action
     * @param int    $idCatalog
     * @param array  $ids
     * @return array
     */
    private function preparePhoneItems($catalogAttributeRelation, $action, $idCatalog, $ids)
    {
        $data = [];

        $parentChildRelation = [
            $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID'] => $this->config['childCatalogs']['VET_PHONES_ID'],
            $this->config['parentCatalogs']['CORPSES_STATION_ID'] => $this->config['childCatalogs']['CORPSES_PHONES_ID'],
            $this->config['parentCatalogs']['REGISTRATION_STATION_ID'] => $this->config['childCatalogs']['REGISTRATION_PHONES_ID'],
            $this->config['parentCatalogs']['VACCINATION_STATION_ID'] => $this->config['childCatalogs']['VACCINATION_PHONES_ID'],
        ];

        foreach ($ids as $id) {
            $data = $this->getDataForPhones($catalogAttributeRelation[$idCatalog], $id, $action, $idCatalog, $parentChildRelation);
        }

        return $data;
    }

    /**
     * @param array  $catalogAttributeRelation
     * @param int    $vetasId
     * @param string $action
     * @param int    $idCatalog
     * @param array  $parentChildRelation
     * @return array
     */
    private function getDataForPhones($catalogAttributeRelation, $vetasId, $action, $idCatalog, $parentChildRelation)
    {
        $resultUpper = [];
        $vetasOrgPhones = Organizations::find()
            ->select('contacts.name')
            ->leftJoin('contacts', 'contacts.entity_id = organizations.id')
            ->leftJoin('contact_types', 'contacts.id_contact_type = contact_types.id')
            ->where(['contacts.entity_type' => (string)Contacts::ENTITY_TYPE_ORGANIZATION])
            ->andWhere(['contact_types.type' => ContactTypes::TYPE_PHONE])
            ->andWhere([Organizations::tableName() . '.id' => $vetasId])
            ->asArray()
            ->column();

        $dataForPhones = [];
        $i = 1;
        foreach ($vetasOrgPhones as $vetasOrgPhone) {
            if ($vetasOrgPhone) {
                if (strlen($vetasOrgPhone) == 12) {
                    // +74956120425
                    $vetasOrgPhone = substr($vetasOrgPhone, 1);
                }
                $vetasOrgPhone = trim('(' . substr($vetasOrgPhone, 1, 3) . ') ' . substr($vetasOrgPhone, 4, 3) . '-' . substr($vetasOrgPhone, 7, 2) . '-' . substr($vetasOrgPhone, 9, 2));
            } else {
                $vetasOrgPhone = '(000) 000-00-00';
            }

            $dataForPhones[] = [
                'tag' => 'data',
                'elements' => [
                    [
                        'tag' => 'attribute',
                        'attributes' => [
                            'field_id' => -5,
                            'type' => 'STRING',
                            'pk' => 'false',
                        ],
                        'elements' => [[
                            'tag' => 'values',
                            'elements' => [[
                                'tag' => 'value',
                                'attributes' => ["occurrence" => 0],
                                'content' => $vetasId,
                            ]],
                        ]],
                    ],
                    [
                        'tag' => 'attribute',
                        'attributes' => [
                            'field_id' => -2,
                            'type' => 'STRING',
                            'pk' => 'false',
                        ],
                        'elements' => [[
                            'tag' => 'values',
                            'elements' => [
                                [
                                    'tag' => 'value',
                                    'attributes' => ["occurrence" => 0],
                                    'content' => $i,
                                ],
                            ],
                        ]],
                    ],
                    [
                        'tag' => 'attribute',
                        'attributes' => [
                            'field_id' => $catalogAttributeRelation['child'],
                            'type' => 'STRING',
                            'pk' => 'false',
                        ],
                        'elements' => [
                            [
                                'tag' => 'values',
                                'elements' => [[
                                    'tag' => 'value',
                                    'attributes' => ["occurrence" => 0],
                                    'content' => $vetasOrgPhone,
                                ]],
                            ],
                        ],
                    ],
                ],
            ];
            $i++;
        }

        foreach ($dataForPhones as $dataForPhone) {
            $resultUpper[] = [
                'tag' => 'catalog',
                'attributes' => ['parent_catalog_id' => $idCatalog],
                'elements' => [
                    [
                        'tag' => 'item',
                        'attributes' => [
                            'action' => $action,
                        ],
                        'elements' => [
                            [
                                'tag' => 'categories',
                                'elements' => [
                                    [
                                        'tag' => 'category',
                                        'attributes' => ["occurrence" => 0],
                                        'content' => $parentChildRelation[$idCatalog],
                                    ],
                                ],
                            ],
                            $dataForPhone,
                        ],
                    ],
                ],
            ];
        }

        return $resultUpper;
    }

    /**
     * @param int    $idCatalog
     * @param string $action
     * @param array  $ids
     * @return array
     */
    private function getInnerScheduleCatalog($idCatalog, $action, $ids)
    {
        $catalogAttributeRelation = [
            $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID'] => [
                'parent' => $this->config['parentAttributes']['VET_PARENT_ATTR_SCHEDULE'],
                'child_day' => $this->config['childAttributes']['VET_CHILD_ATTR_SCHEDULE_DAY'],
                'child_hour' => $this->config['childAttributes']['VET_CHILD_ATTR_SCHEDULE_HOURS']],

            $this->config['parentCatalogs']['CORPSES_STATION_ID'] => [
                'parent' => $this->config['parentAttributes']['CORPSES_PARENT_ATTR_SCHEDULE'],
                'child_day' => $this->config['childAttributes']['CORPSES_CHILD_ATTR_SCHEDULE_DAY'],
                'child_hour' => $this->config['childAttributes']['CORPSES_CHILD_ATTR_SCHEDULE_HOURS']],

            $this->config['parentCatalogs']['REGISTRATION_STATION_ID'] => [
                'parent' => $this->config['parentAttributes']['REGISTRATION_PARENT_ATTR_SCHEDULE'],
                'child_day' => $this->config['childAttributes']['REGISTRATION_CHILD_ATTR_SCHEDULE_DAY'],
                'child_hour' => $this->config['childAttributes']['REGISTRATION_CHILD_ATTR_SCHEDULE_HOURS']],

            $this->config['parentCatalogs']['VACCINATION_STATION_ID'] => [
                'parent' => $this->config['parentAttributes']['VACCINATION_PARENT_ATTR_SCHEDULE'],
                'child_day' => $this->config['childAttributes']['VACCINATION_CHILD_ATTR_SCHEDULE_DAY'],
                'child_hour' => $this->config['childAttributes']['VACCINATION_CHILD_ATTR_SCHEDULE_HOURS']],
        ];

        $items = $this->itemsForSchedule($catalogAttributeRelation, $idCatalog, $ids, $action);

        return $items;
    }

    /**
     * @param array  $catalogAttributeRelation
     * @param int    $idCatalog
     * @param array  $ids
     * @param string $action
     * @return array
     */
    private function itemsForSchedule($catalogAttributeRelation, $idCatalog, $ids, $action)
    {
        $parentChildRelation = [
            $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID'] => $this->config['childCatalogs']['VET_SCHEDULE_ID'],
            $this->config['parentCatalogs']['CORPSES_STATION_ID'] => $this->config['childCatalogs']['CORPSES_SCHEDULE_ID'],
            $this->config['parentCatalogs']['REGISTRATION_STATION_ID'] => $this->config['childCatalogs']['REGISTRATION_SCHEDULE_ID'],
            $this->config['parentCatalogs']['VACCINATION_STATION_ID'] => $this->config['childCatalogs']['VACCINATION_SCHEDULE_ID'],
        ];

        $data = [];
        foreach ($ids as $id) {
            $data[] = $this->getDataForSchedule($id, $catalogAttributeRelation, $idCatalog, $parentChildRelation, $action);
        }

        return $data;
    }

    /**
     * @param int    $id
     * @param array  $catalogAttributeRelation
     * @param int    $idCatalog
     * @param array  $parentChildRelation
     * @param string $action
     * @return array
     */
    private function getDataForSchedule($id, $catalogAttributeRelation, $idCatalog, $parentChildRelation, $action)
    {
        $resultUpper = [];
        $dataArrayForDays = [];
        $organizationSchedule = Organizations::find()
            ->select('schedule')
            ->where(['id' => $id])->asArray()->one();

        $scheduleArray = json_decode($organizationSchedule['schedule'], true);

        $fieldDayId = $catalogAttributeRelation[$idCatalog]['child_day'];
        $fieldHourId = $catalogAttributeRelation[$idCatalog]['child_hour'];

        if (!empty($scheduleArray)) {
            foreach ($scheduleArray as $key => $value) {
                if (isset($value['idle']) && $value['idle'] === true) {
                    $workHours = 'выходной';
                } elseif (isset($value['rtc_operation']) && $value['rtc_operation'] === true) {
                    $workHours = 'круглосуточно';
                } elseif (isset($value['to_time']) && isset($value['from_time'])) {
                    $workHours = $value['from_time'] . '-' . $value['to_time'];
                } else {
                    $workHours = null;
                }
                $dataArrayForDays[] = [
                    'tag' => 'data',
                    'elements' => [
                        [
                            'tag' => 'attribute',
                            'attributes' => [
                                'field_id' => -5, //parent
                                'type' => 'STRING',
                                'pk' => 'false',
                            ],
                            'elements' => [[
                                'tag' => 'values',
                                'elements' => [[
                                    'tag' => 'value',
                                    'attributes' => [
                                        'occurrence' => 0,
                                    ],
                                    'content' => $id,
                                ]],
                            ]],
                        ],
                        [
                            'tag' => 'attribute',
                            'attributes' => [
                                'field_id' => -2,
                                'type' => 'STRING',
                                'pk' => 'false',
                            ],
                            'elements' => [[
                                'tag' => 'values',
                                'elements' => [[
                                    'tag' => 'value',
                                    'attributes' => ["occurrence" => 0],
                                    'content' => $key,

                                ]],
                            ]],
                        ],
                        [
                            'tag' => 'attribute',
                            'attributes' => [
                                'field_id' => $fieldDayId,
                                'type' => 'STRING',
                                'pk' => 'false',
                            ],
                            'elements' => [[
                                'tag' => 'values',
                                'elements' => [[
                                    'tag' => 'value',
                                    'attributes' => [
                                        'occurrence' => 0,
                                    ],
                                    'content' => $key,
                                ]],
                            ]],
                        ],
                        [
                            'tag' => 'attribute',
                            'attributes' => [
                                'field_id' => $fieldHourId,
                                'type' => 'STRING',
                                'pk' => 'false',
                            ],
                            'elements' => [[
                                'tag' => 'values',
                                'elements' => [[
                                    'tag' => 'value',
                                    'attributes' => [
                                        'occurrence' => 0,
                                    ],
                                    'content' => $workHours,
                                ]],
                            ]],
                        ],
                    ],
                ];
            }
        }

        foreach ($dataArrayForDays as $dataForDay) {
            $resultUpper[] = [
                'tag' => 'catalog',
                'attributes' => ['parent_catalog_id' => $idCatalog],
                'elements' => [
                    [
                        'tag' => 'item',
                        'attributes' => [
                            'action' => $action,
                        ],
                        'elements' => [
                            [
                                'tag' => 'categories',
                                'elements' => [
                                    [
                                        'tag' => 'category',
                                        'attributes' => ["occurrence" => 0],
                                        'content' => $parentChildRelation[$idCatalog],
                                    ],
                                ],
                            ],
                            $dataForDay,
                        ],
                    ],
                ],
            ];
        }

        return $resultUpper;
    }

    /**
     * @param int    $idCatalog
     * @param string $action
     * @param array  $globalIds
     * @return array
     * @throws \Exception
     */
    private function generateDeleteDataArray($idCatalog, $action, $globalIds)
    {
        /** @var OdopmCatalogs $catalog */
        $catalog = OdopmCatalogs::findOne(['id_odopm' => $idCatalog]);

        if (!$catalog) {
            throw new \Exception("Каталог не найден");
        }

        $mappedAttributes = OdopmMappingHelper::getMappingAttributesArray($idCatalog);

        $items = [];

        foreach ($globalIds as $globalId) {
            $deleteReasonId = null;
            $odopmObject = OdopmCatalogsItem::findOne([
                'entity_type' => OdopmCatalogsItem::TYPE_ORGANIZATION,
                'id_catalog' => $idCatalog,
                'global_id' => $globalId
            ]);
            if (!empty($odopmObject)) {
                $deleteReasonId = OdopmOrganizationsActions::find()
                    ->select('entry_deleted_reason')
                    ->where(['id_organization' => $odopmObject->entity_id])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->limit(1)
                    ->scalar();
            }
            if (empty($deleteReasonId)) {
                $deleteReasonId = 5; // 'ошибочное добавление объекта'
            }

            $attributes = [
                [
                    'field_id' => ArrayHelper::getValue($mappedAttributes, 'global_id'),
                    'type' => 'INTEGER',
                    'pk' => 'true',
                    'value' => $globalId,
                ],
                [
                    'field_id' => ArrayHelper::getValue($mappedAttributes, 'EntryDeleteReason'),
                    'type' => 'DICT',
                    'pk' => 'false',
                    'value' => $deleteReasonId,
                ],
            ];

            $data = [];

            foreach ($attributes as $attribute) {
                $data[] = [
                    'tag' => 'attribute',
                    'attributes' => [
                        'field_id' => $attribute['field_id'],
                        'type' => $attribute['type'],
                        'pk' => $attribute['pk'],
                    ],
                    'elements' => [
                        [
                            'tag' => 'values',
                            'elements' => [
                                [
                                    'tag' => 'value',
                                    'attributes' => ["occurrence" => 0],
                                    'content' => $attribute['value'],
                                ],
                            ],
                        ],
                    ],
                ];
            }

            $item = [
                'tag' => 'item',
                'attributes' => [
                    'action' => $action,
                ],
                'elements' => [
                    [
                        'tag' => 'categories',
                        'elements' => [
                            [
                                'tag' => 'category',
                                'attributes' => ["nameHier" => 'nameHierarchy'],
                                'content' => $idCatalog,

                            ],
                        ],
                    ],
                    [
                        'tag' => 'data',
                        'elements' => $data,
                    ],
                ],
            ];

            $items[] = $item;
        }

        $result = [
            [
                'tag' => 'message',
                'content' => [
                    [
                        'tag' => 'id',
                        'content' => Uuid::uuid4()->toString(),
                    ],
                    [
                        'tag' => 'catalog',
                        'elements' => $items,
                    ],
                ],
            ],
        ];

        return $result;
    }

    /**
     * @param string $tableName
     * @throws \yii\db\Exception
     */
    private function cleanupTable(string $tableName): void
    {
        \Yii::$app->db->createCommand('TRUNCATE TABLE ' . $tableName)->execute();
        \Yii::$app->db->createCommand('SELECT pg_catalog.setval(\'' . $tableName . '_id_seq\', 1, false)')->execute();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function cleanupAllTables()
    {
        $tableNames = [
            OdopmArea::tableName(),
            OdopmAttributesSpecification::tableName(),
            OdopmCatalogs::tableName(),
            OdopmCatalogsItem::tableName(),
            OdopmDistrict::tableName(),
            OdopmOrganization::tableName(),
            OdopmReferences::tableName(),
        ];

        foreach ($tableNames as $tableName) {
            $this->cleanupTable($tableName);
        }
    }

    /**
     * @param string $odopmName
     * @param int    $id_organization
     * @return bool|int|string
     */
    private function findServiceFieldValue($odopmName, $id_organization)
    {
        $query = (new Query())
            ->from(OdopmOrganizationsActions::tableName())
            ->where(['id_organization' => $id_organization])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1);

        switch ($odopmName) {
            case 'EntryState':
                $query->andWhere(['action' => self::ACTION_DELETE]);
                $result = $query->exists();
                return $result ? 2 : 1;
            case 'EntryAddReason':
                $query->select('entry_add_reason')
                    ->andWhere(['action' => self::ACTION_ADD]);
                break;
            case 'EntryChangeReason':
                $query->select('entry_change_reason')
                    ->andWhere(['action' => self::ACTION_UPDATE]);
                break;
            case 'EntryDeleteReason':
                $query->select('entry_deleted_reason')
                    ->andWhere(['action' => self::ACTION_DELETE]);
                break;
            default:
                throw new \Exception('Unknown field ' . $odopmName);
        }

        return $query->scalar();
    }

    /**
     * Ошибка в ответе: "Количество символов в поле «Идентификатор записи»
     * превышает максимально допустимое. Максимальное значение 2"
     * @param int   $idCatalog
     * @param array $codes
     * @return int
     */
    private function generateIdCode($idCatalog, &$codes = [])
    {
        $idCode = null;

        if (empty($codes)) {
            $codes = (new Query())
                ->select('id_code')
                ->from(OdopmOrganization::tableName())
                ->where(['id_odopm_catalog' => $idCatalog])
                ->orderBy(['id_code' => SORT_ASC])
                ->column();
        }

        if (empty($codes)) {
            $idCode = 1;
        }

        foreach (range(1, 99) as $i) {
            if (!in_array($i, $codes)) {
                $idCode = $i;
                break;
            }
        }
        if ($idCode === null) {
            throw new \Exception('Could not generate ID code within range from 1 to 99');
        }

        $codes[] = $idCode;
        sort($codes, SORT_NUMERIC);

        return $idCode;
    }

    /**
     * @param string $name
     * @param string $fieldName
     * @param int    $idCatalog
     * @return string
     */
    private function fixOrgName($name, $fieldName, $idCatalog)
    {
        if ($fieldName == 'short_name' && $idCatalog != $this->config['parentCatalogs']['VET_ORGANIZATIONS_ID']) {
            // в сокращенном наименовании в каталоге ветеринарных организаций допускается точка, а в остальных каталогах нет!!!
            $name = preg_replace('#\.#', '', $name);
        }

        $name = trim($name);
        $name = preg_replace('#\s{2,}#', ' ', $name);
        $name = preg_replace('#\"(.*?)\"#iu', '«$1»', $name);

        return $name;
    }
}
