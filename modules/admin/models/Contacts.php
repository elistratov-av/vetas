<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.12.18
 * Time: 13:33
 */

namespace app\modules\admin\models;

/**
 * Class Contacts
 * @package app\modules\admin\models
 */
class Contacts extends \app\models\db\Contacts
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegCertificatePhone()
    {
        return $this->hasOne(RegCertificates::class, ['phone' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegCertificateMail()
    {
        return $this->hasOne(RegCertificates::class, ['mail' => 'id']);
    }
}
