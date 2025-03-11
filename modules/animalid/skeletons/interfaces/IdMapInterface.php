<?php


namespace app\modules\animalid\skeletons\interfaces;


interface IdMapInterface
{
    public static function getOurByTheir(int $their, string $type);

    public static function getTheirByOur(int $our, string $type);

    public function save();
}
