<?php

use app\commands\migrate\Migration;

/**
 * Class m200826_090239_add_column_id_specialists_to_visits_logs
 */
class m200826_090239_add_column_id_specialists_to_visits_logs extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn(
			'audit.visits_logs',
			'id_organization',
			$this->integer()
				->comment('ID организации пользователя, изменивший статус приема')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('audit.visits_logs', 'id_organization');
	}

}
