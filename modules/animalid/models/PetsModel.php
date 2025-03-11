<?php

namespace app\modules\animalid\models;

use app\models\db\animalid\Companies;
use app\models\db\Breeds;
use app\models\db\PetIdentification;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Species;
use app\modules\animalid\models\db\ConflictsModel;
use app\modules\animalid\models\db\Id_Map;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\interfaces\IntegrationModelInterface;

/**
 * Class PetsModel
 * @package app\modules\animalid\models
 */
class PetsModel extends Model implements IntegrationModelInterface
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $chip;
    /**
     * @var string
     */
    public $chipdate;
    /**
     * @var string
     */
    public $kind;
    /**
     * @var int
     */
    public $kindid;
    /**
     * @var string
     */
    public $breed;
    /**
     * @var int
     */
    public $breedid;
    /**
     * @var string
     */
    public $sex;
    /**
     * @var string
     */
    public $birthday;
    /**
     * @var string
     */
    public $createdate;
    /**
     * @var string
     */
    public $upddate;
    /**
     * @var int
     */
    public $companyid;
    /**
     * @var int
     */
    public $ownerid;

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return [
            'id',
            'chip',
            'chipdate',
            'kind',
            'kindid',
            'breed',
            'breedid',
            'sex',
            'birthday',
            'createdate',
            'upddate',
            'companyid',
            'ownerid',
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id', 'chip', 'chipdate', 'kind', 'breed', 'sex', 'birthday', 'createdate', 'upddate', 'companyid', 'ownerid',], 'safe'],
            ['chip', 'string', 'max' => 15],
            [['chip', 'sex', 'companyid', 'kindid', 'breedid'], 'required'],
            [['kindid', 'breedid'], 'integer'],
            ['chip', 'match', 'pattern' => '/^\d+$/', 'message' => 'Значение чипа должно быть целым числом до 15-ти знаков'],
            [['sex'], 'in', 'range' => ['самец', 'самка', 'мерин'], 'strict' => true],
            [
                'kindid',
                'required',
                'when' => function ($model) {
                    return !empty($model['kind']);
                }, 'skipOnEmpty' => false,
            ],
            [
                'breedid',
                'required',
                'when' => function ($model) {
                    return !empty($model['breed']);
                }, 'skipOnEmpty' => false,
            ],
        ];
    }

    /**
     * @return \app\models\db\Pets|null
     */
    public function findIdenty()
    {
        $id_code = PetIdentification::findOne(['identification_code' => $this->chip, 'main_flag' => true, 'id_ident_type' => 1]);
        if (!$id_code) {
            return null;
        }

        $pet = Pets::findOne(['id' => $id_code->id_pet]);

        return $pet;
    }

    /**
     * @param \app\models\db\Pets $found
     */
    public function merge($found)
    {
        $conflict = new ConflictsModel();
        $conflict->data = $this->toArray();
        $conflict->type = 'pet';
        $conflict->action = 'merge';
        $conflict->reason = 'merge';
        $conflict->our_id = $found->id;
        $conflict->save();
    }

    /**
     * @throws IntegrationException
     */
    public function update()
    {
        $id = Id_Map::getOurByTheir($this->id, 'pet');
        $pet = Pets::findOne(['id' => $id]);

        $org = Companies::findOne(['id' => Id_Map::getOurByTheir($this->companyid, 'company')]);
        if (!$org) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => 'update',
                'type' => 'pet',
            ];
            $message = 'Не найдена организация  чипировшая это животное. Внеш ид орг: ' . $this->companyid;
            throw new IntegrationException($errorModel, $message);
        }

        if ($this->ownerid) {
            $owner = PetsToOwner::findOne(Id_Map::getOurByTheir($this->ownerid, 'owner'));
            if (!$owner) {
                $errorModel = [
                    'data' => $this->toArray(),
                    'action' => 'update',
                    'type' => 'pet',
                ];
                $message = 'Новый владелец этого животного не был предварительно загружен';
                throw new IntegrationException($errorModel, $message);
            }


            $pto = PetsToOwner::findOne(['id_pet' => $id, 'id_owner_type' => 1]);
            if ($pto) {
                $pto->id_owner = $this->ownerid;
            } else {
                $pto = new PetsToOwner();
                $pto->id_owner = $owner->id;
                $pto->id_pet = $pet->id;
                $pto->id_owner_type = 1;
            }
            $pto->save();
        }

        $pi = PetIdentification::findOne(['main_flag' => true, 'id_pet' => $pet->id]);
        if (!$pi) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => 'update',
                'type' => 'pet',
            ];
            $message = 'Не найден чип животного. : ' . $this->chip;
            throw new IntegrationException($errorModel, $message);
        }
        $pi->created_at = $this->chipdate ? $this->chipdate : $pi->created_at;
        if ($pi->identif_org !== $this->companyid && $pi->identif_comp !== $this->companyid) {
            $pi->identif_comp = $org->id; // animalid.companies
            $pi->identif_org = null;
        }
        $pi->save();

        $this->integrateKindAndBreed($pet);

        $pet->sex = ($this->sex == 'самец' || $this->sex == 'мерин') ? 'm' : 'f';
        $pet->id_species = Id_Map::getOurByTheir($this->kindid, 'kind');
        $pet->id_breed = Id_Map::getOurByTheir($this->breedid, 'breed');
        $pet->birthday = $this->birthday;
        $pet->name = 'Импортированно из сервиса AnimalId';
        $pet->updated_at = time();
        if (!$pet->save()) {
            $errors = $pet->getErrorSummary(true);
            throw new IntegrationException($this->data, implode(' ', array_values($errors)));
        }
    }

    /**
     * @param $pet
     * @throws IntegrationException
     */
    private function integrateKindAndBreed(&$pet): void
    {
        if ($this->kind) {
            $our_id = Id_Map::getOurByTheir($this->kindid, 'kind');
            $model = new KindsModel();
            $model->load(array_merge(['name' => $this->kind], ['id' => $this->kindid]));
            $valid = $model->validate();
            if (!$valid) {
                $errors = $model->getErrorSummary(true);
                throw new IntegrationException($model, implode(' ', array_values($errors)));
            }
            if ($our_id) {
                if (Species::findOne($our_id)->name != $this->kind) {
                    $errorModel = [
                        'data' => $this->toArray(),
                        'action' => 'update',
                        'type' => 'pet',
                    ];
                    $message = 'Попытка обновления вида животного ' . Species::findOne($our_id)->name . '. Внеш вид: ' . $this->kind;
                    throw new IntegrationException($errorModel, $message);
                }
                $pet->id_species = $our_id;
            } elseif ($found = $model->findIdenty()) {
                $id_map = new Id_Map();
                $id_map->their = $this->kindid;
                $id_map->our = $found->id;
                $id_map->type = 'kind';
                $id_map->save();
                $pet->id_species = $found->id;

            } else {
                $model->save();
                $pet->id_species = Id_Map::getOurByTheir($this->kindid, 'kind');
            }
        }

        if ($this->breed) {
            $our_id = Id_Map::getOurByTheir($this->breedid, 'breed');
            $model = new BreedsModel();
            $model->load(array_merge(['name' => $this->breed, 'kind' => $this->kindid], ['id' => $this->breedid]));
            $valid = $model->validate();
            if (!$valid) {
                $errors = $model->getErrorSummary(true);
                throw new IntegrationException($model, implode(' ', array_values($errors)));
            }
            if ($our_id) {
                if (Breeds::findOne($our_id)->name != $this->breed) {
                    $errorModel = [
                        'data' => $this->toArray(),
                        'action' => 'update',
                        'type' => 'pet',
                    ];
                    $message = 'Попытка обновления породы животного ' . Breeds::findOne($our_id)->name . '. Внеш порода: ' . $this->kind;
                    throw new IntegrationException($errorModel, $message);
                }
                $pet->id_breed = $our_id;
            } elseif ($found = $model->findIdenty()) {
                $id_map = new Id_Map();
                $id_map->their = $this->breedid;
                $id_map->our = $found->id;
                $id_map->type = 'breed';
                $id_map->save();
                $pet->id_breed = $found->id;
            } else {
                $model->save();
                $pet->id_breed = Id_Map::getOurByTheir($this->breedid, 'breed');
            }
        }
    }

    /**
     * @throws IntegrationException
     */
    public function save()
    {
        $id = $this->id;

        $pet = new Pets();

        $org = Companies::findOne(['id' => Id_Map::getOurByTheir($this->companyid, 'company')]);
        if (!$org) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => 'save',
                'type' => 'pet',
            ];
            $message = 'Не найдена организация  чипировшая это животное. Внеш ид орг: ' . $this->companyid;
            throw new IntegrationException($errorModel, $message);
        }

        $pet->sex = ($this->sex == 'самец' || $this->sex == 'мерин') ? 'm' : 'f';
        $pet->name = 'Импортированно из сервиса AnimalId';
        $pet->birthday = $this->birthday;
        $pet->created_at = $this->createdate ? $this->createdate : time();

        $this->integrateKindAndBreed($pet);

        if ($pet->save()) {
            $our = $pet->getPrimaryKey();
            $id_map = new Id_Map();
            $id_map->their = $id;
            $id_map->our = $our;
            $id_map->type = 'pet';
            $id_map->save();

            $pi = new PetIdentification();
            $pi->id_pet = $our;
            $pi->identification_code = $this->chip;
            $pi->main_flag = true;
            $pi->created_at = $this->chipdate ? $this->chipdate : time();
            $pi->id_ident_type = 1;
            $pi->identif_comp = $org->id; // animalid.companies
            $pi->save();
        } else {
            throw new IntegrationException(
                array_merge(['data' => $this->toArray(), 'type' => 'pet', 'action' => 'save',]),
                'Не удалось сохранить животное: ' . implode(' ', $pet->getErrorSummary(true)));
        }

        if (!$this->ownerid) {
            return;
        }

        $owner = PetsToOwner::findOne(Id_Map::getOurByTheir($this->ownerid, 'owner'));
        if ($owner) {
            $pto = new PetsToOwner();
            $pto->id_owner = $owner->id;
            $pto->id_pet = $pet->getPrimaryKey();
            $pto->save();
        }
    }
}
