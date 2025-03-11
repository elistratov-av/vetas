<?php

namespace app\modules\elk\types;

use yii\base\Model;

class Owner extends Model
{
    /**
     * @var string {nilable=false, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $SsoId;

    /**
     * @var string {nilable=false, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $LastName;

    /**
     * @var string {nilable=false, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $FirstName;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $MiddleName;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $Phone;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $Email;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $Snils;

    /**
     * @return array
     */
    public function getOwnerData() : array
    {
        return [
            'sso'
        ];
    }
}
