<?php

namespace app\modules\v2\modules\petOwners\controllers;

use app\models\db\Breeds;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\FiasAddresses;
use app\models\db\PetIdentification;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\RegCertificates;
use app\models\db\Species;
use app\modules\v2\modules\petOwners\models\PetOwnersModel;
use app\modules\v2\modules\pets\models\VaccinationModel;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Shared\Html;
use app\components\TemplateProcessor;
use PhpOffice\PhpWord\Element\Table;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;
use app\models\db\PetOwners;
use app\modules\v1\models\FileResource;
use Exception;
use Yii;
use yii\httpclient\Client;
use yii\web\UploadedFile;

/**
 * Class PetOwnerController
 * @package app\modules\v2\modules\petOwners\controllers
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769520
 */
class PetOwnerController extends BaseController
{

    /**
     * Создание владельца/представителя
     *
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $jur_name
     * @param string $inn
     * @param string $ogrn
     * @param string $birthday
     * @param string $snils
     * @param string $passport
     * @param bool $is_legal
     * @param int $id_area
     * @param int $id_district
     * @param string $fias_address
     * @param string $fact_fias_address
     * @param bool $entrepreneur
     * @param bool $force
     * @param string $description
     * @param string $passport_number
     * @param string $passport_series
     * @param string $passport_issue_date
     * @param string $passport_issuer
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate(
        $f_fio,
        $i_fio,
        $o_fio = null,
        $jur_name = null,
        $inn = null,
        $ogrn = null,
        $birthday = null,
        $snils = null,
        $is_legal = false,
        $id_area = null,
        $id_district = null,
        $fias_address = null,
        $fact_fias_address = null,
        $entrepreneur = false,
        $force = true,
        $description = null,
        $addresses_is_equal = false,
        $passport_number = null,
        $passport_series = null,
        $passport_issue_date = null,
        $passport_issuer = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $model = new PetOwnersModel();

        if ($force !== true) {
            $suggestions = $model->suggestDuplicates(
                $f_fio,
                $i_fio,
                $o_fio,
                $jur_name,
                $inn,
                $ogrn,
                $snils,
                $is_legal,
                $entrepreneur,
                null,
                true,
                $birthday,
                $addresses_is_equal,
                $fias_address,
                $fact_fias_address,
//                $passport,
            );
            if (!empty($suggestions)) {
                $model->addPetsSummary($suggestions);

                return [
                    'result' => true,
                    'suggestions' => $suggestions,
                ];
            }
        }

        $pet_owner = $model
            ->create(
                $f_fio,
                $i_fio,
                $o_fio,
                $jur_name,
                $inn,
                $ogrn,
                $birthday,
                $snils,
                $is_legal,
                $id_area,
                $id_district,
                $fias_address,
                $fact_fias_address,
                $entrepreneur,
                $description,
                $addresses_is_equal,
                $passport_number,
                $passport_series,
                $passport_issue_date,
                $passport_issuer
            );

        return [
            'result' => true,
            'id' => $pet_owner->id,
        ];
    }

    /**
     * Удаление владельца
     *
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new PetOwnersModel())->delete($id);

        return [
            'result' => true,
        ];
    }

    /**
     * Редактирование владельца/представителя
     *
     * @param int $id
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $jur_name
     * @param string $inn
     * @param string $ogrn
     * @param string $birthday
     * @param string $snils
     * @param string $passport
     * @param bool $is_legal
     * @param int $id_area
     * @param int $id_district
     * @param string $fias_address
     * @param string $fact_fias_address
     * @param bool $entrepreneur
     * @param bool $force (пока оставлено для обратной совместимости с фронтом)
     * @param string $description
     * @param string $passport_number
     * @param string $passport_series
     * @param string $passport_issue_date
     * @param string $passport_issuer
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(
        $id,
        $f_fio,
        $i_fio,
        $o_fio = null,
        $jur_name = null,
        $inn = null,
        $ogrn = null,
        $birthday = null,
        $snils = null,
        $is_legal = false,
        $id_area = null,
        $id_district = null,
        $fias_address = null,
        $fact_fias_address = null,
        $entrepreneur = false,
        $force = null,
        $description = null,
        $addresses_is_equal = false,
        $passport_number = null,
        $passport_series = null,
        $passport_issue_date = null,
        $passport_issuer = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetOwnersModel();

        $model->edit(
            $id,
            $f_fio,
            $i_fio,
            $o_fio,
            $jur_name,
            $inn,
            $ogrn,
            $birthday,
            $snils,
            $is_legal,
            $id_area,
            $id_district,
            $fias_address,
            $fact_fias_address,
            $entrepreneur,
            $description,
            $addresses_is_equal,
            $passport_number,
            $passport_series,
            $passport_issue_date,
            $passport_issuer
        );

        return [
            'result' => true,
        ];
    }

    /**
     * Возвращает владельца по id
     *
     * @param int $id
     * @param bool $with_duplicates Показывать привязанные дубли владельцев
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id, $with_duplicates = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetOwnersModel())->getPetOwner($id, $with_duplicates, true, true, false),
        ];
    }

    /**
     * Поиск владельца животного
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    // public function actionList($page = 1, $limit = 10, $filter = null)
    // {
    //     $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

    //     return [
    //         'result' => (new PetOwnersModel())->listPetOwners($page, $limit, $filter),
    //     ];
    // }

    public function actionList($page = 1, $limit = 10, $filter = null, $isAsc = "flag", $typeQwery = "NULLIF(pet_owners.fullname, '')")
    {
        if ($limit > 100) {
            $limit = 100;
        }

        if ($isAsc == "flag") {
            $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

            return [
                'result' => (new PetOwnersModel())->listPetOwners($page, $limit, $filter),
            ];
        }
        if ($isAsc != "flag") {


            return [
                'result' => (new PetOwnersModel())->listPetOwners1($page, $limit, $filter, $isAsc, $typeQwery),
            ];
        }

    }

    /**
     * Поиск владельца животного cj cgbcjrv tuj ;bdjnys[]
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */

