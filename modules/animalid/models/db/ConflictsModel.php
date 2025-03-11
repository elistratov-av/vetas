<?php

namespace app\modules\animalid\models\db;

use app\models\db\ActiveRecord;
use app\modules\animalid\models\CompanysModel;
use app\modules\animalid\models\PetsModel;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\exceptions\ValidationIntegrationException;

/**
 * @property int $id
 * @property array $data
 * @property string $action
 * @property string $type
 * @property string $reason
 * @property string $our_id
 */
class ConflictsModel extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'animalid.conflicts';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['data', 'action', 'type', 'reason'], 'safe'],
            [['our_id'], 'integer']
        ];
    }

    /**
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function merge()
    {
        $type = $this->type;
        $model = null;

        switch ($type)
        {
            case 'pet':
                $model = new PetsModel();
                break;
            case 'company':
                $model = new CompanysModel();
                break;
            default:
                return;
        }

        try {
            $model->load(array_merge($this->data, ['id' => $this->data['id']]));
            $valid = $model->validate();
            if (!$valid) {
                $errors = $this->model->getErrorSummary(true);
                throw new ValidationIntegrationException($this->data, implode(' ', array_values($errors)));
            }

            $id_map = new Id_Map();
            $id_map->their = $this->data['id'];
            $id_map->our = $this->our_id;
            $id_map->type = $type;
            $id_map->save();

            $model->update();

        } catch (IntegrationException $e) {
            $errorModel = new ErrorsModel();
            if ($e->object) {
                $errorModel->data = $e->object['data'];
                $errorModel->type = $e->object['type'];
                $errorModel->action = isset($e->object['action']) ? $e->object['action'] : null;
            }
            $errorModel->reason = $e->getMessage();
            $errorModel->save();
            return null;
        }

        $this->delete();
    }
}
