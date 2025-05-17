<?php

namespace app\models\db;

use app\models\db\GovServices;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table 'public.gov_services_history'.
 * @property integer $id
 * @property integer $gov_service_id
 * @property integer $action_type
 * @property string $action_date
 * @property integer $user_id
 * @property integer $load_type
 * @property integer $pricelist_loading_id
 */
class GovServicesHistory extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->hasAttribute('action_date')) {
            $this->attachBehavior(
                'timestamp',
                [
                    'class' => TimestampBehavior::class,
                    'createdAtAttribute' => 'action_date',
                    'updatedAtAttribute' => 'action_date',
                    'value' => date('Y-m-d H:i:s')
                ]
            );
        }

        if ($this->hasAttribute('user_id')) {
            $this->attachBehavior(
                'blameable',
                [
                    'class' => BlameableBehavior::class,
                    'createdByAttribute' => 'user_id',
                    'updatedByAttribute' => 'user_id'
                ]
            );
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.gov_services_history';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Первичный ключ',
            'gov_service_id' => 'Ссылка на Государственные услуги',
            'action_type' => 'тип операции в зависимости от изменений (0-создана, 1-изменена, 2- удалена)',
            'action_date' => 'дата и время совершения операции',
            'user_id' => 'идентификатор пользователя сделавшего изменения в таблице (users.id)',
            'load_type' => '0 - ручной 1 - файлом',
            'pricelist_loading_id' => 'Источник данных в pricelist_loading'
        ];
    }
}