    public function actionListWithPets($page = 1, $limit = 10, $filter = null, $typeQwery = "NULLIF(pet_owners.fullname, '')")
    {
        if ($limit > 100) {
            $limit = 100;
        }

        return [
            'result' => (new PetOwnersModel())->listPetOwnersWithPets($page, $limit, $filter, $typeQwery),
        ];


    }

    function createXmlData($surname, $firstname, $patronymic, $birthday, $snils, $benefitcategory)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <ns2:getPrivilegeRequest xmlns:ns2="http://erl.msr.com/schemas/privilege/oiv/v1">
                            <sender>23</sender>
                            <citizenRequest>
                                <requestId>941c55ee-8d86-49b6-b554-11ecc70e8d81</requestId>
                                <citizen>
                                    <name>
                                        <surname>' . $surname . '</surname>
                                        <firstname>' . $firstname . '</firstname>
                                        <patronymic>' . $patronymic . '</patronymic>
                                    </name>
                                    <birthday>' . $birthday . '</birthday>
                                    <snils>' . $snils . '</snils>
                                </citizen>
                                <benefitcategory_pk>' . $benefitcategory . '</benefitcategory_pk>
                            </citizenRequest>
                        </ns2:getPrivilegeRequest>
                    </soapenv:Body>
                </soapenv:Envelope>';
    }

    function createInfoPrevileges($xmlString)
    {
        preg_match('/<benefitcategory_pk>(.*?)<\/benefitcategory_pk>/', $xmlString, $matches);
        $benefitcategory_pk = "";
        if ($matches) {
            $benefitcategory_pk = $matches[1];
        }
        preg_match('/<citizen_pk>(.*?)<\/citizen_pk>/', $xmlString, $matches);
        $citizenPk = "";
        if ($matches) {
            $citizenPk = $matches[1];
        }
        preg_match('/<begin_date>(.*?)<\/begin_date>/', $xmlString, $matches);
        $begin_date = "";
        if ($matches) {
            $begin_date = $matches[1];
        }
        preg_match('/<$end_date>(.*?)<\/$end_date>/', $xmlString, $matches);
        $end_date = "";
        if ($matches) {
            $end_date = $matches[1];
        }
        $arr = [];
        $arr['benefitcategory_pk'] = $benefitcategory_pk;
        $arr['citizenPk'] = $citizenPk;
        $arr['begin_date'] = $begin_date;
        $arr['$end_date'] = $end_date;
        return $arr;
    }

    public function actionPrivilegesUpdate($id_user)
    {
        try {
            $pet_owner = '';
            $pet_owner = PetOwners::findOne(['id' => $id_user]);
            $surname = $pet_owner["f_fio"];
            $firstname = $pet_owner["i_fio"];
            $patronymic = $pet_owner["o_fio"];
            $birthday = $pet_owner["birthday"];
            $snils = $pet_owner["snils"];

//            логирование в папке PetOwners/controllers
            $file = '../modules/v2/modules/petOwners/controllers/PrivilegesUpdateResult.txt';
            $data = 'check text';
            file_put_contents($file, $data);

//            Объявление переменных для сохранения/бновления в базу данных льгот владельца животного
            $is_veteran_infosoc = false;
            $is_disabled_infosoc = false;
            $is_family_disabled_children_infosoc = false;
            $is_blind_infosoc = false;

            ///////////////////////////////////////////////////////////////
            /// Запросы в ИС Социум
            ///////////////////////////////////////////////////////////////


            ///////////////////////////////////////////////////////////////
            /// Коды для проверки
            /// 1020 - Ветеран ВОВ;
            /// 1012 - Инвалиды первой группы;
            /// 1018 - Семьи, воспитывающие детей-инвалидов в возрасте до 23 лет;
            /// 1011 - Инвалиды по зрению, имеющие собак-проводников.
            ///////////////////////////////////////////////////////////////

//             Запрос на проверку льготы Ветеран ВОВ
            $client_is_veteran_infosoc = new Client();
            $xmlData = $this->createXmlData($surname, $firstname, $patronymic, $birthday, $snils, "1020");
//              Создаем файл логирования
            file_put_contents($file, $xmlData);
//              Заголовок устанавливаем один раз для всех запросов
            $headers = [
                'Content-Type' => 'text/xml; charset=utf-8',
            ];
//              Формируем запрос
            $response_is_veteran_infosoc = $client_is_veteran_infosoc->createRequest()
                ->setUrl($_ENV['SOCIUM_URL'])
                ->setMethod('POST')
                ->setHeaders($headers)
                ->setContent($xmlData)
                ->send();
//              Записываем в файл логов результат запроса
            if ($response_is_veteran_infosoc->isOk) {
                $responseData = $response_is_veteran_infosoc->getContent();
                file_put_contents($file, $responseData);
            } else {
                file_put_contents($file, "error response_is_veteran_infosoc");
            }

//               Проверяем результат запроса на наличие льготы Ветеран ВОВ
            $xmlString_is_veteran_infosoc = $response_is_veteran_infosoc->getContent();
            $result_info_is_veteran_infosoc = $this->createInfoPrevileges($xmlString_is_veteran_infosoc);
//               В случае наличия  льготы Ветеран ВОВ обновляем переменную для сохранения в базе
            if ($result_info_is_veteran_infosoc['benefitcategory_pk'] != "") {
                $is_veteran_infosoc = true;
            }

//               Все последующие запросы выполняются аналогично

//               Запрос на проверку льготы инвалида
            $client_is_disabled_infosoc = new Client();
            $xmlData_is_disabled_infosoc = $this->createXmlData($surname, $firstname, $patronymic, $birthday, $snils, "1012");
            $response_is_disabled_infosoc = $client_is_disabled_infosoc->createRequest()
                ->setUrl($_ENV['SOCIUM_URL'])
                ->setMethod('POST')
                ->setHeaders($headers)
                ->setContent($xmlData_is_disabled_infosoc)
                ->send();

//               Дозаписываем файл логирования
            if ($response_is_disabled_infosoc->isOk) {
                $responseData_is_disabled_infosoc = $response_is_disabled_infosoc->getContent();
                file_put_contents($file, "is_disabled_infosoc", FILE_APPEND);
                file_put_contents($file, $responseData_is_disabled_infosoc, FILE_APPEND);
            } else {
                file_put_contents($file, "error response_is_disabled_infosoc");
            }

//            Проверяем результат запроса на наличие льготы инвалида
            $xmlString_is_disabled_infosoc = $response_is_disabled_infosoc->getContent();
            $result_info_is_disabled_infosoc = $this->createInfoPrevileges($xmlString_is_disabled_infosoc);
//              В случае наличия  льготы инвалида обновляем переменную для сохранения в базе
            if ($result_info_is_disabled_infosoc['benefitcategory_pk'] != "") {
                $is_disabled_infosoc = true;
            }
//                Запрос на проверку льготы для семей, воспитывающих детей-инвалидов в возрасте до 23 лет
            $client_family_disabled_children_infosoc = new Client();
            $xmlData_family_disabled_children_infosoc = $this->createXmlData($surname, $firstname, $patronymic, $birthday, $snils, "1018");
            $response_family_disabled_children_infosoc = $client_family_disabled_children_infosoc->createRequest()
                ->setUrl($_ENV['SOCIUM_URL'])
                ->setMethod('POST')
                ->setHeaders($headers)
                ->setContent($xmlData_family_disabled_children_infosoc)
                ->send();

//               Дозаписываем файл логирования
            if ($response_family_disabled_children_infosoc->isOk) {
                $responseData_family_disabled_children_infosoc = $response_family_disabled_children_infosoc->getContent();
                file_put_contents($file, "is_disabled_infosoc", FILE_APPEND);
                file_put_contents($file, $responseData_family_disabled_children_infosoc, FILE_APPEND);
            } else {
                file_put_contents($file, "error response_family_disabled_children_infosoc");
            }

//            Проверяем результат запроса на наличие льготы для семей
            $xmlString_family_disabled_children_infosoc = $response_family_disabled_children_infosoc->getContent();
            $result_info_family_disabled_children_infosoc = $this->createInfoPrevileges($xmlString_family_disabled_children_infosoc);
//              В случае наличия  льготы для семей обновляем переменную для сохранения в базе
            if ($result_info_family_disabled_children_infosoc['benefitcategory_pk'] != "") {
                $is_family_disabled_children_infosoc = true;
            }

//                Запрос на проверку льготы для инвалидов по зрению, имеющих собак-проводников
            $client_is_blind_infosoc = new Client();
            $xmlData_is_blind_infosoc = $this->createXmlData($surname, $firstname, $patronymic, $birthday, $snils, "1010");
            $response_is_blind_infosoc = $client_is_blind_infosoc->createRequest()
                ->setUrl($_ENV['SOCIUM_URL']) 
                ->setMethod('POST')
                ->setHeaders($headers)
                ->setContent($xmlData_is_blind_infosoc)
                ->send();

//               Дозаписываем файл логирования
            if ($response_is_blind_infosoc->isOk) {
                $responseData_is_blind_infosoc = $response_is_blind_infosoc->getContent();
                file_put_contents($file, "is_disabled_infosoc", FILE_APPEND);
                file_put_contents($file, $responseData_is_blind_infosoc, FILE_APPEND);
            } else {
                file_put_contents($file, "error response_is_blind_infosoc");
            }

//            Проверяем результат запроса на наличие льготы для семей
            $xmlString_is_blind_infosoc = $response_is_blind_infosoc->getContent();
            $result_info_is_blind_infosoc = $this->createInfoPrevileges($xmlString_is_blind_infosoc);
//            В случае наличия  льготы для семей обновляем переменную для сохранения в базе
            if ($result_info_is_blind_infosoc['benefitcategory_pk'] != "") {
                $is_blind_infosoc = true;
            }

            $pet_owner->is_veteran_infosoc = $is_veteran_infosoc;
            $pet_owner->is_disabled_infosoc = $is_disabled_infosoc;
            $pet_owner->is_family_disabled_children_infosoc = $is_family_disabled_children_infosoc;
            $pet_owner->is_blind_infosoc = $is_blind_infosoc;
            $pet_owner->updated_at = date('Y-m-d H:i:s');

            $pet_owner->update();
            $result_update = [];
            $result_update['is_veteran_infosoc'] = $pet_owner->is_veteran_infosoc;
            $result_update['is_disabled_infosoc'] = $pet_owner->is_disabled_infosoc;
            $result_update['is_family_disabled_children_infosoc'] = $pet_owner->is_family_disabled_children_infosoc;
            $result_update['is_blind_infosoc'] = $pet_owner->is_blind_infosoc;
            $result_update['updated_at'] = $pet_owner->updated_at;

            $result_update['$result_info_is_veteran_infosoc'] = $result_info_is_veteran_infosoc;
            $result_update['$response_is_disabled_infosoc'] = $result_info_is_disabled_infosoc;
            $result_update['$result_info_family_disabled_children_infosoc'] = $result_info_family_disabled_children_infosoc;
            $result_update['$result_info_is_blind_infosoc'] = $result_info_is_blind_infosoc;
            $result_update['$fullname'] = $pet_owner["fullname"];
            $result_update['pet_owner'] = $pet_owner;

            return [
                'response' => $result_update,
            ];

        } catch (\Throwable $e) {

            throw new ServerErrorHttpException('Ошибка получения сообщенияй для организации');
        }
    }

    function actionDevChangePrivileges($id_user, $is_veteran_infosoc, $is_disabled_infosoc, $is_family_disabled_children_infosoc, $is_blind_infosoc)
    {
        try {
            $pet_owner = PetOwners::findOne(['id' => $id_user]);
            $pet_owner->is_veteran_infosoc = $is_veteran_infosoc;
            $pet_owner->is_disabled_infosoc = $is_disabled_infosoc;
            $pet_owner->is_family_disabled_children_infosoc = $is_family_disabled_children_infosoc;
            $pet_owner->is_blind_infosoc = $is_blind_infosoc;
            $pet_owner->update();
            return [
                'response' => true,
            ];
        } catch (\Throwable $e) {

            throw new ServerErrorHttpException('Ошибка обновления льгот');
        }
    }

    public function actionDownloadDoc($idOwner)
    {
        $owner = PetOwners::findOne(['id' => $idOwner]);
        $address = FiasAddresses::find()->where(['id' => $owner->id_fact_fias_address])->one();
        $contacts = Contacts::find()->where(['entity_type' => Contacts::ENTITY_TYPE_PET_OWNER])->andWhere(['entity_id' => $owner->id])->all();

        $mainPhone = Contacts::find()
            ->where(['entity_type' => Contacts::ENTITY_TYPE_PET_OWNER])
            ->andWhere(['entity_id' => $owner->id])
            ->andWhere(['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_PHONE])])
            ->orderBy([
                'main_flag' => SORT_DESC,
                'created_at' => SORT_DESC
            ])
            ->limit(1)
            ->one();

        $otherPhones = Contacts::find()
            ->where(['entity_type' => Contacts::ENTITY_TYPE_PET_OWNER])
            ->andWhere(['entity_id' => $owner->id])
            ->andWhere(['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_PHONE])])
            ->all();

        $contact = '';
        if (!empty($mainPhone)) {
            $contact = $mainPhone->name;
        }else{
            foreach (array_reverse($otherPhones) as $otherPhone) {
                if ($otherPhone->name != $mainPhone->name) {
                    $contact = $otherPhone->name;
                }
            }
        }

        if (empty($mainPhone) && empty($otherPhones)) {
            $contact .= '-';
        }

        $emails = Contacts::find()
            ->where(['entity_type' => Contacts::ENTITY_TYPE_PET_OWNER])
            ->andWhere(['entity_id' => $owner->id])
            ->andWhere(['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_EMAIL])])
            ->all();

        $contact .= ', электронная почта ';
        foreach ($emails as $num => $email) {
            if ($num > 0) {
                $contact .= ', ';
            }

            $contact .=  $email->name;
        }

        if (empty($emails)) {
            $contact .= '-';
        }

        $ownerToPets = PetsToOwner::find()->where(['id_owner' => $owner->id])->all();

        $petInfo = '';
        foreach ($ownerToPets as $ownerToPet) {
            $pet = Pets::find()->where(['id' => $ownerToPet->id_pet])->one();
            $specie = Species::find()->where(['id' => $pet->id_species])->one();
            $breed = Breeds::find()->where(['id' => $pet->id_breed])->one();
            $ident = PetIdentification::find()->where(['id_pet' => $ownerToPet->id_pet])->one();
            $regCert = RegCertificates::find()->where(['id_pet' => $ownerToPet->id_pet])->one();

            $rab = PetRabiesVaccination::find()->where(['id_pet' => $ownerToPet->id_pet])
                ->orderBy(['date' => SORT_DESC])
                ->one();

            $rabDate = 'Отсутствует';
            if (!empty($rab)) {
                $rabDate = date('Y-m-d', strtotime($rab->date));
            }

            $identText = 'не имеет';
            if (!empty($ident)) {
                $identText = $ident->identification_code;
            }

            $regCertText = 'не имеет';
            if (!empty($regCert)) {
                $regCertText = $regCert->number;
            }

            $petAddress = FiasAddresses::find()->where(['id' => $pet->id_fias_address])->one();

            $diff = date_diff(date_create($pet->birthday), date_create(date('Y-m-d')));

            $fontFamily = 'font-family: Trebuchet MS';
            $petInfo .= '<p style="font-size: 14px;' . $fontFamily . '">Сведения о животном:</p>';
            $petInfo .= '<p style="font-size: 11px">';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Вид животного:</span> '  . $specie->name. ',   ';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Кличка животного:</span> ' . $pet->name. ',   ';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Порода:</span> ' . $breed->name. ', ';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Пол:</span> ' . $pet->getSexName(). ', ';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Возраст</span> ' . $diff->format('%y') . ' лет ' . $diff->format('%m') .' мес';
            $petInfo .= '</p>';
            $petInfo .= '<p style="font-size: 11px">';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Идентификация:</span> ' . $identText. ', ';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Номер регистрационного удостоверения:</span> ' . $regCertText . ' ';
            $petInfo .= '</p>';
            $petInfo .= '<p style="font-size: 11px">';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Дата вакцинации против бешенства:</span> ' . $rabDate;
            $petInfo .= '</p>';
            $petInfo .= '<p style="font-size: 11px">';
            $petInfo .= '<span style="font-weight: bold;' . $fontFamily . '">Адрес содержания животного:</span> ' . $petAddress->full_address. ' ';
            $petInfo .= '</p><br/>';
        }

        $template_path = realpath(__DIR__) . '/../templates/owner-cart.docx';
        $templateWord = new TemplateProcessor($template_path);

        $templateWord->setValue("fio", $owner->f_fio . ' ' . $owner->i_fio . ' ' . $owner->o_fio);
        $templateWord->setValue("address", $address->full_address);
        $templateWord->setValue("contact", $contact);

        $section = new Section(1);
        Html::addHtml($section, $petInfo, false, false);

        $templateWord->setComplexBlock("petInfo", $section);

        $tmpFileName = $templateWord->save();
        $response = \Yii::$app->getResponse();
        return $response->sendFile(
            $tmpFileName, "owner-cart.docx",
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }

    public function actionAttachAgreement($owner_id) {
        if (FileResource::find()->where(['entity_id' => $owner_id])->andWhere(['entity_type' => 'owner_agreement'])->count()) throw new Exception('Согласие на обработку персональных данных уже загружено!', 400);
        $fileService = Yii::$app->fileService;
        $resource = $fileService->upload(UploadedFile::getInstanceByName('file'));
        $resource->entity_id = $owner_id;
        $resource->entity_type = 'owner_agreement';
        $resource->save();
        return $resource;
    }
    public function actionDeleteAgreement($owner_id) {
        $resources = FileResource::find()->where(['entity_id' => $owner_id])->andWhere(['entity_type' => 'owner_agreement'])->all();
        if (count($resources)>0) {
            foreach ($resources as $resource) {
                $resource->delete();
            }
        }
    }

}
