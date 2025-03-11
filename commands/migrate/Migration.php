<?php

namespace app\commands\migrate;

class Migration extends \yii\db\Migration
{

    public function init()
    {
        parent::init();
        $this->lockCheck();
    }

    public function dropTable($table)
    {
        if ($this->db->schema->getTableSchema($table, true) !== null) {
            parent::dropTable($table);
        }
    }

    public function renameTable($table, $newName)
    {
        if ($this->db->schema->getTableSchema($table, true) !== null) {
            parent::renameTable($table, $newName);
        }
    }

    public function addColumn($table, $column, $type)
    {
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema !== null && array_key_exists($column, $schema->columns) === false) {
            parent::addColumn($table, $column, $type);
        }
    }

    public function dropColumn($table, $column)
    {
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema !== null && array_key_exists($column, $schema->columns)) {
            parent::dropColumn($table, $column);
        }
    }

    public function alterColumn($table, $column, $type)
    {
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema !== null && array_key_exists($column, $schema->columns)) {
            parent::alterColumn($table, $column, $type);
        }
    }

    public function renameColumn($table, $name, $newName)
    {
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema !== null && array_key_exists($name, $schema->columns)) {
            parent::renameColumn($table, $name, $newName);
        }
    }

    /*
     * Блокировка запуска паралельных миграций
     */

    /**
     * Заблокировать запуск миграций
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function lockMigrations()
    {
        $filename = $this->lockFilePath();

        $this->lockCheck();

        if (touch($filename) != true){
            echo PHP_EOL;
            echo PHP_EOL . '[EXIT] Can`t lock migrations! ' . $filename . PHP_EOL;
            echo PHP_EOL;
            \Yii::$app->end(1);
         }

        return true;
    }

    /**
     * Проверяет, есть ли блокировка и если есть - завершает выполнение
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function lockCheck()
    {
        $filename = $this->lockFilePath();

        if (is_file($filename)){
            echo PHP_EOL;
            echo PHP_EOL . '[EXIT] Migrations locked!  ' . $filename . PHP_EOL;
            echo PHP_EOL;
            \Yii::$app->end(1);
        }

        return false;
    }

    /**
     * Возвращает имя файла для блокировки запуска паралельных миграций
     * @return bool|string
     */
    public function lockFilePath()
    {
        return \Yii::getAlias('@app/runtime/migrations.lock');
    }

    /**
     * Разблокирует выполнение миграций
     * @throws \yii\base\ExitException
     */
    public function unlockMigrations()
    {
        $filename = $this->lockFilePath();

        if (unlink($filename) != true){
            echo PHP_EOL;
            echo PHP_EOL . '[EXIT] Can`t unlock migrations!  ' . $filename . PHP_EOL;
            echo PHP_EOL;
            \Yii::$app->end(1);
        }
    }
}
