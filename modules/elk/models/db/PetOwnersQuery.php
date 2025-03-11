<?php

namespace app\modules\elk\models\db;

use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class PetOwnersQuery
 * @package app\modules\soap\models
 * @see PetOwners
 */
class PetOwnersQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        $this->andWhere(['is_deleted' => false]);

        return $this;
    }

    /**
     * @param string $sso_id
     * @return $this
     */
    public function bySsoId(string $sso_id)
    {
        $this->andWhere(['sso_id' => $sso_id]);

        return $this;
    }

    /**
     * @param string $snils
     * @return $this
     */
    public function bySnils(string $snils)
    {
        $snils = preg_replace("/[^0-9]/", "", $snils);

        if (!empty($snils)) {
            $this->andWhere(['snils' => $snils]);
        } else {
            $this->emulateExecution();
        }

        return $this;
    }

    /**
     * @param string $f_fio
     * @param string $i_fio
     * @param null|string $o_fio
     * @return $this
     */
    public function byFio(string $f_fio, string $i_fio, ?string $o_fio = '')
    {
        $this->andWhere(new Expression("lower(f_fio) = :f_fio", [
                'f_fio' => mb_strtolower($f_fio)
            ]))
            ->andWhere(new Expression("lower(i_fio) = :i_fio", [
                'i_fio' => mb_strtolower($i_fio)
            ]))
            ->andWhere(new Expression("lower(COALESCE(o_fio, '')) = :o_fio", [
                'o_fio' => isset($o_fio) ? mb_strtolower($o_fio) : ''
            ]));

        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function byMobilePhone(string $phone)
    {
        $this->joinWith('contacts', false, 'INNER JOIN')
            ->andWhere(['contacts.name' => $phone]);

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function byEmail(string $email)
    {
        $this->joinWith('contacts', false, 'INNER JOIN')
            ->andWhere(['contacts.name' => $email]);

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutSnils()
    {
        $this->andWhere(new Expression("COALESCE(snils, '') = ''"));

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutSsoId()
    {
        $this->andWhere(new Expression("COALESCE(sso_id, '') = ''"));

        return $this;
    }
}
