<?php


namespace app\modules\animalid\models;


use app\models\db\Species;
use app\modules\animalid\models\db\Id_Map;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\interfaces\IntegrationModelInterface;

/**
 * Вид
 * @property string $id
 * @property string $name
 */
class KindsModel extends Model implements IntegrationModelInterface
{
    public $id;
    public $name;

    public function attributes()
    {
        return [
            'id',
            'name',
        ];
    }

    public function rules()
    {
        return [
            [['id', 'name'], 'safe'],
            [['id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function findIdenty()
    {
        return Species::find()->where(['name' => $this->name])->one();

    }

    public function merge($found)
    {
        return;
    }

    /**
     * @throws \yii\db\StaleObjectException
     * @throws IntegrationException
     */
    public function update()
    {
        $id = Id_Map::getOurByTheir($this->id, 'kind');
        $species = Species::findOne(['id' => $id]);
        $species->name = $this->name;
        $species->save();
    }

    /**
     * @throws IntegrationException
     */
    public function save()
    {
        $species = new Species(['name' => $this->name]);
        if($species->save()){
            $our = $species->getPrimaryKey();
            $id_map = new Id_Map();
            $id_map->their = $this->id;
            $id_map->our = $our;
            $id_map->type = 'kind';
            $id_map->save();
        }else
        {
            throw new IntegrationException(
                array_merge(['data' => $this->toArray(), 'type' => 'kind']),
                'Не удалось сохранить вид животного');
        }

    }
}
