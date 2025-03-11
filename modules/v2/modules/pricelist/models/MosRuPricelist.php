<?php

namespace app\modules\v2\modules\pricelist\models;

use app\models\db\MosRuServices;
use app\models\db\Organizations;
use app\models\db\ServicesSpecialists;
use app\models\db\Specialists;
use app\modules\v2\modules\pricelist\skeletons\mosru\Lists;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class MosRuPricelist
 * @package app\modules\v2\modules\pricelist\models
 *
 * @property integer $id_organization
 * @property integer $id_specialist
 */
class MosRuPricelist extends Model
{
    /** @var integer */
    public $id_organization;

    /** @var integer */
    public $id_specialist;

    public function rules()
    {
        return [
            [['id_organization', 'id_specialist'], 'required'],
            [['id_organization', 'id_specialist'], 'integer'],
            [
                'id_organization',
                'exist',
                'targetClass' => Organizations::class,
                'targetAttribute' => 'id',
                'message' => 'Организация не найдена'
            ],
            [
                'id_specialist',
                'exist',
                'targetClass' => Specialists::class,
                'targetAttribute' => 'id',
                'message' => 'Специалист не найден'
            ],
            [
                'id_specialist',
                'exist',
                'targetClass' => Specialists::class,
                'targetAttribute' => ['id_specialist' => 'id', 'id_organization'],
                'message' => 'Специалист не является сотрудником данной организации'
            ],
        ];
    }

    /**
     * @return Lists
     */
    public function getServices()
    {
        $list = new Lists($this->findMosruServices());
        return $list;
    }

    /**
     * @param array $mosru_services
     * @return \stdClass
     */
    public function save(array $mosru_services)
    {
        $services = $this->findMosruServices(true);
        foreach ($mosru_services as $service) {
            if (!isset($services[$service['id']])) {
                continue;
            }
            if ($services[$service['id']]['provided'] != $service['provided']) {
                $this->saveService($service['id'], $service['provided']);
            }
        }

        $result = new \stdClass();
        $result->result = true;
        return $result;
    }

    /**
     * @param int $id
     * @param bool $provided
     */
    protected function saveService(int $id, bool $provided)
    {
        if ($provided) {
            $item = new ServicesSpecialists();
            $item->id_service = $id;
            $item->id_organization = $this->id_organization;
            $item->id_specialist = $this->id_specialist;
            $item->save(false);
        } else {
            ServicesSpecialists::deleteAll([
                'id_service' => $id,
                'id_organization' => $this->id_organization,
                'id_specialist' => $this->id_specialist
            ]);
        }
    }

    /**
     * @param bool $indexed
     * @return MosRuServices[]|\app\models\db\Shifts[]|\app\models\db\ShiftType[]|array|\yii\db\ActiveRecord[]
     */
    protected function findMosruServices($indexed = false)
    {
        $query = MosRuServices::find()
            ->select([
                'mosru.services.id',
                'mosru.services.name',
                'id_service_type',
                'type_name' => 'service_types.name',
                'at_home',
                'provided' => new Expression('services_specialists.id is not null'),
            ])
            ->innerJoin(
                'service_types',
                'service_types.id = mosru.services.id_service_type'
            )
            ->leftJoin(
                'services_specialists',
                'id_service = mosru.services.id and id_specialist = :id_specialist and id_organization = :id_organization',
                [
                    'id_specialist' => $this->id_specialist,
                    'id_organization' => $this->id_organization
                ]
            )
            ->orderBy([
                'service_types.sort_by' => SORT_ASC,
                'service_types.id' => SORT_ASC,
                'mosru.services.sort_by' => SORT_ASC
            ]);

        if ($indexed) {
            $query->indexBy('id');
        }

        return $query->asArray()
            ->all();
    }
}
