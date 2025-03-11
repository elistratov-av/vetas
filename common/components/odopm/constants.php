<?php

$test = [
    'dictionaries' => [
        'days' => 66,
        'entryState' => 89,
        'yesNo' => 553,
        'respDepartments' => 2449,
        'reasonAdd' => 2451,
        'reasonDelete' => 2452,
    ],

    'parentCatalogs' => [
        'VET_ORGANIZATIONS_ID' => 5547,
        'CORPSES_STATION_ID' => 5550,
        'REGISTRATION_STATION_ID' => 5553,
        'VACCINATION_STATION_ID' => 5554
    ],
    'childCatalogs' => [
        'VET_PHONES_ID' => 5548,
        'VET_SCHEDULE_ID' => 5549,
        'REGISTRATION_PHONES_ID' => 5557,
        'REGISTRATION_SCHEDULE_ID' => 5558,
        'CORPSES_PHONES_ID' => 5551,
        'CORPSES_SCHEDULE_ID' => 5552,
        'VACCINATION_PHONES_ID' => 5555,
        'VACCINATION_SCHEDULE_ID' => 5556,
    ],
    'parentAttributes' => [
        'REGISTRATION_PARENT_ATTR_PHONE' => 62490,
        'VET_PARENT_ATTR_PHONE' => 62322,
        'CORPSES_PARENT_ATTR_PHONE' => 62338,
        'VACCINATION_PARENT_ATTR_PHONE' => 62475,
        'REGISTRATION_PARENT_ATTR_SCHEDULE' => 62491,
        'VET_PARENT_ATTR_SCHEDULE' => 62323,
        'CORPSES_PARENT_ATTR_SCHEDULE' => 62339,
        'VACCINATION_PARENT_ATTR_SCHEDULE' => 62476
    ],
    'childAttributes' => [
        'REGISTRATION_CHILD_ATTR_PHONE' => 62492,
        'REGISTRATION_CHILD_ATTR_SCHEDULE_DAY' => 62493,
        'REGISTRATION_CHILD_ATTR_SCHEDULE_HOURS' => 62494,

        'CORPSES_CHILD_ATTR_PHONE' => 62341,
        'CORPSES_CHILD_ATTR_SCHEDULE_DAY' => 62342,
        'CORPSES_CHILD_ATTR_SCHEDULE_HOURS' => 62343,

        'VET_CHILD_ATTR_PHONE' => 62326,
        'VET_CHILD_ATTR_SCHEDULE_DAY' => 62327,
        'VET_CHILD_ATTR_SCHEDULE_HOURS' => 62328,

        'VACCINATION_CHILD_ATTR_PHONE' => 62478,
        'VACCINATION_CHILD_ATTR_SCHEDULE_DAY' => 62479,
        'VACCINATION_CHILD_ATTR_SCHEDULE_HOURS' => 62480
    ],
];

$prod = [
    'dictionaries' => [
        'days' => 66,
        'entryState' => 89,
        'yesNo' => 553,
        'respDepartments' => 2449,
        'reasonAdd' => 2451,
        'reasonDelete' => 2452,
    ],

    'parentCatalogs' => [
        'VET_ORGANIZATIONS_ID' => 251,
        'CORPSES_STATION_ID' => 100015,
        'REGISTRATION_STATION_ID' => 102566,
        'VACCINATION_STATION_ID' => 100039
    ],
    'childCatalogs' => [
        'VET_PHONES_ID' => 1400,
        'VET_SCHEDULE_ID' => 1401,
        'REGISTRATION_PHONES_ID' => 102572,
        'REGISTRATION_SCHEDULE_ID' => 102571,
        'CORPSES_PHONES_ID' => 100021,
        'CORPSES_SCHEDULE_ID' => 100029,
        'VACCINATION_PHONES_ID' => 100040,
        'VACCINATION_SCHEDULE_ID' => 100041,
    ],
    'parentAttributes' => [
        'REGISTRATION_PARENT_ATTR_PHONE' => 1160842,
        'VET_PARENT_ATTR_PHONE' => 17803,
        'CORPSES_PARENT_ATTR_PHONE' => 1148058,
        'VACCINATION_PARENT_ATTR_PHONE' => 1148098,
        'REGISTRATION_PARENT_ATTR_SCHEDULE' => 1160838,
        'VET_PARENT_ATTR_SCHEDULE' => 6090,
        'CORPSES_PARENT_ATTR_SCHEDULE' => 1148066,
        'VACCINATION_PARENT_ATTR_SCHEDULE' => 1148099
    ],
    'childAttributes' => [
        'REGISTRATION_CHILD_ATTR_PHONE' => 1162520,
        'REGISTRATION_CHILD_ATTR_SCHEDULE_DAY' => 1160840,
        'REGISTRATION_CHILD_ATTR_SCHEDULE_HOURS' => 1162521,

        'CORPSES_CHILD_ATTR_PHONE' => 1148059,
        'CORPSES_CHILD_ATTR_SCHEDULE_DAY' => 1148068,
        'CORPSES_CHILD_ATTR_SCHEDULE_HOURS' => 1148069,

        'VET_CHILD_ATTR_PHONE' => 21540,
        'VET_CHILD_ATTR_SCHEDULE_DAY' => 21538,
        'VET_CHILD_ATTR_SCHEDULE_HOURS' => 21539,

        'VACCINATION_CHILD_ATTR_PHONE' => 1148101,
        'VACCINATION_CHILD_ATTR_SCHEDULE_DAY' => 1148102,
        'VACCINATION_CHILD_ATTR_SCHEDULE_HOURS' => 1148103
    ],
];


return [
    'test' => $test,
    'prod' => $prod
];
