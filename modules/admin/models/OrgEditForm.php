<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.12.18
 * Time: 17:47
 */

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;

/**
 * Class OrgEditForm
 * @package app\modules\admin\models
 */
class OrgEditForm extends Model
{
    private $_organization;
    public $id;
    public $name;
    public $short_name;
    public $kpp;
    public $inn;
    public $ogrn;

    /**
     * OrgEditForm constructor.
     * @param Organization $organization
     * @param array $config
     */
    public function __construct(Organization $organization, $config = [])
    {
        $this->_organization = $organization;
        $this->id = $organization->id;
        $this->name = $organization->name;
        $this->short_name = $organization->short_name;
        $this->kpp = $organization->kpp;
        $this->inn = $organization->inn;
        $this->ogrn = $organization->ogrn;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'short_name', 'kpp', 'inn', 'ogrn'], 'required'],
            [['name', 'short_name'],
            'unique',
            'targetClass' => Organization::class],
            ['kpp', 'match', 'pattern' => '/^[0-9]{9}$/'],
            ['inn', 'match', 'pattern' => '/^[0-9]{10}$/'],
            ['ogrn', 'match', 'pattern' => '/^[0-9]{13}$/'],
        ];
    }

    /**
     * @return bool
     */
    public function editOrg()
    {
        $attr = Yii::$app->request->post();
        $organization = $this->_organization;
        $organization->name = $attr['OrgEditForm']['name'];
        $organization->short_name = $attr['OrgEditForm']['short_name'];
        $organization->kpp = $attr['OrgEditForm']['kpp'];
        $organization->inn = $attr['OrgEditForm']['inn'];
        $organization->ogrn = $attr['OrgEditForm']['ogrn'];
        if($this->validate()) {
            return $organization->save();
        } else {
            return false;
        }
    }

}