<?php


namespace app\modules\animalid\models;


use app\models\db\Breeds;
use app\modules\animalid\models\db\ConflictsModel;
use app\modules\animalid\models\db\Id_Map;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\interfaces\IntegrationModelInterface;


/**
 * порода
 * @property  $id
 * @property  $name
 * @property  $kind
 */
class BreedsModel extends Model implements IntegrationModelInterface
{
    public $id;
    public $name;
    public $kind;

    public function attributes()
    {
        return [
            'id',
            'name',
            'kind'
        ];
    }

    public function rules()
    {
        return [
            [['id', 'name', 'kind'], 'safe'],
            [['id', 'kind'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['kind'], 'exist', 'skipOnError' => true, 'targetClass' => Id_Map::class, 'targetAttribute' => ['kind' => 'their']],
        ];
    }

    /**
     * @return Breeds|array|null|\yii\db\ActiveRecord
     * @throws IntegrationException
     */
    public function findIdenty()
    {
        return Breeds::find()
            ->where([
                'name' => $this->name,
                'species_id' => Id_Map::getOurByTheir($this->kind, 'kind')])
            ->one();

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
        $id = Id_Map::getOurByTheir($this->id, 'breed');
        $breed = Breeds::findOne(['id' => $id]);
        $breed->name = $this->name;
        $breed->species_id = Id_Map::getOurByTheir($this->kind, 'kind');
        $breed->save();
    }

    /**
     * @throws IntegrationException
     */
    public function save()
    {
        $breed = new Breeds(['name' => $this->name, 'species_id' => Id_Map::getOurByTheir($this->kind, 'kind')]);
        if ($breed->save()) {
            $our = $breed->getPrimaryKey();
            $id_map = new Id_Map();
            $id_map->their = $this->id;
            $id_map->our = $our;
            $id_map->type = 'breed';
            $id_map->save();
        } else {
            throw new IntegrationException(
                array_merge(['data' => $this->toArray(), 'type' => 'breed']),
                'Не удалось сохранить породу животного: ' . implode(' ', $breed->getErrorSummary(true)));
        }

    }
}
