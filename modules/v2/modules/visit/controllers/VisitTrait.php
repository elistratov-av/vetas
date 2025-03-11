<?php

namespace app\modules\v2\modules\visit\controllers;

use app\models\db\ShiftType;
use app\models\db\VisitPets;
use yii\db\Expression;
use yii\db\Query;
use yii\web\NotFoundHttpException;

use app\models\db\PetOwners;
use app\models\db\Specialists;
use app\models\db\Violation;
use app\models\db\ViolationType;
use app\models\db\Visits;
use app\common\models\VisitStatus;
use app\models\db\FiasAddresses;

/**
 * Trait VisitTrait
 * @package app\modules\v2\modules\visit\controllers
 */
trait VisitTrait
{
    /**
     * @param int  $id
     * @param bool $fullResponse
     * @param bool $withInformStatus
     * @return \app\models\db\Visits|array
     * @throws \yii\web\NotFoundHttpException
     */
    protected function findVisit($id, $fullResponse = false, $withInformStatus = false)
    {
        if ($fullResponse === true) {
            $visit = Visits::findOne(['id' => $id]); //поиск визита, чтобы вычленить нужное согласие на обработку перс. данных
            $query = Visits::find()
                ->alias('v')
                ->select([
                    '*',
                ])
                ->addSelect(new Expression("CASE WHEN (v.guid_video IS NOT NULL) THEN concat('{$_ENV['VKS_SPECIALIST_URL']}/', v.guid_video) ELSE null END AS vks_link"))
                ->with(['owner' => function ($query) {
                    /** @var ActiveQuery $query */
                    $query
                        ->alias('o')
                        ->select([
                            'o.*',
                            // Подменяем имя Владельца для данных неавторизованного пользователя mosru
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.f_fio ELSE o.f_fio END AS "f_fio"'),
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.i_fio ELSE o.i_fio END AS "i_fio"'),
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.o_fio ELSE o.o_fio END AS "o_fio"'),
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.fullname ELSE o.fullname END AS "fullname"'),
                        ])
                        ->joinWith(['tmpOwner' => function ($query) {
                            /** @var ActiveQuery $query */
                            $query->alias('tmpo');
                        }])
                    ;
                }])
                ->with('owner.fias_addresses')
                ->with('owner.phoneMainContact')
                ->with('owner.emailMainContact')
                ->with('owner.agreement_pers')
                ->with('organization')
                ->with('files')
                ->with('mosruFiles')
                ->with(['pets' => function ($petsQuery) use ($id) {
                    /** @var $petsQuery \yii\db\ActiveQuery */
                    $petsQuery
                        ->alias('p')
                        ->select([
                            'p.*',
                            // Подменяем кличку Питомца для данных неавторизованного пользователя mosru
                            new Expression('CASE WHEN (tmpp.id IS NOT NULL) THEN tmpp.name ELSE p.name END AS "name"'),
                        ])
                        ->joinWith(['tmpPet' => function ($query) {
                            /** @var ActiveQuery $query */
                            $query->alias('tmpp');
                        }])
                        ->with('breeds')
                        ->with('species')
                        ->with(['pet_identification' => function ($q) {
                            /* @var $q \yii\db\ActiveQuery */
                            $q->with('ident_type');
                        }])
                        ->with('reg_certificate')
                        ->with('reg_certificate.contact_mail')
                        ->with('reg_certificate.contact_phone')
                        ->with('reg_certificate.file')
                        ->with('fias_address')
                        ->with('brood')
                        ->with(['agreement_surg' => function ($q) use ($id) {
                            /* @var $q \yii\db\ActiveQuery */
                            $q->innerJoin('visits', new Expression('visits.id = agreements.id_visit'));
                            $q->andWhere(['id_visit' => $id]);
                        }])
                        ->with([
                            'visits' => function ($q) {
                                /** @var \yii\db\ActiveQuery $q */
                                $q
                                    ->alias('pv') // pet visits
                                    ->select([
                                        'pv.id',
                                        'pv.start_dttm',
                                        'st.name as service_type_name',
                                    ])
                                    ->innerJoinWith('services s', false)
                                    // TODO:service-types-codes
                                    ->innerJoinWith('services.serviceType st', false)
                                    ->andWhere(['pv.status' => [
                                        VisitStatus::NEW,
                                        VisitStatus::CHANGED,
                                        VisitStatus::IN_WORK,
                                    ]])
                                    ->andWhere('start_dttm > now()')
                                    ->andWhere(['st.name' => ['Вакцинация', 'Чипирование']])
                                    ->orderBy('pv.start_dttm ASC')
                                ;
                            },
                            'violations' => function ($q) {
                                /** @var \yii\db\ActiveQuery $q */
                                $q
                                    ->alias('v')
                                    ->select([
                                        'v.id_pet', // Иначе yii не может наполнить модели
                                        'v.id_violation',
                                        'v.id_visit',
                                        'v.date_violation',
                                        'v.id_type',
                                        'v.rejection_reason',
                                    ])
                                    ->innerJoinWith('type t')
                                    ->where([
                                        'v.state' => Violation::ACTIVE_STATES,
                                        't.tech_name' => [
                                            ViolationType::V02_IDENT_REJECTION,
                                            ViolationType::V04_VACCINATION_REJECTION,
                                        ],
                                    ])
                                ;
                            }
                            /**
                             * Для каждого животного нужно отдать описание по текущему приёму.
                             * @see https://jira.altarix.ru/browse/VETAIS-3316
                             */,
                            'visits_descriptions' => function ($q) use ($id) {
                                /** @var \yii\db\ActiveQuery $q */
                                $q->where(['id_visit' => $id]);
                            },
                        ])
                    ;}
                ])
                ->with('services')
                ->with(['specialists' => function ($specialistQuery) {
                    /* @var $specialistQuery \yii\db\ActiveQuery */
                    $specialistQuery->joinWith('user', false)
                        ->select(array_merge(['specialists.*'], Specialists::personalAttributes()));
                }])
                ->with(['sign' => function ($signQuery) {
                    /* @var $signQuery \yii\db\ActiveQuery */
                    $signQuery->select(['id', 'created_at', 'cert_number', 'cert_owner', 'valid_from', 'valid_to']);
                }])
                ->where(['v.id' => $id])
            ;

            if ($withInformStatus) {
                $sub_query = (new Query())
                    ->select('id')
                    ->from('public.shift_type')
                    ->where([
                        'IN', 'type', ShiftType::NOTIFY_DISABLED
                    ])->column();

                $ids = implode(',', $sub_query);

                $query->addSelect(new Expression("
                    (
                        v.channel NOT IN ($ids) 
                    ) AS need_inform
                "));
            }
            $model = $query->asArray()->one();

            if ($model === null) {
                throw new NotFoundHttpException('Прием с указанным id не найден');
            }

            if (isset($model['need_inform']) && $model['need_inform']) {
                try {
                    $model['need_inform'] = PetOwners::findOne(['id' => $model['id_owner']])->hasSubscriptions();
                } catch (\Exception $e) {
                    $model['need_inform'] = false;
                }
            }

            /**
             * Для завершенных/отмененных приемов
             * заменяем адрес на момент завершения/отмены приема
             * @see https://jira.altarix.ru/browse/VETAIS-3008
             */
            if (($model['status'] == VisitStatus::CANCELED) || ($model['status'] == VisitStatus::FINISHED)){
                foreach ($model['pets'] as &$pet){
                    /** @var VisitPets $visit_pet */
                    $visit_pet = VisitPets::find()->where(['id_visit'=> $model['id'], 'id_pet' => $pet['id']])->one();
                    $pet['fias_address'] = $visit_pet->fias_address;
                }
                unset($pet);
            }

            // Отформатируем данные по животным
            foreach ($model['pets'] as &$pet) {
                /**
                 * Для каждого животного нужно отдать
                 * ближайший приём с идентификацией (чипированием)
                 * и ближайший приём с вакцинацией
                 * @see https://jira.altarix.ru/browse/VETAIS-3216
                 */
                $pet['next_vaccination_visit'] = null;
                $pet['next_identification_visit'] = null;
                foreach ($pet['visits'] as $visit) {
                    // TODO:service-types-codes
                    if (null === $pet['next_vaccination_visit'] && 'Вакцинация' === $visit['service_type_name']) {
                        $pet['next_vaccination_visit'] = $visit;
                    }

                    // TODO:service-types-codes
                    if (null === $pet['next_identification_visit'] && 'Чипирование' === $visit['service_type_name']) {
                        $pet['next_identification_visit'] = $visit;
                    }

                    if (null !== $pet['next_vaccination_visit'] && null !== $pet['next_identification_visit']) {
                        break;
                    }
                }

                unset ($pet['visits']);

                $descs = [];
                foreach($pet['visits_descriptions'] as $visitDesc) {
                    $needAdd = 1;
                    foreach ($descs as $descNum => $descItem) {
                        if ($descItem['id_description_type'] == $visitDesc['id_description_type']) {
                            $needAdd = 0;
                            break;
                        }
                    }

                    if ($needAdd == 1) {
                        $descs[] = $visitDesc;
                    }else{
                        if (is_array($descs[$descNum]['description'])) {
                            $descs[$descNum]['description'][] = $visitDesc['description'];
                        }else{
                            $descs[$descNum]['description'] = [$descs[$descNum]['description']];
                            $descs[$descNum]['description'][] = $visitDesc['description'];
                        }
                    }
                }

                // Переименуем коллекцию описаний визита для пущей семантичности
                $pet['visit_descriptions'] = $descs;
                unset ($pet['visits_descriptions']);
            }
        } else {
            $model = Visits::findOne(['id' => $id]);
        }

        if ($model === null) {
            throw new NotFoundHttpException('Прием с указанным id не найден');
        }

        foreach($model['files'] as $index => $file) {
            $model['files'][$index]['filesize'] = filesize(\Yii::getAlias('@webroot') . $file['path']);
        }

        if (array_key_exists('type', $model)) {
            if ($model["type"] == "AMBULANCE") {

            $name = '';
            $rows = (new \yii\db\Query())
            ->select(['id_specialist'])
            ->from('brigades_specialists')
            ->where(['id_brigade' =>  $model['id_brigade']])
            ->all();

            foreach ($rows as $row) {
                $specialist =  (new \yii\db\Query())
                ->select(['id_user'])
                ->from('specialists')
                ->where(['id' =>  $row['id_specialist']])
                ->all();

                $user =  (new \yii\db\Query())
                ->select(['fullname'])
                ->from('users')
                ->where(['id' =>  $specialist[0]['id_user']])
                ->all();
                $name = $name.$user[0]['fullname'].', ';
            }

            $model['brigade_specialists'] = substr($name,0,-2);

            $brigade_name = (new \yii\db\Query())
            ->select(['name'])
            ->from('brigades')
            ->where(['id' =>  $model['id_brigade']])
            ->all();
            // unset($visit['id']);
            // unset($visit['id_brigade']);
            unset($model['id_pet']);
            $model['brigade_name'] = $brigade_name[0]['name'];

            $owner_phone_numbers = '';
            $rows_owner_phone_numbers = (new \yii\db\Query())
            ->select(['name'])
            ->from('contacts')
            ->where([
                'id' =>  $model['id_owner'],
                'id_contact_type' =>  1,
                ])
            ->all();

            if(count($rows_owner_phone_numbers)>0){
                foreach ($rows_owner_phone_numbers[0] as $phone_number) {
                    $owner_phone_numbers = $phone_number.', ';
                }
                $model['owner_phone_numbers'] = substr($owner_phone_numbers ,0,-2);
            }else{
                $model['owner_phone_numbers'] = '';
            }

            // $array_address_visit = explode(", ", $model['visit_to_address']);

            //////
            // $visit_to_address_id = FiasAddresses::findOrCreateFiasAddress(["region" => $array_address_visit[0], "city" => $array_address_visit[0], "street" => $array_address_visit[1],  "house" => $array_address_visit[2]]);

            // $visit_to_address_coord = (new \yii\db\Query())
            // ->select(['*'])
            // ->from('fias_addresses')
            // ->where(['id' =>  $visit_to_address_id])
            // ->all();
            // $model['visit_to_address_coord'] = $visit_to_address_coord[0];


            ////

            $visit_to_address_coord = (new \yii\db\Query())
            ->select(['*'])
            ->from('fias_addresses')
            ->where(['full_address' =>  $model['visit_to_address']])
            ->andWhere('fias_addresses.lon is not null')
            ->andWhere('fias_addresses.lat is not null')
            ->all();
            if (count($visit_to_address_coord) == 0) {
                $visit_to_address_coord = null;
            }else{
                $visit_to_address_coord = $visit_to_address_coord[0];
            }
            $model['visit_to_address_coord'] = $visit_to_address_coord;
            ///////

            // unset($visit['id']);
            // unset($visit['id_brigade']);
            unset($model['id_pet']);
            $model['brigade_name'] = $brigade_name[0]['name'];

            return $model;
        }
    }
        return $model;
    }
}
