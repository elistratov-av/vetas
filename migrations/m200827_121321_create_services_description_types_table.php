<?php

use app\models\db\GovServices;
use app\models\db\DescriptionTypes;
use yii\db\Query;
use yii\db\Migration;

/**
 * Handles the creation of table `services_required_description_types`.
 */
class m200827_121321_create_services_description_types_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('services_description_types', [
            'id' => $this->primaryKey(),
            'id_service' => $this->integer()->comment('ID услуги'),
            'id_description_type' => $this->integer()->comment('ID поля данных приема'),
            'required' => $this->boolean()->comment('Обязательность заполнения поля данных приема')
        ]);

        $this->addCommentOnTable(
            'services_description_types',
            'Список полей данных приема по услугам'
        );

        $this->createIndex(
            'services_description_types-id_service',
            'services_description_types',
            'id_service'
        );

        $this->createIndex(
            'services_description_types-id_description_type',
            'services_description_types',
            'id_description_type'
        );

        $this->addForeignKey(
            'fk-services_description_types-id_description_type',
            'services_description_types',
            'id_description_type',
            'description_types',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-services_description_types-id_service',
            'services_description_types',
            'id_service',
            'gov_services',
            'id',
            'CASCADE'
        );

        $data = $this->parseCsv(__DIR__ . '/data/list_description_types_for_services_20200827.csv');
        $descriptionTypes = $this->getDescriptionTypes();
        $error = [];

        foreach ($data as $row) {
            try {
                if (!array_key_exists($row['name_description_type'], $descriptionTypes)) {
                    throw new Exception('Не найдено соответствие типа поля ' . $row['name_description_type']);
                }

                if (!isset($idService) || !empty($row['name_service'])) {
                    if (empty($row['name_service'])) {
                        continue;
                    }
                    $service = GovServices::findOne([
                        'name' => $row['name_service'],
                        'type' => null
                    ]);
                    if (is_null($service)) {
                        unset($idService);
                        throw new Exception('Не найдено соответствие услуги ' . $row['name_service']);
                    }
                    $idService = $service->id;
                }

                $this->insert(
                    'services_description_types',
                    [
                        'id_service' => $idService,
                        'id_description_type' => $descriptionTypes[$row['name_description_type']],
                        'required' => $row['required']
                    ]
                );
            } catch (Exception $e) {
                $error[] = $e->getMessage() . ' - [' . implode(',', $row) . ']';
            }
        }

        if (!empty($error)) {
            echo 'Ошибка записи' . PHP_EOL . var_export($error, true) . PHP_EOL;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-services_description_types-id_description_type',
            'services_description_types'
        );

        $this->dropForeignKey(
            'fk-services_description_types-id_service',
            'services_description_types'
        );

        $this->dropIndex(
            'services_description_types-id_service',
            'services_description_types'
        );

        $this->dropIndex(
            'services_description_types-id_description_type',
            'services_description_types'
        );

        $this->dropTable('services_description_types');
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws Exception
     */
    protected function parseCsv(string $filePath): array
    {
        $fp = fopen($filePath, 'r');
        if ($fp === false) {
            throw new Exception("Не удалось открыть файл {$filePath}");
        }

        $result = [];

        while (($row = fgetcsv($fp, 1024, ',')) !== false) {
            $result[] = [
                'name_service' => $row[1],
                'name_description_type' => $row[3],
                'required' => ($row[4] == 'обязательное'),
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getDescriptionTypes(): array
    {
        $descriptionTypes = (new Query())->select('id, name')
            ->from(DescriptionTypes::tableName())
            ->where(['entity_type' => 'visit'])
            ->all();

        $prepareDescriptionTypes = [];

        foreach ($descriptionTypes as $descriptionType) {
            $prepareDescriptionTypes[$descriptionType['name']] = $descriptionType['id'];
        }

        return $prepareDescriptionTypes;
    }
}
