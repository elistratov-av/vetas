<?php


namespace app\modules\adminv\controllers\audit;

use app\common\components\rbac\Role;
use app\models\db\audit\AuditLog;
use app\models\db\audit\TimesheetLog;
use app\models\db\audit\VisitLog;
use app\modules\adminv\controllers\AdminController;
use app\modules\adminv\models\search\TimesheetLogSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class AuditController extends AdminController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Role::ROLE_SYSADMIN_GOS, Role::ROLE_MANAGEMENT_GOS],
                    ],
                ],
            ],
        ];
    }

    /**
     * Аудит животных
     * @param $id
     * @return string
     */
    public function actionPets($id)
    {
        $query = AuditLog::find()
            ->where([
                'parent_table_name' => 'public.pets',
                'parent_entity_id' => $id
            ])->orderBy(['date' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('pet', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Аудит владельцев
     * @param $id
     * @return string
     */
    public function actionPetOwners($id)
    {
        $query = AuditLog::find()
            ->where([
                'parent_table_name' => 'public.pet_owners',
                'parent_entity_id' => $id
            ])->orderBy(['date' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('pet_owner', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Аудит организаций
     * @param $id
     * @return string
     */
    public function actionOrganizations($id)
    {
        $query = AuditLog::find()
            ->where([
                'parent_table_name' => 'public.organizations',
                'parent_entity_id' => $id
            ])->orderBy(['date' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('organization', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionLogDetail()
    {
        $id = \Yii::$app->request->get('id');
        $log_row = AuditLog::findOne(['id' => $id]);

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('log-detail', [
                'log_row' => $log_row,
            ]);
        } else {
            return $this->render('log-detail', [
                'log_row' => $log_row,
            ]);
        }
    }

	/**
	 * История смены статусов приема
	 * @param $id
	 *
	 * @return string
	 */
    public function actionVisit($id)
	{
		return $this->render(
			'visit',
			[
				'dataProvider' => new ActiveDataProvider([
					'query' => VisitLog::find()
						->where(['id_visit' => $id])
						->orderBy(['date' => SORT_DESC]),
					'pagination' => [
						'pageSize' => 20,
					],
				])
			]
		);
	}

	/**
	 * @return string
	 */
	public function actionVisitDetail()
	{
		$visit_log_row = VisitLog::findOne(['id' => \Yii::$app->request->get('id')]);

		if (\Yii::$app->request->isAjax) {
			return $this->renderPartial('visit-detail', [
				'visit_log_row' => $visit_log_row,
			]);
		} else {
			return $this->render('visit-detail', [
				'visit_log_row' => $visit_log_row,
			]);
		}
	}

    /**
     * История удаления рассписаний
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function actionTimesheet(): string
    {
        $searchModel = new TimesheetLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render(
            'timesheet',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * Отображение слепка лога
     *
     * @return string
     */
	public function actionTimesheetDetail(): string
    {
        $timesheet_log = TimesheetLog::findOne(['id' => Yii::$app->request->get('id')]);

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial(
                'timesheet-detail',
                [
                    'timesheet_log' => $timesheet_log,
                ]
            );
        }

        return $this->render(
            'timesheet-detail',
            [
                'timesheet_log' => $timesheet_log,
            ]
        );
    }
}
