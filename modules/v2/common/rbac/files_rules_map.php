<?php

//use app\models\db\Drugs;
use app\models\db\Faqs;
use app\models\db\Pets;
use app\models\db\RegCertificates;
use app\models\db\Specialists;
use app\models\db\SpecialistsUpdateReasons;
//use app\models\db\Vaccines;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitsGovServices;
use app\models\db\ViolationAdminRights;
use app\models\db\Help;
use app\modules\v2\modules\pets\models\PassportModel;

/*
 * Не забывать указывать use иначе в ключе будет неверный путь
 * и checkAccess будет давать true
 */

return [
    /*
    Drugs::class => [
        'data.classificators.drugs.W'
    ],
    */
    /*
    Vaccines::class => [
        'data.classificators.vaccines.W'
    ],
    */
    Pets::class => [
        'data.pets.manage.U',
        'data.pets.manage.W',
    ],
    RegCertificates::class => [
        'data.pets.reg_certificates',
    ],
    Specialists::class => [
        'data.specialists.manage.W',
        'admin.users.manage.W',
    ],
    VisitServiceParamValues::class => [
        'parent' => Visits::class,
        'key' => 'id_visit',
        'via' => [
            'class' => VisitsGovServices::class,
            'key' => 'id_visitservice',
        ],
        'permissions' => [
            'activity.visits.manage.W',
            'activity.visits.edit',
            'activity.visits.edit.new',
            // TODO
            // 'ambulance.visits.edit',
            // 'ambulance.visits.edit.new',
        ],
    ],
    VisitsGovServices::class => [
        'parent' => Visits::class,
        'key' => 'id_visit',
        'permissions' => [
            'activity.visits.manage.W',
            'activity.visits.edit',
            'activity.visits.edit.new',
            // TODO
            // 'ambulance.visits.edit',
            // 'ambulance.visits.edit.new',
        ]
    ],
    SpecialistsUpdateReasons::class => [
        'parent' => Specialists::class,
        'key' => 'id_specialist',
        'permissions' => [
            'data.specialists.manage.W',
            'admin.users.manage.W',
        ]
    ],
    Faqs::class => [
        'data.faqs.manage.W',
    ],
    ViolationAdminRights::class => [
        'data.gosvetnadzor.violation_admin_rights.W',
    ],
    Help::class => [
        'data.help.manage.W',
    ],
    PassportModel::class => [
        'data.pets.manage'
    ],
];
