<?php

namespace app\commands;

use app\common\components\entity\EntityException;
use app\common\components\entity\EntityMigration;
use yii\console\ExitCode;
use yii\db\ColumnSchema;
use yii\db\Query;
use yii\helpers\BaseInflector;
use yii\helpers\Console;

class MigrateController extends \yii\console\controllers\MigrateController
{

    public $templateFile = '@app/commands/migrate/views/migration.php';
    
    /**
     * @return Object|EntityMigration
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getEntityMigrationService()
    {
        return \Yii::$container->get('entityMigration', [], [
            'entityManager' => \Yii::$container->get('entityManager')
        ]);
    }

    /**
     * Create migration to entity from config
     * @param string $name
     * @param bool $default_fields
     * @return int
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionEntity(string $name, bool $default_fields = true)
    {
        try {
            $migration = $this->getEntityMigrationService();
            $tableName = BaseInflector::camel2id($name);
            $migrationName = 'create_' . str_replace('-', '_', $tableName) . '_table';
            if($default_fields){
                $df = ', created_by:integer, updated_by:integer, created_at:timestamp, updated_at:timestamp';
            }
            return $this->runAction('create', [
                $migrationName,
                'fields' => $migration->getCreateParams($name). $df,
                'interactive' => 0
            ]);
        } catch (EntityException $e) {
            Console::output(Console::ansiFormat("Ошибка: " . $e->getMessage(), [
                Console::FG_RED
            ]));

            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @param string $name
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\console\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function actionEntityDiff(string $name)
    {
        $this->stdout($this->ansiFormat("Генерация миграции расхождений для сущности: {$name}" . PHP_EOL,
            Console::FG_GREEN));

        $log = [];
        try {
            $migration = $this->getEntityMigrationService();
            $diff = $migration->diff($name);
            $tableName = str_replace('-', '_', BaseInflector::camel2id($name));
            if (!empty($diff['deleteColumns'])) {
                /** @var ColumnSchema $column */
                foreach ($diff['deleteColumns'] as $column) {
                    $migrationName = "drop_{$column->name}_column_from_{$tableName}_table";
                    $this->runAction('create', [
                        $migrationName,
                        'fields' => "{$column->name}:{$column->type}",
                        'interactive' => 0
                    ]);
                    $log[] = $migrationName;
                }
            }

            if (!empty($diff['addColumns'])) {
                $migrationName = 'add';
                foreach ($diff['addColumns'] as $column) {
                    $migrationName .= "_{$column['name']}_column";
                }
                $migrationName .= "_to_{$tableName}_table";

                $fields = $migration->makeFieldsParam($diff['addColumns']);
                $this->runAction('create', [
                    $migrationName,
                    'fields' => implode(',', $fields),
                    'interactive' => 0
                ]);
                $log[] = $migrationName;
            }

            if (!empty($log)) {
                $this->stdout("Созданы миграции:" . PHP_EOL . implode(PHP_EOL, $log) . PHP_EOL,
                    Console::FG_GREEN
                );
            } else {
                $this->stdout($this->ansiFormat("Изменений для сущности {$name} не обнаружено" . PHP_EOL,
                    Console::FG_YELLOW
                ));
            }
        } catch (EntityException $e) {
            Console::output(Console::ansiFormat("Ошибка: " . $e->getMessage(), [
                Console::FG_RED
            ]));

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    public function actionDiff(){
        $this->stdout($this->ansiFormat("Миграции, которые есть на диске, но нет в таблице migration\n"));
        /** @var \yii\db\ActiveQuery $query */
        $query = (new Query())
            ->select('version')
            ->from($this->migrationTable);
        $migrations_in_db = $query->all($this->db);
        $migrations_in_db = array_map(function ($x) { return $x['version']; }, $migrations_in_db);
        unset($migrations_in_db[0]);


        $migrations_in_dir = scandir(\Yii::getAlias('@app/migrations'));
        unset($migrations_in_dir[0]);unset($migrations_in_dir[1]);
        $migrations_in_dir = array_map(function ($x) { return str_replace('.php', '', $x); }, $migrations_in_dir);

        $diff = array_diff($migrations_in_dir, $migrations_in_db);
        print_r($diff);
    }
}
