<?php

namespace app\modules\animalid\models;

use app\models\db\animalid\Companies;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\FiasAddresses;
use app\models\db\Organizations;
use app\modules\animalid\models\db\ConflictsModel;
use app\modules\animalid\models\db\Id_Map;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\interfaces\IntegrationModelInterface;
use yii\db\Query;

/**
 * Class CompanysModel
 * @package app\modules\animalid\models
 */
class CompanysModel extends Model implements IntegrationModelInterface
{
    const PHONE_REG_EXP = '/^((\+7|8)\d{10})$/';

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $fullname;
    /**
     * @var string
     */
    public $phone;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $address;

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return [
            'id',
            'fullname',
            'phone',
            'email',
            'address',
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id', 'fullname', 'phone', 'email', 'address',], 'safe'],
            [['id'], 'integer'],
            [['fullname', 'phone', 'email', 'address'], 'string', 'max' => 255],
            [['id', 'fullname', 'email'], 'required',],
            [['email'], 'email'],
            [['phone'], 'match', 'pattern' => self::PHONE_REG_EXP,
                'message' => 'Телефон должен быть в формате (+7|8)1234567890'],
        ];
    }

    /**
     * @return mixed
     */
    public function findIdenty()
    {
        $sub_query = (new Query())
            ->from('organizations org')
            ->andWhere([
                'org.name' => $this->fullname,
            ])
            ->select('org.id');

        // Нужен объект
        $org = Organizations::find()
            ->where(['IN', 'id', $sub_query])
            ->one();

        return $org;
    }

    /**
     * @param Organizations $found
     */
    public function merge($found)
    {
        $conflict = new ConflictsModel();
        $conflict->data = $this->toArray();
        $conflict->type = 'company';
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
        $comp = Companies::findOne(Id_Map::getOurByTheir($this->id, 'company')); //госов не обновляем, тру инфа по госам у ветаса

        if (empty($comp)){
            $errorModel = [
                'data' => $this->toArray(),
                'action' => 'update',
                'type' => 'company',
            ];
            $errors = $comp->getErrorSummary(true);
            $message = 'Не удалось найти организацию для обновления; Their id:' . $this->id;
            throw new IntegrationException($errorModel, $message);
        }

        $comp->fullname = $this->fullname;
        $comp->phone = $this->phone;
        $comp->email = $this->email;
        $comp->address = $this->address;

        if (!$comp->save()) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => 'update',
                'type' => 'company',
            ];
            $errors = $comp->getErrorSummary(true);
            $message = 'Ошибка при обновлении организации ' . implode("\n", array_values($errors));
            throw new IntegrationException($errorModel, $message);
        }
    }

    /**
     * @throws IntegrationException
     */
    public function save()
    {
        $comp = new Companies(); //госов не создаем, все госы уже известны ветасу
        $comp->fullname = $this->fullname;
        $comp->phone = $this->phone;
        $comp->email = $this->email;
        $comp->address = $this->address;

        if ($comp->save()) {
            $our = $comp->getPrimaryKey();
            $id_map = new Id_Map();
            $id_map->their = $this->id;
            $id_map->our = $our;
            $id_map->type = 'company';
            $id_map->save();
        } else {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => 'save',
                'type' => 'company',
            ];
            $errors = $comp->getErrorSummary(true);
            $message = 'Ошибка при сохранении организации ' . implode("\n", array_values($errors));
            throw new IntegrationException($errorModel, $message);
        }
    }

    /**
     * Обрабатываем поле email и создаем/обновляем контакт
     *
     * @param $action
     * @param $org_id
     * @throws IntegrationException
     */
    protected function processFieldEmail($action, $org_id)
    {
        if (empty($this->email)) {
            return;
        }

        // Ищем такой же в бд, что бы понять надо ли что делать
        $mail = Contacts::find()
            ->andWhere([
                'entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION,
                'entity_id' => $org_id,
                'name' => $this->email,
            ])
            ->orderBy(['main_flag' => SORT_DESC])
            ->one();

        // Такого нет - создаем
        if (!$mail) {
            $mail = new Contacts();
            $mail->id_contact_type = ContactTypes::findOne([
                'entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION,
                'type' => 'email'
            ])->id;
            $mail->main_flag = true;
            $mail->entity_type = Contacts::ENTITY_TYPE_ORGANIZATION;
            $mail->entity_id = $org_id;
        }

        $mail->name = $this->email;

        // Сохраняем обновленный/созданный или пишем в error_log
        if (!$mail->save()) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => $action,
                'type' => 'company',
            ];
            $errors = $mail->getErrorSummary(true);
            $message = 'Ошибка при сохранении email организации ' . implode("\n", array_values($errors));
            throw new IntegrationException($errorModel, $message);
        }
    }

    /**
     * Обрабатываем поле phone и создаем/обновляем контакт
     *
     * @param $action
     * @param $org_id
     * @throws IntegrationException
     */
    protected function processFieldPhone($action, $org_id)
    {
        if (empty($this->phone)) {
            return;
        }

        if (!substr($this->phone, 0, 1) !== '+') {
            $this->phone = '+7' . substr($this->phone, 1, strlen($this->phone) - 1);
        }

        // Ищем такой же в бд, что бы понять надо ли что делать
        $phone = Contacts::find()
            ->andWhere([
                'entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION,
                'entity_id' => $org_id,
                'name' => $this->phone,
            ])
            ->orderBy(['main_flag' => SORT_DESC])
            ->one();

        // Такого нет - создаем
        if (!$phone) {
            $phone = new Contacts();
            $phone->id_contact_type = ContactTypes::findOne([
                'entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION,
                'type' => 'phone'
            ])->id;
            $phone->main_flag = true;
            $phone->entity_type = Contacts::ENTITY_TYPE_ORGANIZATION;
            $phone->entity_id = $org_id;
        }

        $phone->name = $this->phone;

        // Сохраняем обновленный/созданный или пишем в error_log
        if (!$phone->save()) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => $action,
                'type' => 'company',
            ];
            $errors = $phone->getErrorSummary(true);
            $message = 'Ошибка при сохранении телефона организации ' . implode("\n", array_values($errors));
            throw new IntegrationException($errorModel, $message);
        }
    }

    /**
     * Обрабатываем поле address
     *
     * @param $action
     * @param Organizations $organization
     * @throws IntegrationException
     */
    protected function processFieldAddress($action, $organization)
    {
        if (empty($this->address)) {
            return;
        }

        // Ищем такой же в бд, что бы понять надо ли что делать
        $addr = FiasAddresses::findOne(['full_address' => $this->address]);

        // Такого нет - создаем
        if (!$addr) {
            $addr = new FiasAddresses();
            $addr->full_address = $this->address;
        }

        // Сохраняем созданный и прицепляем к организации
        $error = false;
        if ($addr->save()) {
            $organization->id_fias_address = $addr->getPrimaryKey();
        } else {
            $error = true;
        }

        // Адрес сохранился, пытаемся сохранить организацию (привязку обновляли)
        if (!$error && !$organization->save()) {
            $error = true;
        }

        // или пишем в error_log
        if ($error) {
            $errorModel = [
                'data' => $this->toArray(),
                'action' => $action,
                'type' => 'company',
            ];
            $errors = $addr->getErrorSummary(true);
            $org_errors = $organization->getErrorSummary(true);

            $message = 'Ошибка при сохранении адреса организации '
                . implode("\n", array_values($errors))
                . implode("\n", array_values($org_errors));
            throw new IntegrationException($errorModel, $message);
        }
    }
}
