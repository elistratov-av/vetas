<?php


namespace app\modules\animalid\models;


use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\FiasAddresses;
use app\models\db\PetOwners;
use app\modules\animalid\models\db\Id_Map;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\interfaces\IntegrationModelInterface;


/**
 * Class OwnersModel
 * @package app\modules\animalid\models
 * @property $id
 * @property $fullName
 * @property $phone
 * @property $email
 * @property $address
 */
class OwnersModel extends Model implements IntegrationModelInterface
{
    public $id;
    public $fullName;
    public $phone;
    public $email;
    public $address;

    public function attributes()
    {
        return [
            'id',
            'fullName',
            'phone',
            'email',
            'address',
        ];
    }

    public function rules()
    {
        return [
            [['id', 'fullName', 'phone', 'email', 'address'], 'safe'],
            [['id', 'kind'], 'integer'],
            [['fullName', 'phone', 'email', 'address'], 'string', 'max' => 255],
            [['id', 'fullName', 'email'], 'required',],
        ];
    }

    /**
     * @return mixed
     * @throws IntegrationException
     */
    public function findIdenty()
    {

        try {
            $F_FIO = explode(' ', $this->fullName)[0];
            $I_FIO = explode(' ', $this->fullName)[1];
        } catch (\Exception $e) {
            throw new IntegrationException(
                array_merge(['data' => $this->toArray(), 'type' => 'owner']),
                'Не удалось разделить ФИО: ' . $e->getMessage());
        }

        $pet_owner = PetOwners::find()
            ->with('contacts as co')
            ->with('contact_types as ct')
            ->where([
                'pet_owners.f_fio' => $F_FIO,
                'pet_owners.i_fio' => $I_FIO,
                'co.entity_type' => Contacts::ENTITY_TYPE_PET_OWNER,
                'co.entity_id' => 'pet_owners.id',
                'co.name' => $this->phone,
                'co.id_contact_type' => 'ct.id',
                'ct.type' => 'phone',
            ])->one();

        return $pet_owner;
    }

    /**
     * @param PetOwners $found
     */
    public function merge($found)
    {
        return;
    }

    /**
     * @throws IntegrationException
     */
    public function update()
    {
        try {
            $F_FIO = explode(' ', $this->fullName)[0];
            $I_FIO = explode(' ', $this->fullName)[1];
        } catch (\Exception $e) {
            throw new IntegrationException(
                array_merge(['data' => $this->toArray(), 'type' => 'owner']),
                'Не удалось разделить ФИО: ' . $e->getMessage());
        }

        $pet_owner = PetOwners::findOne(Id_Map::getOurByTheir($this->id, 'owner'));

        $pet_owner->f_fio = $F_FIO;
        $pet_owner->i_fio = $I_FIO;

        if ($this->email) {
            $mail = Contacts::findOne(['entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER, 'entity_id' => $pet_owner->id]);
            if (!$mail) {
                $mail = new Contacts();
                $mail->id_contact_type = ContactTypes::findOne([
                    'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
                    'type' => 'email'
                ]);
                $mail->main_flag = true;
                $mail->entity_type = Contacts::ENTITY_TYPE_PET_OWNER;
                $mail->entity_id = $pet_owner->getPrimaryKey();
            }

            $mail->name = $this->email;
            $mail->save();
        }

        if ($this->address) {
            $addr = FiasAddresses::findOne($pet_owner->id_fias_address);
            if (!$addr) {
                $addr = new FiasAddresses();
            }
            $addr->full_address = $this->address;
            $pet_owner->id_fias_address = $addr->getPrimaryKey();
            $addr->save();
        }

        $pet_owner->save();
    }

    /**
     * @throws IntegrationException
     */
    public function save()
    {
        $F_FIO = explode(' ', $this->fullName)[0];
        $I_FIO = explode(' ', $this->fullName)[1];

        $pet_owner = new PetOwners();
        $pet_owner->f_fio = $F_FIO;
        $pet_owner->i_fio = $I_FIO;


        if ($this->address) {
            $addr = new FiasAddresses();
            $addr->full_address = $this->address;
            $addr->save();
            $pet_owner->id_fias_address = $addr->getPrimaryKey();
        }

        if ($pet_owner->save()) {
            $our = $pet_owner->getPrimaryKey();
            $id_map = new Id_Map();
            $id_map->their = $this->id;
            $id_map->our = $our;
            $id_map->type = 'owner';
            $id_map->save();
        } else {
            throw new IntegrationException(
                array_merge(['data' => $this->toArray(), 'type' => 'owner']),
                'Не удалось сохранить владельца животного: ' . implode(' ', $pet_owner->getErrorSummary(true)));
        }


        $mail = new Contacts();
        $mail->name = $this->email;
        $mail->id_contact_type = ContactTypes::findOne([
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
            'type' => 'email'
        ]);
        $mail->main_flag = true;
        $mail->entity_type = Contacts::ENTITY_TYPE_PET_OWNER;
        $mail->entity_id = $pet_owner->getPrimaryKey();
        $mail->save();

        if ($this->phone) {
            $phone = new Contacts();
            $phone->name = $this->phone;
            $phone->id_contact_type = ContactTypes::findOne([
                'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
                'type' => 'phone'
            ]);
            $phone->main_flag = true;
            $phone->entity_type = Contacts::ENTITY_TYPE_PET_OWNER;
            $phone->entity_id = $pet_owner->getPrimaryKey();
            $phone->save();

        }
    }

}
