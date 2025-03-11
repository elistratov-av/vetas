<?php

namespace app\models\db;

use app\common\models\NewsletterInfoStatus;
use app\common\models\UserModel;
use app\modules\admin\models\Organization;

/**
 * This is the model class for table "newsletter_info".
 *
 * @property int $id
 * @property int|null $type
 * @property string $name Наименование рассылки
 * @property int $status Статус рассылки
 * @property string|null $period_from Период недоступности системы от
 * @property string|null $period_upto Период недоступности системы до
 * @property string $mailing_date Дата рассылки
 * @property string|null $actual_mailing_date Фактическая дата рассылки
 * @property bool|null $all_organizations
 * @property string $text
 * @property string $created_at
 * @property int|null $user_id
 * @property int $mailing_time_option
 *
 * @property Organizations $organizations
 * @property NewsletterType $type_id
 */
class NewsletterInfo extends ActiveRecord
{
    public const MAILING_TIME_OPTION_DATE = 1;
    public const MAILING_TIME_OPTION_NOW = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'newsletter_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type', 'name', 'mailing_date', 'text', 'created_at'], 'required'],
            [['type', 'user_id'], 'default', 'value' => null],
            [['type', 'user_id', 'status',], 'integer'],
            ['type', 'in', 'range' => [
                NewsletterType::TYPE_USER,
                NewsletterType::TYPE_EVENT,
            ]],
            ['mailing_time_option', 'in', 'range' => [
                self::MAILING_TIME_OPTION_DATE,
                self::MAILING_TIME_OPTION_NOW,
            ]],
            [['all_organizations'], 'boolean'],
            ['status', 'in', 'range' => NewsletterInfoStatus::list()],
            [['period_from', 'period_upto', 'mailing_date', 'created_at'], 'safe'],
            [['text'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
            'status' => 'Status',
            'period_from' => 'Period From',
            'period_upto' => 'Period Upto',
            'mailing_date' => 'Mailing Date',
            'actual_mailing_date' => 'Actual mailing Date',
            'all_organizations' => 'All Organizations',
            'text' => 'Text',
            'created_at' => 'Created At',
            'user_id' => 'User',
        ];
    }

    /**
     * Gets query for [[Organizations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizations()
    {
        return $this->hasMany(Organization::class, ['id' => 'organization_id'])
            ->viaTable('newsletter_info_organization', ['newsletter_info_id' => 'id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(NewsletterType::class, ['id' => 'type']);
    }

    /**
     * Gets query for [[User0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserModel::class, ['id' => 'user_id']);
    }
}
