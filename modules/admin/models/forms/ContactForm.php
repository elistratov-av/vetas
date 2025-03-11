<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.12.18
 * Time: 14:38
 */

namespace app\modules\admin\models\forms;

use app\models\db\ContactTypes;
use app\modules\admin\models\Contacts;
use Yii;
use yii\base\Model;

/**
 * Class ContactForm
 * @package app\modules\admin\models\forms
 */
class ContactForm extends Model
{

    public $id_contact_type;
    public $entity_id;
    public $name;


    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id_contact_type', 'name'], 'required'],
            [
                'name', 'match',
                'pattern' => Contacts::PHONE_REG_EXP,
                'when' => function() {
                    $valid = ContactTypes::find()->where([
                        'entity_type' => 'pet_owner',
                        'type' => ContactTypes::TYPE_PHONE])
                        ->indexBy('id')
                        ->asArray()
                        ->column();
                    return in_array($this->id_contact_type, $valid);
                },
                'message' => 'Телефон должен быть в формате +71234567890',
            ],
            [
                'name',
                'email',
                'when' => function() {
                    $valid = ContactTypes::find()->where([
                        'entity_type' => 'pet_owner',
                        'type' => ContactTypes::TYPE_EMAIL])
                        ->indexBy('id')
                        ->asArray()
                        ->column();
                    return in_array($this->id_contact_type, $valid);
                },
                'message' => 'Введенный email имеет ошибочный формат'
            ]
        ];
    }


    /**
     * @return mixed
     * @throws \Throwable
     */
    public function save()
    {
        return Yii::$app->db->transaction(function(){
            $cont = new Contacts();
            $cont->entity_type = 'pet_owner';
            $this->entity_id = Yii::$app->request->get()['owner'];
            $cont->setAttributes($this->getAttributes());

            if ($cont->validate()) {
                $cont->save();
                return true;
            } else {
                return false;
            }
        });
    }


    /**
     * @param Contacts $contact
     * @return mixed
     * @throws \Throwable
     */
    public function edit(Contacts $contact)
    {
        return Yii::$app->db->transaction(function() use ($contact){
            $contact->setAttributes($this->getAttributes());
            $contact->id_contact_type = (integer)$contact->id_contact_type;
            $contact->entity_id = Yii::$app->request->get()['owner'];
            if ($contact->validate()) {
                $contact->save();
                return true;
            } else {
                return false;
            }
        });
    }
}
