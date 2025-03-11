<?php


namespace app\modules\animalid\models;

class User
{
    public function loginByAccessToken($token){
        if($token === '123321')
            return true;
        else
            return false;
    }

    public static function getIdentity($id)
    {
        return new User();
    }

    public static function getId()
    {
        return 1;
    }

}
