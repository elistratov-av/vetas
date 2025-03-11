<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\rbac\Item;

/**
 * Class RbacController
 * @package app\commands
 */
class RbacController extends Controller
{
    /**
     * Выводит список существующих ролей, разрешений и правил в консоль или файл
     * Файл пишется в '@runtime/output'
     * @param string $filename
     * @return int
     */
    public function actionList($filename = null)
    {
        $auth = $this->authManager();

        $roles = $auth->getRoles();

        $permissions = $auth->getPermissions();
        ArrayHelper::multisort($permissions, 'name', SORT_ASC, SORT_STRING);

        $table = new Table();
        $table->setHeaders([
            'Name',
            'Type',
            'Description',
            'Rule',
            'Children'
        ]);

        $rows = [];

        foreach (array_merge($roles, $permissions) as $item) {
            $children = ArrayHelper::getColumn($auth->getChildren($item->name), 'name', false);
            sort($children, SORT_STRING);
            $rows[] = [
                $item->name,
                ($item->type == Item::TYPE_ROLE ? 'role' : 'permission'),
                (empty($item->description) ? '' : mb_substr($item->description, 0, (int)mb_strrpos($item->description, "\n"))),
                (empty($item->ruleName) ? '' : $item->ruleName),
                (empty($children) ? '' : $children),
            ];
        }

        $rules = $auth->getRules();
        ArrayHelper::multisort($permissions, 'name', SORT_ASC, SORT_STRING);

        foreach ($rules as $rule) {
            $rows[] = [
                $rule->name,
                'rule',
                get_class($rule),
                '',
                '',
            ];
        }

        $table->setRows($rows);

        $output = $table->run();

        if (isset($filename)) {
            $this->writeToFile($filename, $output);
        } else {
            $this->stdout($output);
        }

        return ExitCode::OK;
    }

    /**
     * Выводит список заданных для сущностей разрешений в консоль или файл
     * @param string $filename
     * @return int
     */
    public function actionListEntities($filename = null)
    {
        $path = Yii::getAlias('@app/config/entities');
        $files = FileHelper::findFiles($path, ['only' => ['*.php'], 'recursive' => false]);

        $table = new Table();
        $table->setHeaders([
            'Entity name',
            'Permissions',
        ]);

        $rows = [];

        foreach ($files as $file) {
            $config = require $file;
            if (!is_array($config)) {
                continue;
            }
            $entityName = key($config);
            $entityConfig = current($config);
            if (empty($entityConfig) || !is_array($entityConfig) || !isset($entityConfig['meta']) || !is_array($entityConfig['meta'])) {
                continue;
            }
            $access = ArrayHelper::getValue($entityConfig['meta'], 'access');
            if (empty($access) || !is_array($access)) {
                continue;
            }
            $rules = [];
            foreach ($access as $key => $value) {
                $rules[] = $key . ': ' . implode(', ', $value);
            }
            $rows[] = [
                $entityName,
                $rules,
            ];
        }

        $table->setRows($rows);

        $output = $table->run();

        if (isset($filename)) {
            $this->writeToFile($filename, $output);
        } else {
            $this->stdout($output);
        }

        return ExitCode::OK;
    }

    /**
     * Выводит список заданных для сущностей разрешений в консоль или файл
     * @param string $filename
     * @return int
     */
    public function actionListUsers($filename = null)
    {
        $table = new Table();
        $table->setHeaders([
            'User ID',
            'Login',
            'Spec ID',
            'Spec FIO',
            'Org ID',
            'Organization',
            'Roles',
        ]);

        $rows = [];

        $q = (new Query())
            ->select('[[u]].[[id]], [[u]].[[login]], [[s]].[[id]] AS specialist_id, [[u]].[[fullname]], [[s]].[[id_organization]], [[o]].[[short_name]] AS org_name')
            ->from('users u')
            ->leftJoin('specialists s', '[[u]].[[id]] = [[s]].[[id_user]]')
            ->leftJoin('organizations o', '[[s]].[[id_organization]] = [[o]].[[id]]')
            ->orderBy([
                '[[u]].[[fullname]]' => SORT_ASC,
                '[[s]].[[id]]' => SORT_ASC,
            ]);

        $records = $q->all();

        $auth = $this->authManager();

        foreach ($records as $record) {
            $row = array_values($record);
            $assignments = $auth->getAssignments($record['id'], $record['specialist_id']);
            $roles = [];
            if (!empty($assignments)) {
                $roles = array_keys($assignments);
                sort($roles);
            }
            $row[] = empty($roles) ? '-' : $roles;
            $rows[] = $row;
        }

        $table->setRows($rows);

        $output = $table->run();

        if (isset($filename)) {
            $this->writeToFile($filename, $output);
        } else {
            $this->stdout($output);
        }

        return ExitCode::OK;
    }

    /**
     * @return \app\common\components\rbac\DbManager
     * @throws \yii\base\InvalidConfigException
     */
    private function authManager()
    {
        return \Yii::$app->get('authManager');
    }

    /**
     * @param string $filename
     * @param string $output
     * @throws \yii\base\Exception
     */
    private function writeToFile($filename, $output)
    {
        $dir = Yii::getAlias('@runtime/output');
        if (FileHelper::createDirectory($dir)) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $output . PHP_EOL, FILE_TEXT);
        }
    }
}
