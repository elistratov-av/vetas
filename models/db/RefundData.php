<?php

namespace app\models\db;

/**
 * @property string bank_name               Наименование Банка
 * @property integer corresponded_account   Корреспонденсткий счёт
 * @property string org_name                Наименование организации
 * @property integer bik                    БИК
 * @property integer client_account         Клиентский счёт
 * @property string client_fio              ФИО клиента
 * @property integer id_visit
 * @property Visits visit                   Осмотр к которому оформляется возврат средств
 *
 * Данные предоставленные клиентом для возврата средств по предоплаченой услуге
 * На момент реализации актуально для телевет услуг с мос.ру
 */
class RefundData extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.refund_data';
    }

    public function rules()
    {
        return [
            [['bank_name', 'corresponded_account', 'org_name', 'bik', 'client_account', 'client_fio', 'id_visit'], 'required'],
            [['bank_name', 'org_name', 'client_fio'], 'string'],
            [['corresponded_account', 'bik', 'client_account', 'id_visit'], 'integer'],
            [
                ['id_visit'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Visits::class,
                'targetAttribute' => ['id_visit' => 'id']
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit']);
    }
}
