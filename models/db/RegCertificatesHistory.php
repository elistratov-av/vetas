<?php


namespace app\models\db;


use app\common\validators\FullTrimValidator;


/**
 * This is the model class for table "public.reg_updates".
 *
 * @property integer $id
 * @property string $id_reg
 * @property string $id_pet
 * @property string|array $snapshot
 * @property string $reason
 * @property string $action
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property RegCertificates $regCertificate
 */
class RegCertificatesHistory extends ActiveRecord
{
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.reg_certificates_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'id_reg', 'id_pet'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['reason', 'action'], 'string', 'max' => 255],
            [['reason'], FullTrimValidator::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_reg' => 'ID Reg Certificate',
            'snapshot' => 'Snapshot',
            'reason' => 'Reason',
            'update_date' => 'Update Date',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}