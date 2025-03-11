<?php


namespace app\modules\adminv\models;


use app\models\db\Organizations;
use app\models\db\Pets;
use app\modules\animalid\models\db\Id_Map;
use yii\helpers\ArrayHelper;

class AnimalIdHelper
{
    public static function formatOurPet($id)
    {
        $out = '';
        $pet = Pets::findOne($id);
        if (!$pet)
            return '';
        $ident = $pet->getPet_identification()
            ->andWhere(['main_flag' => true])
            ->limit(1)
            ->one();

        if ($pet->sex) {
            $pet->sex == 'm' ? $sex = 'М' : $sex = 'Ж';
        } else {
            $sex = '-';
        }

        $out .= '<p class="small">' . 'Id - ' . ($pet->id) . '</p>';
        $out .= '<p class="small">' . 'Дата чипирования - ' . ($ident ? $ident->created_at : '') . '</p>';
        $out .= '<p class="small">' . 'Вид - ' . ($pet->species ? $pet->species->name : '') . '</p>';
        $out .= '<p class="small">' . 'Порода - ' . ($pet->breeds ? $pet->breeds->name : '') . '</p>';
        $out .= '<p class="small">' . 'Пол - ' . $sex . '</p>';
        $out .= '<p class="small">' . 'Дата рождения - ' . $pet->birthday . '</p>';
        $out .= '<p class="small">' . 'Организация - ' . ($pet->regOrganization ? $pet->regOrganization->short_name : '') . '</p>';

        return $out;
    }

    /**
     * @param $data
     * @throws \app\modules\animalid\skeletons\exceptions\IntegrationException
     */
    public static function formatTheirPet($data)
    {
        $chip = ArrayHelper::getValue($data, 'chip');
        $chip_date = ArrayHelper::getValue($data, 'chipdate');
        $species = ArrayHelper::getValue($data, 'kind');
        $breed = ArrayHelper::getValue($data, 'breed');
        $pet_sex = ArrayHelper::getValue($data, 'sex');
        $birthday = ArrayHelper::getValue($data, 'birthday');
        $org = ArrayHelper::getValue($data, 'companyid');

        $html = '';
        $inner = '';
        $sex = '';

        if ($pet_sex) {
            $pet_sex == 'самец' ? $sex = 'М' : $sex = 'Ж';
        }else{
            $pet_sex = '-';
        }

        $org_id = Id_Map::getOurByTheir($org, 'company');
        $org_name = Organizations::findOne($org_id) ? Organizations::findOne($org_id)->short_name : '';

        $inner .= '<p class="small">' . 'Id - ' . ($data['id']) . '</p>';
        $inner .= '<p class="small">' . 'Дата чипирования - ' . $chip_date . '</p>';
        $inner .= '<p class="small">' . 'Вид - ' . $species . '</p>';
        $inner .= '<p class="small">' . 'Порода - ' . $breed . '</p>';
        $inner .= '<p class="small">' . 'Пол - ' . $sex . '</p>';
        $inner .= '<p class="small">' . 'Дата рождения - ' . $birthday . '</p>';
        $inner .= '<p class="small">' . 'Организация - ' . $org_name . '</p>';

        return $inner;
    }

    public static function formatOurOrg($id)
    {
        $out = '';
        $org = Organizations::findOne($id);
        if (!$org) return '';

        /*
         * {"Id":41,
         * "FullName":"ООО \"\"ДЕЛЬТА-ВЕТ\"\"",
         * "Phone":"84993811033",
         * "Email":"deltaklin@gmail.com"}
         * "Address":"Россия, Москва, Булатниковский проезд, 14к7 ",
         */

        $out .= '<p class="small">' . 'Id - ' . ($org->id) . '</p>';
        $out .= '<p class="small">' . 'Полное наименование  - ' . $org->name . '</p>';
        $out .= '<p class="small">' . 'Телефон - ' . $org->getPhoneMain() . '</p>';
        $out .= '<p class="small">' . 'E-mail - ' . $org->getEmail() . '</p>';
        $out .= '<p class="small">' . 'Адрес - ' . ($org->fias_addresses ? $org->fias_addresses->full_address : '') . '</p>';

        return $out;
    }

    public static function formatTheirOrg($data)
    {
        $out = '';
        $out .= '<p class="small">' . 'Id - ' . ($data['id']) . '</p>';
        $out .= '<p class="small">' . 'Полное наименование  - ' . ArrayHelper::getValue($data, 'fullname') . '</p>';
        $out .= '<p class="small">' . 'Телефон - ' . ArrayHelper::getValue($data, 'phone') . '</p>';
        $out .= '<p class="small">' . 'E-mail - ' . ArrayHelper::getValue($data, 'email') . '</p>';
        $out .= '<p class="small">' . 'Адрес - ' . ArrayHelper::getValue($data, 'address') . '</p>';

        return $out;
    }

    /**
     * @param $data
     * @return null
     * @throws \app\modules\animalid\skeletons\exceptions\IntegrationException
     */
    public static function merge($data)
    {
        $type = ArrayHelper::getValue($data, 'type');
        if (!$type) return null;
        switch ($type) {
            case 'pet':
                $chip = ArrayHelper::getValue($data, 'chip');
                $chip_date = ArrayHelper::getValue($data, 'chipdate');
                $species = ArrayHelper::getValue($data, 'kind');
                $breed = ArrayHelper::getValue($data, 'breed');
                $pet_sex = ArrayHelper::getValue($data, 'sex');
                $birthday = ArrayHelper::getValue($data, 'birthday');
                $org = ArrayHelper::getValue($data, 'companyid');

                $id = Id_Map::getOurByTheir($data['id'], 'pet');
                $pet = Pets::findOne($id);
                if (!$pet)
                    return null;


                break;
            case 'company':
                $fullnae = ArrayHelper::getValue($data, 'fullname');
                $phone = ArrayHelper::getValue($data, 'phone');
                $mail = ArrayHelper::getValue($data, 'email');
                $address = ArrayHelper::getValue($data, 'address');

                $id = Id_Map::getOurByTheir($data['id'], 'company');
                break;
        }
    }
}
