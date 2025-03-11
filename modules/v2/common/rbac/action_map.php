<?php

return [
    // \app\modules\v2\modules\pets\controllers\RegCertificatesController
    'v2/pets/reg-certificates/get' => [
        'data.owners.manage',
        'data.pets.manage',
    ],
    'v2/pets/reg-certificates/get-updates' => [
        'data.owners.manage',
        'data.pets.manage',
    ],
    'v2/pets/reg-certificates/create' => [
        'data.pets.reg_certificates',
    ],
    'v2/pets/reg-certificates/update' => [
        'data.pets.reg_certificates',
    ],
    'v2/pets/reg-certificates/delete' => [
        'data.pets.reg_certificates',
    ],
    // \app\modules\v2\modules\visit\controllers\DescriptionsController
    'v2/visit/descriptions/types' => [
        // 'activity.visits.manage',
        // 'activity.visits.descriptions',
    ],
    'v2/visit/descriptions/get' => [
        'activity.visits.manage',
        'activity.visits.descriptions',
        // (+ AllOrgsCompositeRule5 VETAIS-3228)
        'activity.visits.view',
    ],
    'v2/visit/descriptions/save' => [
        // п. 85 Управление приемом: управление данными приема
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.descriptions',
    ],
    'v2/visit/descriptions/file' => [
        'activity.visits.manage',
        'activity.visits.descriptions',
        // (+ AllOrgsCompositeRule5 VETAIS-3228)
        'activity.visits.view',
    ],
    // \app\modules\v2\modules\visit\controllers\ServicesController
    'v2/visit/services/list' => [
        // 'activity.visits.manage',
        // (+ AllOrgsCompositeRule5 VETAIS-3228)
        'activity.visits.view',
    ],
    'v2/visit/services/add' => [
        // п. 83 "Управление приемом: редактирование набора услуг"
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.services-edit',
    ],
    'v2/visit/services/edit' => [
        // п. 83 "Управление приемом: редактирование набора услуг"
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.services-edit',
    ],
    'v2/visit/services/delete' => [
        // п. 83 "Управление приемом: редактирование набора услуг"
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.services-edit',
    ],
    // \app\modules\v2\modules\visit\controllers\VisitController
    'v2/visit/visit/list' => [
        // п. 76 "Учет приемов"
        // (+ AllOrgsCompositeRule1)
        // "Пользователю доступны данные связанные с действующим местом работы пользователя, где ему
        // выдана соответствующая роль, и дочерними организациями."
        'activity.visits.manage',
        // (+ AllOrgsCompositeRule5 VETAIS-3228)
        'activity.visits.view',
    ],
    'v2/visit/visit/get' => [
        // п. 76 "Учет приемов"
        // (+ AllOrgsCompositeRule1)
        // "Пользователю доступны данные связанные с действующим местом работы пользователя, где ему
        // выдана соответствующая роль, и дочерними организациями."
        'activity.visits.manage',
        'activity.visits.edit',
        'activity.visits.edit.new',
        // (+ AllOrgsCompositeRule5 VETAIS-3228)
        'activity.visits.view',
    ],
    'v2/visit/visit/create' => [
        // п. 77 "Учет приемов: создание приема" (дублирует п. 71 "Запись на прием")
        // нет специальных ограничений
        'activity.visits.create',
        'activity.visits.manage.W',
    ],
    'v2/visit/visit/edit' => [
        // редактирование приема, не предусмотренное п. 78 (отсутствует в таблице, но нужно)
        // (+ VisitCompositeRule2)
        // регистратура - действующее место работы, ветспециалист - специалист приема
        'activity.visits.edit',
        // п. 78 Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу"
        // (+ VisitCompositeRule1)
        // "Пользователю доступны данные связанные с действующим местом работы.
        // Приемы с каналом "направление" также доступны автору направления."
        'activity.visits.edit.new',
    ],
    'v2/visit/visit/start' => [
        // п. 80 Управление приемом: взятие приема в работу
        // (+ UserOrgRule)
        // "Пользователю доступны данные связанные с действующим местом работы"
        'activity.visits.start',
    ],
    'v2/visit/visit/finish' => [
        // п. 86 Управление приемом: завершение приема
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.finish',
        'activity.visits.manage.finish', // завершение приема (для менеджеров|регистратуры в пределах организации)
    ],
    'v2/visit/visit/generate-xml' => [
        // п. 86 Управление приемом: завершение приема
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.finish',
    ],
    'v2/visit/visit/sign' => [
        // п. 86 Управление приемом: завершение приема
        // (+ VisitSpecialistRule)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.finish',
    ],
    'v2/visit/visit/cancel' => [
        // п. 87 Управление приемом: отмена приема в состоянии "В работе"
        // Но! Есть также у роли registryGos, поэтому заменил правило
        // (+ VisitCompositeRule2)
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.cancel',
    ],
    'v2/visit/visit/confirm-payment' => [
        // п. 79 Управление приемом: управление состоянием оплаты приема
        'activity.visits.confirm-payment', // (VisitSpecialistRule)
        'activity.visits.manage.confirm-payment', // (UserOrgRule)
    ],
    'v2/visit/visit/edit-preferences' => [
        // редактирование приема, не предусмотренное п. 78 (отсутствует в таблице, но нужно)
        // (+ VisitCompositeRule2)
        // регистратура - действующее место работы, ветспециалист - специалист приема
        'activity.visits.edit',
        // п. 79 Управление приемом: управление состоянием оплаты приема
        // (+ UserOrgRule)
        // "Пользователю доступны данные связанные с действующим местом работы"
        'activity.visits.confirm-payment', // (VisitSpecialistRule)
        'activity.visits.manage.confirm-payment', // (UserOrgRule)
    ],
    'v2/visit/visit/remove-pet' => [
        // "Пользователю доступны приемы, где он является специалистом приема"
        'activity.visits.services-edit',
    ],
    'v2/visit/visit/specialistworkday' => [],
    'v2/visit/visit/referrals' => [],
    // \app\modules\v2\modules\visit\controllers\ServiceTmcsController
    'v2/visit/service-tmcs/save' => [
        'activity.visits.tmc',
    ],
    'v2/visit/violation/report' => [
        // + VisitSpecialistRule
        'activity.visits.violation.report',
    ],
    'v2/visit/service-tmcs/save-equipments' => [
        'activity.visits.tmc',
    ],
    // \app\modules\v2\modules\organization\controllers\ServicesWithoutMarkUpController
    'v2/organization/services-without-mark-up/get' => [
        'activity.visits.manage',
        'data.pricelist.services',
    ],
    'v2/organization/services-without-mark-up/save' => [
        'data.organizations.manage.W',
    ],
    // \app\modules\v2\modules\discount\controllers\DiscountController
    // скидки у нас по БД вообще не привязаны ни к какой к организации - просто не предусмотрено
    // а для 'activity.visits.manage' и 'data.pricelist.services' действуют org rules!
    'v2/discount/discount/all' => [
        // 'activity.visits.manage',
        'data.pricelist.services',
    ],
    'v2/discount/discount/get' => [
        // 'activity.visits.manage',
        'data.pricelist.services',
    ],
    'v2/discount/discount/create' => [
        'data.pricelist.services.W',
        'data.pricelist.discount.W',
    ],
    'v2/discount/discount/edit' => [
        'data.pricelist.services.W',
        'data.pricelist.discount.W',
    ],
    'v2/discount/discount/delete' => [
        'data.pricelist.services.W',
        'data.pricelist.discount.W',
    ],
    // \app\modules\v2\modules\pricelist\controllers\MosRuController
    'v2/pricelist/mos-ru/get' => [
        'activity.visits.manage',
        'data.pricelist.services',
    ],
    'v2/pricelist/mos-ru/save' => [
        'data.pricelist.services.W',
        'data.pricelist.mos-ru-services.W',
    ],
    // \app\modules\v2\modules\pricelist\controllers\ServicesController
    // на данный момент не реализовано в контроллере
    'v2/pricelist/services/list' => [
        // 'activity.visits.manage',
        // 'data.pricelist.services',
        'data.pricelist.services',
    ],
    'v2/pricelist/services/get' => [
        // 'activity.visits.manage',
        // 'data.pricelist.services',
        'data.pricelist.services',
    ],
    'v2/pricelist/services/create' => [
        'data.pricelist.services.W',
    ],
    'v2/pricelist/services/edit' => [
        'data.pricelist.services.W',
    ],
    'v2/pricelist/services/delete' => [
        'data.pricelist.services.W',
    ],

    // настройка обязательности полей описания для каждой услуги
    '/v2/services/services-description-types/save' => [
        'data.pricelist.services.W',
    ],

    // app\modules\v2\modules\organization\controllers\OutsideOrganizationController
    'v2/organization/outside-organization/list' => [
        'data.outside-organization.manage',
    ],

    // \app\modules\v2\modules\petOwners\controllers\PetOwnerController
    'v2/pet-owners/pet-owner/list' => [
        'data.owners.manage',
    ],
    'v2/pet-owners/pet-owner/get' => [
        'data.owners.manage',
    ],
    'v2/pet-owners/pet-owner/create' => [
        'data.owners.manage.W',
    ],
    'v2/pet-owners/pet-owner/edit' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/pet-owner/delete' => [
        'data.owners.manage.W',
    ],
    // \app\modules\v2\modules\petOwners\controllers\ContactsController
    'v2/pet-owners/contacts/types' => [
        // 'data.owners.manage',
    ],
    'v2/pet-owners/contacts/list' => [
        'data.owners.manage',
    ],
    'v2/pet-owners/contacts/get' => [
        'data.owners.manage',
    ],
    'v2/pet-owners/contacts/create' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/contacts/edit' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/contacts/delete' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    // \app\modules\v2\modules\petOwners\controllers\PetsController
    'v2/pet-owners/pets/list' => [
        'data.owners.manage',
        'data.pets.manage',
    ],
    // \app\modules\v2\modules\specializations\controllers\SpecializationController
    'v2/specializations/specialization/list' => [
        'data.pets.manage',
    ],

    // \app\modules\v2\modules\pets\controllers\PetController
    'v2/pets/pet/list' => [
        'data.pets.manage',
    ],
    'v2/pets/pet/get' => [
        'data.pets.manage',
    ],
    'v2/pets/pet/create' => [
        'data.pets.manage.W',
    ],
    'v2/pets/pet/edit' => [
        'data.pets.manage.U',
        'data.pets.manage.W',
    ],
    // \app\modules\v2\modules\petOwners\controllers\DuplicatesController
    'v2/pet-owners/duplicates/check' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/link' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/unlink' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/undo-main' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/check-pets' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/main-pet' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/undo-main-pet' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/link-pets' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    'v2/pet-owners/duplicates/unlink-pet' => [
        'data.owners.manage.U',
        'data.owners.manage.W',
    ],
    // \app\modules\v2\modules\pets\controllers\RegExpireReasonsController::actionList
    'v2/pets/reg-expire-reasons/list' => [
        // 'data.classificators.reg_expire_reasons',
        // 'data.pets.manage',
    ],
    // \app\modules\v2\modules\pets\controllers\OwnerController
    'v2/pets/owner/list' => [
        'data.pets.manage',
        'data.owners.manage',
    ],
    'v2/pets/owner/get' => [
        'data.pets.manage',
        'data.owners.manage',
    ],
    'v2/pets/owner/type' => [
        'data.pets.manage',
        'data.owners.manage',
    ],
    'v2/pets/owner/history' => [
        'data.pets.manage',
        'data.owners.manage',
    ],
    'v2/pets/owner/create' => [
        'data.pets.manage.U',
        'data.pets.manage.W',
    ],
    'v2/pets/owner/edit' => [
        'data.pets.manage.U',
        'data.pets.manage.W',
    ],
    'v2/pets/owner/delete' => [
        'data.pets.manage.U',
        'data.pets.manage.W',
    ],
    // \app\modules\v2\modules\pets\controllers\IdentController
    'v2/pets/ident/types' => [
        // 'data.pets.manage',
        // 'data.owners.manage',
        // 'data.pets.vaccination-identification',
    ],
    'v2/pets/ident/save' => [
        'data.pets.manage.U',
        'data.pets.manage.W',
    ],
    // \app\modules\v2\modules\pets\controllers\BroodController
    'v2/pets/brood/create' => [
        'data.pets.manage.W',
    ],
    'v2/pets/brood/edit' => [
        'data.pets.manage.W',
    ],
    'v2/pets/brood/delete' => [
        'data.pets.manage.W',
    ],
    'v2/pets/brood/add-pet' => [
        'data.pets.manage.W',
    ],
    'v2/pets/brood/remove-pet' => [
        'data.pets.manage.W',
    ],
    'v2/pets/brood/get' => [
        'data.pets.manage',
        'data.pets.manage.W',
    ],
    'v2/pets/brood/list-pets' => [
        'data.pets.manage',
        'data.pets.manage.W',
    ],
    'v2/pets/brood/list-broods' => [
        'data.pets.manage',
        'data.pets.manage.W',
    ],
    'v2/pet-hotel/item/all' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/item/create' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/item/delete' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/item/edit' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/item/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/item/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/all' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/create' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/delete' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/edit' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/room/free' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/owner/create' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/owner/delete' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/owner/edit' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/owner/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/owner/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/animal/create' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/animal/delete' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/animal/edit' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/animal/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/animal/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/animal/count-by-type' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/status/all' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/status/create' => [
        'sysAdminGos',
    ],
    'v2/pet-hotel/status/delete' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/status/edit' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/status/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/status/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/species/all' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/species/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/species/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/breed/all' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/breed/create' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/breed/edit' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/breed/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/breed/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/all' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/all-by-hotel' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/create' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/delete' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/edit' => [
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/get' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/search' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/count-by-status' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/upload-contract' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    'v2/pet-hotel/request/uploaded-contract' => [
        'data.pet-hotel.user',
        'data.pet-hotel.admin',
    ],
    // \app\modules\v2\modules\timesheet\controllers\TimesheetController
    'v2/timesheet/timesheet/list' => [
        'registry.schedule.timesheets',
        'registry.schedule.shifts',
        'registry.schedule.manage',
        'activity.visits.manage',
    ],
    'v2/timesheet/timesheet/xlsx' => [
        'registry.schedule.timesheets',
        'registry.schedule.shifts',
        'registry.schedule.manage',
        'activity.visits.manage',
    ],
    'v2/timesheet/timesheet/save' => [
        'registry.schedule.timesheets',
    ],
    'v2/timesheet/timesheet/copy' => [
        'registry.schedule.timesheets',
    ],
    // \app\modules\v2\modules\timesheet\controllers\ShiftController
    'v2/timesheet/shift/list' => [
        'registry.schedule.timesheets',
        'registry.schedule.shifts',
        'registry.schedule.manage',
        'activity.visits.manage',
    ],
    'v2/timesheet/shift/get' => [
        'registry.schedule.manage',
        'registry.schedule.shifts',
        'registry.schedule.manage',
        'activity.visits.manage',
    ],
    'v2/timesheet/shift/save' => [
        'registry.schedule.shifts',
    ],
    'v2/timesheet/shift/delete' => [
        'registry.schedule.shifts',
    ],

    // Администрирование
    'v2/administration/user/list' => [
        'data.administration.admin',
    ],
    'v2/administration/user/create' => [
        'data.administration.admin',
    ],
    'v2/administration/user/update' => [
        'data.administration.admin',
    ],
    'v2/administration/user/get' => [
        'data.administration.admin',
    ],
    'v2/administration/user/organization' => [
        'data.administration.admin',
    ],
    'v2/administration/user/specialization' => [
        'data.administration.admin',
    ],
    'v2/administration/user/reg-organization' => [
        'data.administration.admin',
    ],
    'v2/administration/user/expel-organization' => [
        'data.administration.admin',
    ],
    'v2/administration/user/add-specialization' => [
        'data.administration.admin',
    ],
    'v2/administration/user/delete-specialization' => [
        'data.administration.admin',
    ],
    'v2/administration/user/add-role' => [
        'data.administration.admin',
    ],
    'v2/administration/user/delete-role' => [
        'data.administration.admin',
    ],
    'v2/administration/user/role-list' => [
        'data.administration.admin',
    ],

    'v2/administration/role/list' => [
        'data.administration.admin',
    ],
    'v2/administration/role/elements' => [
        'data.administration.admin',
    ],
    'v2/administration/role/add-elements' => [
        'data.administration.admin',
    ],
    'v2/administration/role/delete-elements' => [
        'data.administration.admin',
    ],

    // Типы смен
    'v2/shift-type-ref/shift-type-ref/all' => [
        'data.shift_type_ref.user',
    ],
    'v2/shift-type-ref/shift-type-ref/get-by-title' => [
        'data.shift_type_ref.user',
    ],
    'v2/shift-type-ref/shift-type-ref/get-by-id' => [
        'data.shift_type_ref.user',
    ],
    'v2/shift-type-ref/shift-type-ref/create' => [
        'data.shift_type_ref.admin',
    ],
    'v2/shift-type-ref/shift-type-ref/delete' => [
        'data.shift_type_ref.admin',
    ],

    // Недопустимые пересечения типов смен
    'v2/shift-type-ref/invalid-intersections/all' => [
        'data.shift_type_invalid_intersections.user',
    ],
    'v2/shift-type-ref/invalid-intersections/get' => [
        'data.shift_type_invalid_intersections.user',
    ],
    'v2/shift-type-ref/invalid-intersections/get-by-id-type' => [
        'data.shift_type_invalid_intersections.user',
    ],
    'v2/shift-type-ref/invalid-intersections/get-by-organization-id' => [
        'data.shift_type_invalid_intersections.user',
    ],
    'v2/shift-type-ref/invalid-intersections/create' => [
        'data.shift_type_invalid_intersections.admin',
    ],

    // \app\modules\v2\modules\specialist\controllers\SpecialistController
    'v2/specialist/specialist/list' => [
        'data.specialists.manage',
        'activity.visits.manage',
        'admin.users.manage',
    ],
    'v2/specialist/specialist/get' => [
        'data.specialists.manage',
        'activity.visits.manage',
        'admin.users.manage',
    ],
    'v2/specialist/specialist/create' => [
        'data.specialists.manage.W',
        // 'admin.users.manage.W',
    ],
    'v2/specialist/specialist/edit' => [
        'data.specialists.manage.W',
        // 'admin.users.manage.W',
    ],
    'v2/specialist/specialist/delete' => [
        'data.specialists.manage.W',
        // 'admin.users.manage.W',
    ],

    // Управление шаблонами (дополнительная валидация на права - в модели)
    'v2/visit/descriptions-templates/list' => [
        'data.descriptions-templates.manage',
        'activity.visits.descriptions-templates',
    ],
    'v2/visit/descriptions-templates/get' => [
        'data.descriptions-templates.manage',
        'activity.visits.descriptions-templates',
    ],
    'v2/visit/descriptions-templates/create' => [
        'data.descriptions-templates.manage',
        'activity.visits.descriptions-templates',
    ],
    'v2/visit/descriptions-templates/edit' => [
        'data.descriptions-templates.manage',
    ],
    'v2/visit/descriptions-templates/delete' => [
        'data.descriptions-templates.manage',
    ],
    'v2/visit/descriptions-templates/types' => [
        'data.descriptions-templates.manage',
        'activity.visits.descriptions-templates',
    ],

    // приемы НВП (выездные бригады)
    'v2/visit/ambulance/list' => [
        'ambulance.visits.manage',
    ],
    'v2/visit/ambulance/get' => [
        'ambulance.visits.manage',
    ],
    'v2/visit/ambulance/create' => [
        'ambulance.visits.create',
    ],
    'v2/visit/ambulance/edit' => [
        'ambulance.visits.edit',
        'ambulance.visits.edit.new',
    ],
    'v2/visit/ambulance/start' => [
        'ambulance.visits.start',
    ],
    'v2/visit/ambulance/finish' => [
        'ambulance.visits.finish',
        'activity.visits.manage.finish', // завершение приема (для менеджеров|регистратуры в пределах организации)
    ],
    'v2/visit/ambulance/cancel' => [
        'ambulance.visits.cancel',
    ],
    'v2/visit/ambulance/confirm-payment' => [
        'ambulance.visits.confirm-payment', // (VisitSpecialistRule)
        'activity.visits.manage.confirm-payment', // (UserOrgRule)
    ],
    'v2/visit/ambulance/edit-preferences' => [
        'ambulance.visits.edit',
        'ambulance.visits.confirm-payment', // (VisitSpecialistRule)
        'activity.visits.manage.confirm-payment', // (UserOrgRule)
    ],
    'v2/visit/ambulance/datelist' => [
        'ambulance.visits.manage',
        'ambulance.schedule.manage',
    ],
    'v2/visit/ambulance/timelist' => [
        'ambulance.visits.manage',
        'ambulance.schedule.manage',
    ],
    'v2/visit/ambulance/sign' => [
        'ambulance.visits.sign',
    ],

    // журналы
    'v2/reports/journal/records' => [
        'data.journals.manage',
    ],
    'v2/reports/journal/file' => [
        'data.journals.manage',
    ],
    'v2/reports/journal/visit-records' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/print' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/export' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/service-types' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/services' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/identification-types' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/species' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/pets' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/visit-types' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/channels' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/discount-types' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],
    'v2/reports/journal/owners' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
    ],

    //справка
    'v2/faq/faq/all' => [
        'data.faqs.manage',
    ],
    'v2/faq/faq/get' => [
        'data.faqs.manage',
    ],
    'v2/faq/faq/create' => [
        'data.faqs.manage.W',
    ],
    'v2/faq/faq/edit' => [
        'data.faqs.manage.W',
    ],
    'v2/faq/faq/delete' => [
        'data.faqs.manage.W',
    ],

    //ссылки справкок
    'v2/faq/links/list' => [
        'data.faqs.manage',
    ],
    'v2/faq/links/get' => [
        'data.faqs.manage',
    ],
    'v2/faq/links/create' => [
        'data.faqs.manage.W',
    ],
    'v2/faq/links/edit' => [
        'data.faqs.manage.W',
    ],
    'v2/faq/links/delete' => [
        'data.faqs.manage.W',
    ],

    // справки
    'v2/faq2/item/all' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],
    'v2/faq2/item/create' => [
        'data.faq2.admin',
    ],
    'v2/faq2/item/delete' => [
        'data.faq2.admin',
    ],
    'v2/faq2/item/edit' => [
        'data.faq2.admin',
    ],
    'v2/faq2/item/get' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],
    'v2/faq2/item/search' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],

    // группы справок
    'v2/faq2/group/all' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],
    'v2/faq2/group/create' => [
        'data.faq2.admin',
    ],
    'v2/faq2/group/delete' => [
        'data.faq2.admin',
    ],
    'v2/faq2/group/edit' => [
        'data.faq2.admin',
    ],
    'v2/faq2/group/get' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],
    'v2/faq2/group/get-by-code' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],
    'v2/faq2/group/get-items' => [
        'data.faq2.admin',
        'data.faq2.user',
    ],
    'v2/faq2/group/add-item' => [
        'data.faq2.admin',
    ],
    'v2/faq2/group/del-item' => [
        'data.faq2.admin',
    ],

    // Госветнадзор: АПН
    'v2/gosvetnadzor/violation-admin-rights/all' => [
        'data.gosvetnadzor.violation_admin_rights',
        'data.gosvetnadzor.violation_admin_rights.W',
    ],
    'v2/gosvetnadzor/violation-admin-rights/create' => [
        'data.gosvetnadzor.violation_admin_rights.W',
    ],
    'v2/gosvetnadzor/violation-admin-rights/delete' => [
        'data.gosvetnadzor.violation_admin_rights.W',
    ],
    'v2/gosvetnadzor/violation-admin-rights/edit' => [
        'data.gosvetnadzor.violation_admin_rights.W',
    ],
    'v2/gosvetnadzor/violation-admin-rights/get' => [
        'data.gosvetnadzor.violation_admin_rights',
        'data.gosvetnadzor.violation_admin_rights.W',
    ],
    'v2/gosvetnadzor/violation-admin-rights/list' => [
        'data.gosvetnadzor.violation_admin_rights',
        'data.gosvetnadzor.violation_admin_rights.W',
    ],

    // Госветнадзор: справочник тип нарушения
    'v2/gosvetnadzor/violation-type/all' => [
        'data.gosvetnadzor.violation_type',
    ],

    // Госветнадзор: работа с нарушениями
    'v2/gosvetnadzor/violation/edit' => [
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/violation/start' => [
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/violation/cancel' => [
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/violation/finish' => [
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/violation/get' => [
        'gosvetnadzor.violation.manage',
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/violation/list' => [
        'gosvetnadzor.violation.manage',
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/violation/planned-save' => [
        // сохранение даты плановой вакцинации и идентификации в нарушениях
        'gosvetnadzor.violation.manage.W'
    ],

    // Госветнадзор: работа с нарушениями - история
    'v2/gosvetnadzor/violation-history/get' => [
        'gosvetnadzor.violation.manage',
        'gosvetnadzor.violation.manage.W'
    ],

    // Госветнадзор: запрос выписок из АС УР
    'v2/gosvetnadzor/registry/get' => [
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/gosvetnadzor/registry/status' => [
        'gosvetnadzor.violation.manage.W'
    ],

    // Карантин
    'v2/quarantine/quarantines/get' => [
        'quarantine.manage.quarantine.R',
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/list' => [
        'quarantine.manage.quarantine.R',
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/add' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/edit' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/add-territory' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/edit-territory' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/remove-territory' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/finish' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/quarantine/quarantines/animals' => [
        'quarantine.manage.animals.R',
        'quarantine.manage.animals.W'
    ],

    // справка для сотрудников
    'v2/help/help/all' => [
        'data.help.manage',
    ],
    'v2/help/help/get' => [
        'data.help.manage',
    ],
    'v2/help/help/create' => [
        'data.help.manage.W',
    ],
    'v2/help/help/edit' => [
        'data.help.manage.W',
    ],
    'v2/help/help/delete' => [
        'data.help.manage.W',
    ],

    // ссылки справок для сотрудников
    'v2/help/links/list' => [
        'data.help.manage',
    ],
    'v2/help/links/get' => [
        'data.help.manage',
    ],
    'v2/help/links/create' => [
        'data.help.manage.W',
    ],
    'v2/help/links/edit' => [
        'data.help.manage.W',
    ],
    'v2/help/links/delete' => [
        'data.help.manage.W',
    ],

    // приюты
    'v2/shelter/pets/get' => [
        'shelter.pets.manage',
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pets/list' => [
        'shelter.pets.manage',
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pets/add' => [
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pets/edit-pet' => [
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pets/edit-record' => [
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pets/add-identification' => [
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pets/departure' => [
        'shelter.pets.manage.W',
    ],
    'v2/shelter/pet-owners/list' => [
        'shelter.owners.manage',
        'shelter.owners.manage.W',
    ],
    'v2/shelter/pet-owners/get' => [
        'shelter.owners.manage',
        'shelter.owners.manage.W',
    ],
    'v2/shelter/pet-owners/create' => [
        'shelter.owners.manage.W',
    ],
    'v2/shelter/pet-owners/link' => [
        'shelter.pets.manage.W',
        'shelter.owners.manage.W',
    ],

    // справочник вакцин
    'v2/vaccines/vaccines/list' => [
        'data.classificators.vaccines',
        'data.classificators.vaccines.W'
    ],
    'v2/vaccines/vaccines/get' => [
        'data.classificators.vaccines',
        'data.classificators.vaccines.W'
    ],
    'v2/pets/services/list' => [
        'activity.visits.manage',
        'ambulance.visits.manage',
    ],

    // Запрос изменений
    'v2/change-request/change-request/create' => [
        'data.change_requests.create',
    ],
    'v2/pets/passport/print' => [
        'data.pets.manage',
    ],

    // калькулятор дозировки препаратов
    'v2/tmc/dosages/get' => [
        'data.dosages.manage'
    ],
    'v2/tmc/dosages/list' => [
        'data.dosages.manage'
    ],
    'v2/tmc/dosages/calculate' => [
        'data.dosages.calc'
    ],
    'v2/tmc/dosages/create' => [
        'data.dosages.manage.W'
    ],
    'v2/tmc/dosages/edit' => [
        'data.dosages.manage.W'
    ],

    // конец калькулятора
    // уведомления
    'v2/gosvetnadzor/violation/notify' => [
        'gosvetnadzor.violation.manage.W'
    ],
    'v2/quarantine/quarantines/notify' => [
        'quarantine.manage.quarantine.W'
    ],
    'v2/visit/visit/notify' => [
        'activity.visits.edit.new',
    ],
    // 'v2/reports/notification/check' => [
    //     'activity.visits.edit.new',
    // ],
    'v2/reports/notification/send' => [
        'activity.visits.confirm-payment', // (VisitSpecialistRule)
        'activity.visits.manage.confirm-payment', // (UserOrgRule)
    ],

    // Сервис "Поиск животных"
    'v2/found/moderation/list-ads' => [
        'found_pet.manage.W'
    ],
    'v2/found/moderation/get-ad' => [
        'found_pet.manage.W'
    ],
    'v2/found/moderation/get-ad-photos' => [
        'found_pet.manage.W'
    ],
    'v2/found/moderation/approve' => [
        'found_pet.manage.W'
    ],
    'v2/found/moderation/close' => [
        'found_pet.manage.W'
    ],

    // Справочник ТМЦ оборудование
    'v2/tmc/equipments/get' => [
        'data.classificators.equipments',
        'data.classificators.equipments.W',
    ],
    'v2/tmc/equipments/list' => [
        'data.classificators.equipments',
        'data.classificators.equipments.W',
    ],
    'v2/tmc/equipments/create' => [
        'data.classificators.equipments.W',
    ],
    'v2/tmc/equipments/edit' => [
        'data.classificators.equipments.W',
    ],
    'v2/tmc/equipments/delete' => [
        'data.classificators.equipments.W',
    ],

    // Справочник ТМЦ расходные материалы
    'v2/tmc/exp-materials/get' => [
        'data.classificators.exp_materials',
        'data.classificators.exp_materials.W',
    ],
    'v2/tmc/exp-materials/list' => [
        'data.classificators.exp_materials',
        'data.classificators.exp_materials.W',
    ],
    'v2/tmc/exp-materials/create' => [
        'data.classificators.exp_materials.W',
    ],
    'v2/tmc/exp-materials/edit' => [
        'data.classificators.exp_materials.W',
    ],
    'v2/tmc/exp-materials/delete' => [
        'data.classificators.exp_materials.W',
    ],

    // Справочник ТМЦ препараты
    'v2/tmc/drugs/get' => [
        'data.classificators.drugs',
        'data.classificators.drugs.W',
    ],
    'v2/tmc/drugs/list' => [
        'data.classificators.drugs',
        'data.classificators.drugs.W',
    ],
    'v2/tmc/drugs/create' => [
        'data.classificators.drugs.W',
    ],
    'v2/tmc/drugs/edit' => [
        'data.classificators.drugs.W',
    ],
    'v2/tmc/drugs/delete' => [
        'data.classificators.drugs.W',
    ],

    // Справочник ТМЦ препараты
    'v2/tmc/vaccines/get' => [
        'data.classificators.vaccines',
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/vaccines/list' => [
        'data.classificators.vaccines',
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/vaccines/create' => [
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/vaccines/edit' => [
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/vaccines/delete' => [
        'data.classificators.vaccines.W',
    ],

    // Справочник категория ТМЦ
    'v2/tmc/categories/get' => [
        'data.classificators.categories',
        'data.classificators.categories.W',
    ],
    'v2/tmc/categories/list' => [
        'data.classificators.categories',
        'data.classificators.categories.W',
    ],
    'v2/tmc/categories/create' => [
        'data.classificators.categories.W',
    ],
    'v2/tmc/categories/edit' => [
        'data.classificators.categories.W',
    ],
    'v2/tmc/categories/delete' => [
        'data.classificators.categories.W',
    ],

    // Формы выпуска (ТМЦ)
    'v2/tmc/production-forms/get' => [
        'data.classificators.drugs',
        'data.classificators.vaccines',

        'data.classificators.drugs.W',
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/production-forms/list' => [
        'data.classificators.drugs',
        'data.classificators.vaccines',

        'data.classificators.drugs.W',
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/production-forms/create' => [
        'data.classificators.drugs.W',
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/production-forms/edit' => [
        'data.classificators.drugs.W',
        'data.classificators.vaccines.W',
    ],
    'v2/tmc/production-forms/delete' => [
        'data.classificators.drugs.W',
        'data.classificators.vaccines.W',
    ],

    // Категории для услуг
    'v2/services/categories/get' => [
        'data.pricelist.categories',
        'data.pricelist.categories.W',
    ],
    'v2/services/categories/save' => [
        'data.pricelist.categories.W',
    ],

    // Постановка на баланс
    'v2/tmc/balance-equipments/add' => [
        'tmc.balance.add'
    ],
    'v2/tmc/balance-drugs/add' => [
        'tmc.balance.add'
    ],
    'v2/tmc/balance-exp-materials/add' => [
        'tmc.balance.add'
    ],
    'v2/tmc/balance-vaccines/add' => [
        'tmc.balance.add'
    ],

    // Просмотр баланса
    'v2/tmc/balance-equipments/list' => [
        'tmc.balance.list'
    ],
    'v2/tmc/balance-drugs/list' => [
        'tmc.balance.list'
    ],
    'v2/tmc/balance-exp-materials/list' => [
        'tmc.balance.list'
    ],
    'v2/tmc/balance-vaccines/list' => [
        'tmc.balance.list'
    ],

    // Прививочные пункты
    'v2/vaccination-station/vaccination-station/get' => [
        'data.vaccinationStation.R',
    ],
    'v2/vaccination-station/vaccination-station/list' => [
        'data.vaccinationStation.R',
    ],
    'v2/vaccination-station/vaccination-station/create' => [
        'data.vaccinationStation.W',
    ],
    'v2/vaccination-station/vaccination-station/edit' => [
        'data.vaccinationStation.W',
    ],
    'v2/vaccination-station/vaccination-station/delete' => [
        'data.vaccinationStation.W',
    ],

    // Упрощенные вакцинации - ПП
    'v2/vaccination-journal/station/get' => [
        'activity.vaccinationJournal.R',
    ],
    'v2/vaccination-journal/station/list' => [
        'activity.vaccinationJournal.R',
    ],
    'v2/vaccination-journal/station/create' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],
    'v2/vaccination-journal/station/edit' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],
    'v2/vaccination-journal/station/delete' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],

    // Упрощенные вакцинации - Обходы
    'v2/vaccination-journal/flat/get' => [
        'activity.vaccinationJournal.R',
    ],
    'v2/vaccination-journal/flat/list' => [
        'activity.vaccinationJournal.R',
    ],
    'v2/vaccination-journal/flat/create' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],
    'v2/vaccination-journal/flat/edit' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],
    'v2/vaccination-journal/flat/delete' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],

    // Упрощенные вакцинации - Приюты
    'v2/vaccination-journal/shelter/get' => [
        'activity.vaccinationJournal.R',
    ],
    'v2/vaccination-journal/shelter/list' => [
        'activity.vaccinationJournal.R',
    ],
    'v2/vaccination-journal/shelter/create' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],
    'v2/vaccination-journal/shelter/edit' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],
    'v2/vaccination-journal/shelter/delete' => [
        'activity.vaccinationJournal.R',
        'activity.vaccinationJournal.W',
    ],

    // Действия с балансами (прием, передача, подтверждения передач, списание)
    '/v2/tmc/balance-actions/list' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    '/v2/tmc/balance-actions/write-off' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    '/v2/tmc/balance-actions/create-transfer-request' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    '/v2/tmc/balance-actions/create-transfer-to-balance' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    '/v2/tmc/balance-actions/transfer-to-requester' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    '/v2/tmc/balance-actions/confirm-transfer' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    '/v2/tmc/balance-actions/cancel-transfer' => [
        'managementGos',
        'vetSpecGos',
        'sysAdminGos',
        'technicMto',
    ],
    'v2/user/user/roles' => [
        'sysAdminGos',
    ]
];
