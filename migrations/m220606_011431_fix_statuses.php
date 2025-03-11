<?php

use app\commands\migrate\Migration;
use app\models\db\ShelterGuests;

class m220606_011431_fix_statuses extends Migration
{
    public function safeUp()
    {
        ShelterGuests::updateAll(['status' => ShelterGuests::STATUS_QUARANTINE], ['status' => 'quarantine']);
        ShelterGuests::updateAll(['departure_reason' => ShelterGuests::DEPARTURE_REASON_DEATH], ['status' => ShelterGuests::DEPARTURE_REASON_DEATH]);
        ShelterGuests::updateAll(['departure_reason' => ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER], ['status' => ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER]);
        ShelterGuests::updateAll(['status' => ShelterGuests::STATUS_DEPARTURED], ['departure_reason' => [
            ShelterGuests::DEPARTURE_REASON_DEATH,
            ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER,
        ]]);
    }

    public function safeDown()
    {
        ShelterGuests::updateAll(['status' => ShelterGuests::DEPARTURE_REASON_DEATH], ['departure_reason' => ShelterGuests::DEPARTURE_REASON_DEATH]);
        ShelterGuests::updateAll(['status' => ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER], ['departure_reason' => ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER]);
        ShelterGuests::updateAll(['status' => 'quarantine'], ['departure_reason' => ShelterGuests::STATUS_QUARANTINE]);
    }

}
