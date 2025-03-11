<?php


namespace app\modules\v2\modules\pets\models;

use app\common\components\FileService;
use app\common\models\VisitStatus;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\Files;
use app\models\db\Organizations;
use app\models\db\PetDehelmintization;
use app\models\db\PetIdentification;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwners;
use app\models\db\PetOwnerType;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Specialists;
use app\models\db\Species;
use app\models\db\Visits;
use app\modules\soap\models\Breeds;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\visit\skeletons\visit\Lists;
use function GuzzleHttp\Promise\all;
use Yii;
use yii\base\DynamicModel;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class PassportModel
{
    public $id_organization;
    public $file_type = 'passport';

    /**
     * DiscountModel constructor.
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function __construct()
    {
        $user = \Yii::$app->user->getIdentity();
        $this->id_organization = $user->specialist->id_organization;
        if ($this->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }


    /**
     * @param array $ids
     * @return false|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function createPdf(int $id_pet)
    {
        /* @var $generator \app\common\components\pdfGenerator\PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');

        $passport_data = $this->getPassportData($id_pet);

        if (empty($passport_data)) {
            return false;
        }

        $data = [
            'data' => $passport_data,
        ];

        try {
            [$dir, $filename, $ext] = $generator->createDocument('pet_passport', $data);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Ошибка при генерации файла паспорта животного');
        }

        /** @var FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $path = $dir . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
        $hash = $fileService->generateHash($filename);
        $old = FileResource::find()
            ->andWhere(['entity_id' => $id_pet, 'entity_type' => $this->file_type])
            ->orderBy(['created' => SORT_DESC])
            ->one();

        try {
            $old_path = $old ? $old->path : null;
            $old_path ? $fileService->delete($this->file_type, $id_pet, $old_path) : null;
            $fileResource = new FileResource();
            $fileResource->hash = $hash;
            $fileResource->path = '/upload/pdf/' . $filename . '.' . $ext;
            $fileResource->name = $filename . '.' . $ext;
            $fileResource->entity_id = $id_pet;
            $fileResource->entity_type = $this->file_type;
            $fileResource->save();
            $fileService->attach($fileResource);
        } catch (\Throwable $e) {
            $fileService->repository->delete($path);
            throw new ServerErrorHttpException('Ошибка сохранения файла паспорта животного: ' . $e->getMessage());
        }

        return $fileResource;
    }

    /**
     * @param int $id_pet
     * @return array
     * @throws NotFoundHttpException
     */
    private function getPassportData(int $id_pet)
    {
        $pet = Pets::findOne($id_pet);
        if (!$pet) {
            throw new NotFoundHttpException('Животное не найдено');
        }

        $petOwnerType = PetOwnerType::findOne(['is_owner' => true]);
        $pto = PetsToOwner::findOne(['id_pet' => $id_pet, 'id_owner_type' => $petOwnerType->id]);
        $pto = $pto ? $pto->id_owner : null;
        $petOwner = PetOwners::findOne($pto);

        $fullname = $phone = $email = $addrs = $reg_cert = 'Нет данных';

        if ($petOwner) {
            $fullname = $petOwner->fullname;
            $contacts = Contacts::find()
                ->joinWith('contact_type')
                ->andWhere(['contact_types.entity_type' => Contacts::ENTITY_TYPE_PET_OWNER, 'contacts.entity_id' => $petOwner->id])
                ->asArray()
                ->all();

            $phone = $this->getFirstContactOfType($contacts, ContactTypes::TYPE_PHONE);
            $email = $this->getFirstContactOfType($contacts, ContactTypes::TYPE_EMAIL);

            $addrs = $petOwner->fact_fias_addresses ? $petOwner->fact_fias_addresses->full_address : null;
            foreach ($petOwner->regCertificates as $cert) {
                if ($cert->id_pet === $pet->id) {
                    $reg_cert = $cert->number;
                    break;
                }
            }
        }

        $breed_name = ($br = Breeds::findOne($pet->id_breed)) ? $br->name : 'Нет данных';
        $species_name = ($sp = Species::findOne($pet->id_species)) ? $sp->name : 'Нет данных';

        $ident = PetIdentification::find()
            ->andWhere(['id_pet' => $pet->id, 'main_flag' => true])
            ->joinWith('ident_type')
            ->one();

        $ident_code = $ident ? $ident->identification_code : 'Нет данных';
        $ident_type = ArrayHelper::getValue($ident, 'ident_type.name', 'Нет данных');

        $org = Organizations::findOne($pet->id_reg_organization);
        $org_name = $org ? $org->short_name : 'Нет данных';

        $spec_fullname = \Yii::$app->user->getIdentity()->specialist->fullname;

        if ($pet->sex == 'm') {
            $sex = 'М';
        } elseif ($pet->sex == 'f') {
            $sex = 'Ж';
        } else {
            $sex = 'Нет данных';
        }

        if ($pet->is_main === true && !empty($pet->duplicates)) {
            $ids = ArrayHelper::getColumn($pet->duplicates, 'id', false);
            if (!empty($ids)) {
                array_unshift($ids, $id_pet);
            } else {
                $ids = $id_pet;
            }
        } else {
            $ids = $id_pet;
        }

        $pet_rabies_vaccination = $this->findPetRabiesVaccinations($ids);

        $pet_other_vaccinations = $this->findPetOtherVaccinations($ids);

        $pet_dehelmintization = $this->findPetDehelmintizations($ids);

        return [
            'fullname' => $fullname,
            'phone' => $phone,
            'email' => $email,
            'address' => $addrs,
            'breed' => $breed_name,
            'species' => $species_name,
            'name' => $pet->name,
            'ident_type' => $ident_type,
            'ident_code' => $ident_code,
            'sex' => $sex,
            'birthday' => $pet->birthday ? \DateTime::createFromFormat('Y-m-d', $pet->birthday)->format('d.m.Y') : null,
            'reg_date' => $pet->reg_date ? \DateTime::createFromFormat('Y-m-d', $pet->reg_date)->format('d.m.Y') : null,
            'org_name' => $org_name,
            'reg_cert' => $reg_cert,
            'pet_rabies_vaccination' => $pet_rabies_vaccination,
            'pet_other_vaccinations' => $pet_other_vaccinations,
            'pet_dehelmintization' => $pet_dehelmintization,
            'spec_fullname' => $spec_fullname,
        ];
    }

    private function getFirstContactOfType($contacts, $type = '')
    {
        if (empty($contacts) || $type == '') {
            return 'Нет данных';
        }

        if ($phone = array_filter($contacts, function ($x) use ($type) {
            return $x['main_flag'] == true
                && $x['contact_type']['type'] == $type;
        })) {
            return array_shift($phone)['name'];
        } else {
            $phone = array_filter($contacts, function ($x) use ($type) {
                return $x['contact_type']['type'] == $type;
            });

            if (!empty($phone)) {
                return array_shift($phone)['name'];
            }
        }

        return 'Нет данных';
    }

    /**
     * @param int|int[] $ids
     * @return array
     */
    private function findPetRabiesVaccinations($ids)
    {
        return PetRabiesVaccination::find()
            ->andWhere(['id_pet' => $ids])
            ->limit(2)
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * @param int|int[] $ids
     * @return array
     */
    private function findPetOtherVaccinations($ids)
    {
        return PetOtherVaccinations::find()
            ->andWhere(['id_pet' => $ids])
            ->limit(2)
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * @param int|int[] $ids
     * @return array
     */
    private function findPetDehelmintizations($ids)
    {
        return PetDehelmintization::find()
            ->andWhere(['id_pet' => $ids])
            ->limit(1)
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
    }
}
