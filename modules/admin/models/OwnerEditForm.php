<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.12.18
 * Time: 13:26
 */

namespace app\modules\admin\models;

use app\common\validators\FilterUcwordsValidator;
use app\common\validators\FullTrimValidator;
use Yii;
use yii\base\Model;
use app\common\validators\SnilsValidator;

/**
 * Class OwnerEditForm
 * @package app\modules\admin\models
 */
class OwnerEditForm extends Model
{
    private $_owner;
    public $id;
    public $f_fio;
    public $i_fio;
    public $o_fio;
    public $birthday;
    public $is_legal;
    public $jur_name;
    public $inn;
    public $ogrn;
    public $snils;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'trim'],
            [['f_fio', 'i_fio'], 'required'],
            [['f_fio'], 'string', 'max' => 150],
            [['i_fio', 'o_fio'], 'string', 'max' => 50],
            [['f_fio', 'i_fio', 'o_fio'], FullTrimValidator::class],
            [['f_fio', 'i_fio', 'o_fio'], FilterUcwordsValidator::class],
            [['birthday'], 'date'],
            ['inn', 'string', 'max' => 12],
            ['ogrn', 'string', 'max' => 13],
            ['snils', SnilsValidator::class],
        ];
    }

    /**
     * SpecEditForm constructor.
     * @param Owners $owner
     * @param array $config
     */
    public function __construct(Owners $owner, $config = [])
    {
        $this->_owner = $owner;
        $this->id = $owner->id;
        $this->f_fio = $owner->f_fio;
        $this->i_fio = $owner->i_fio;
        $this->o_fio = $owner->o_fio;
        $this->birthday = $owner->birthday;
        $this->is_legal = $owner->is_legal;
        $this->jur_name = $owner->jur_name;
        $this->inn = $owner->inn;
        $this->ogrn = $owner->ogrn;
        $this->snils = $owner->snils;
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function editOwner()
    {
        $attr = Yii::$app->request->post();
        $owner = $this->_owner;
        $owner->f_fio = $attr['OwnerEditForm']['f_fio'];
        $owner->i_fio = $attr['OwnerEditForm']['i_fio'];
        $owner->o_fio = $attr['OwnerEditForm']['o_fio'];
        $owner->birthday = $attr['OwnerEditForm']['birthday'];
        $owner->is_legal = $attr['OwnerEditForm']['is_legal'];
        $owner->jur_name = $attr['OwnerEditForm']['jur_name'];
        $owner->inn = $attr['OwnerEditForm']['inn'];
        $owner->ogrn = $attr['OwnerEditForm']['ogrn'];
        $owner->snils = $attr['OwnerEditForm']['snils'];
        if($this->validate()) {
            return $owner->save();
        } else {
            return false;
        }
    }

}
