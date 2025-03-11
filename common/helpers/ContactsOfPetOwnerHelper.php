<?php

namespace app\common\helpers;

use app\models\db\PetOwners;

/**
 * Class ContactsOfPetOwnerHelper
 * @package app\common\helpers
 */
class ContactsOfPetOwnerHelper
{
    const priority = [
        'Мобильный телефон', 'Домашний телефон', 'Основной телефон', 'Рабочий телефон', 'Факс',
    ];

    /**
     * Возвращае приоритетные контакты
     * - номер телефона (при наличии нескольких контактов типа телефон (ContactType.type = phone)
     * выбор производится по приоритету от 1 – max приоритет до 5 – min приоритет:
     * Мобильный телефон – 1,
     * Домашний телефон – 2,
     * Основной телефон – 3,
     * Рабочий телефон – 4,
     * Факс – 5);
     * - электронная почта - первая попавшаяся.
     *
     * @param \app\models\db\PetOwners $pet_owner
     * @return array|null
     */
    public static function getContacts(PetOwners $pet_owner)
    {
        if (!$pet_owner) {
            return null;
        }

        $contacts = $pet_owner->contacts;
        if (empty($contacts)) {
            return null;
        }

        $mail = (array)array_filter($contacts, function ($x) {
            /* @var $x \app\models\db\Contacts */
            return $x->contactType->type == 'email';
        });

        $phone = (array)array_filter($contacts, function ($x) {
            return $x->contactType->type == 'phone';
        });

        if (count($mail) > 0) {
            //электронная почта - первая попавшаяся.
            $mail = array_shift($mail);
        }

        if (count($phone) > 0) {
            usort($phone, function ($a, $b) {
                return self::getSortOrder($a->contact_type->name) <=> self::getSortOrder($b->contact_type->name);
            });
            $phone = array_shift($phone);
        }

        return [
            'mail' => $mail,
            'phone' => $phone,
        ];
    }

    /**
     * @param $contactTypeName
     * @return false|int|string
     */
    private static function getSortOrder($contactTypeName)
    {
        $pos = array_search($contactTypeName, self::priority);

        return $pos !== false ? $pos : 999;
    }
}
