<?php

namespace app\models\db\tmc;

use app\models\db\ActiveRecord;

/**
 * Список ТМЦ над которым производится действие (передача, утилизация и тд)
 *
 * @property int $id
 * @property int $id_action ID действия
 * @property int $id_balance_tmc ID балансового ТМЦ
 * @property int $id_organization ID организации балансового ТМЦ (нужен для ключа)
 * @property int $id_specialist ID специалиста балансового ТМЦ (нужен для ключа)
 * @property string $count Количество в единицах измерения
 * @property string $count_selected Значение кол-во введенное пользователем
 * @property string $count_production_form Количество в формах производства
 * @property string $count_utilize Количество утилизорованого ТМЦ
 * @property int $id_dosage ID дозировки (если выбрано списание в дозировке)
 * @property string $comment Комментарий
 * @property bool $write_off_all Флаг: Списать целиком
 * @property bool $write_off_pack_form Флаг: Списать целиком по форме производства
 * @property Balance $balance
 * @property BalanceAction $balanceAction
 * @property Dosages $dosage
 * @property TmcBase $tmc
 * @author Aleksandr Roik
 */
class BalanceActionTmcList extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.balance_action_tmc_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_action', 'id_balance_tmc', 'id_organization'], 'required'],
            [['id_action', 'id_balance_tmc', 'id_organization', 'id_specialist', 'id_dosage'], 'default', 'value' => null],
            [['id_action', 'id_balance_tmc', 'id_organization', 'id_specialist', 'id_dosage'], 'integer'],
            [['count', 'count_selected', 'count_production_form', 'count_utilize'], 'number'],
            [['write_off_all', 'write_off_pack_form'], 'boolean'],
            ['comment', 'string', 'max' => 255],
            [['id_balance_tmc', 'id_organization', 'id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => Balance::className(), 'targetAttribute' => ['id_balance_tmc' => 'id', 'id_organization' => 'id_organization', 'id_specialist' => 'id_specialist']],
            [['id_action', 'id_organization', 'id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => BalanceAction::className(), 'targetAttribute' => ['id_action' => 'id', 'id_organization' => 'from_id_organization', 'id_specialist' => 'from_id_specialist']],
            [['id_dosage'], 'exist', 'skipOnError' => true, 'targetClass' => Dosages::className(), 'targetAttribute' => ['id_dosage' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_action'             => 'id действия',
            'id_balance_tmc'        => 'id балансового ТМЦ',
            'id_organization'       => 'id организации балансового ТМЦ',
            'id_specialist'         => 'id специалиста балансового ТМЦ',
            'count'                 => 'Количество в единицах измерения',
            'count_selected'        => 'Количество',
            'count_production_form' => 'Количество в формах производства',
            'count_utilize'         => 'Количество утилизорованого ТМЦ',
            'id_dosage'             => 'id дозировки',
            'comment'               => 'Комментарий',
            'write_off_all'         => 'Отметка "Списать целиком"',
            'write_off_pack_form'   => 'Отметка "Списать целиком по форме производства"',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalance()
    {
        return $this->hasOne(Balance::class, ['id' => 'id_balance_tmc']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalanceAction()
    {
        return $this->hasOne(BalanceAction::class, ['id' => 'id_action']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDosage()
    {
        return $this->hasOne(Dosages::class, ['id' => 'id_dosage']);
    }
}
