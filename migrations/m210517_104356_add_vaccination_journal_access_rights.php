<?php

use app\common\migrate\RbacMigration;
use yii\base\InvalidConfigException;

/**
 * Class m210517_104356_add_vaccination_journal_access_rights
 */
class m210517_104356_add_vaccination_journal_access_rights extends RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->assign = [
            'activity.vaccinationJournal.menu' => [
                'descr' => 'Доступность меню журнала упрощенных вакцинаций',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
            'activity.vaccinationJournal.R' => [
                'descr' => 'Просмотр журнала упрощенных вакцинаций',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
            'activity.vaccinationJournal.W' => [
                'descr' => 'Заполнение журнала упрощенных вакцинаций',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->grantPermissions($this->assign);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException|\yii\base\Exception
     */
    public function safeDown()
    {
        $this->revokePermissions($this->assign, true);
    }
}
