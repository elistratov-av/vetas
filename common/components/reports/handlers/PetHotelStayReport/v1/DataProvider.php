<?php

namespace app\common\components\reports\handlers\PetHotelStayReport\v1;

use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\models\db\GovServices;
use app\models\db\PetHotel;
use app\models\db\Pricelists;
use app\modules\v2\modules\pethotels\models\ExtPetHotelRequest;
use app\models\db\PetHotelRequestStatus;
use app\models\db\PetHotelRoom;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Провайдер данных отчета.
 * Здесь должны буть основные методы, что подготавилают и возвращаю данные для отчета
 * Class DataProvider
 *
 * @package app\common\components\reports\handlers\PetHotelStayReport\v1
 */
class DataProvider extends AbctractDataProvider
{
    /**
     * @var PetHotelRequestStatus
     */
    private $statusFinish;

    /**
     * @array PetHotelRequest[]
     */
    private $animals;

    /**
     * @array PetHotelRoom[]
     */
    private $rooms;

    /**
     * @float
     */
    private $priceForDay;

    /**
     * DataProvider constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param MakeRequestDto $requestDto
     */
    public function __construct(ReportHandlerInterface $handler, RequestDtoInterface $requestDto)
    {
        parent::__construct($handler, $requestDto);

        $this->initRooms();
        $this->initStatusFinish();
        $this->initPriceForDay();
        $this->initRequests();
    }

    /**
     * @throws Exception
     */
    private function initRequests()
    {
        $this->animals = ExtPetHotelRequest::find()
            ->alias('phr')
            ->select('phr.* , species.name as species_name')
            ->leftJoin('pets', 'pets.id=phr.id_animal')
            ->leftJoin('species', 'species.id=pets.id_species')
            ->where(['>=', 'phr.date_to', date('Y-m-d', strtotime($this->requestDto->dateFrom)) . ' 00:00:00'])
            ->andWhere(['<=', 'phr.date_from', date('Y-m-d', strtotime($this->requestDto->dateTo)) . ' 23:59:59'])
            ->andWhere(['phr.id_status' => $this->statusFinish->id])
            ->andWhere(['in', 'id_room', ArrayHelper::map($this->rooms, 'id', 'id')])
            ->all();
    }

    /**
     * @throws Exception
     */
    private function initStatusFinish()
    {
        $this->statusFinish = PetHotelRequestStatus::find()
            ->where(['code' => 'Завершено'])
            ->one();

        if (empty($this->statusFinish)) {
            throw new \Exception("Не найден статус 'Завершено'");
        }
    }

    /**
     * @throws Exception
     */
    private function initRooms()
    {
        $obj = PetHotelRoom::find()->where(['id_pet_hotel' => $this->requestDto->idPetHotel]);

        $condition = [];
        if (in_array('Cats', $this->requestDto->animalType)) {
            $condition[] = ['purpose' => 'Для кошек'];
        }

        if (in_array('Dogs', $this->requestDto->animalType)) {
            $condition[] = ['purpose' => 'Для собак'];
        }


        if (count($this->requestDto->animalType) == 1 && in_array('Other', $this->requestDto->animalType)) {
            $obj->andWhere(['purpose' => 'Для иных']);
        }elseif (!empty($condition)) {
            $conditionTemp = array_merge(['or'], $condition);
            $obj->andWhere($conditionTemp);
        }

        $this->rooms = $obj->all();
    }

    /**
     * @throws Exception
     */
    private function initPriceForDay()
    {
        $petHotel = PetHotel::find()
            ->where(['id' => $this->requestDto->idPetHotel])
            ->one();

        if (empty($petHotel)) {
            throw new \Exception("Зоогостиница не найдена");
        }

        $priceList = Pricelists::find()
            ->where(['id_organization' => $petHotel->id_organization])
            ->one();

        if (empty($priceList)) {
            throw new \Exception("Прайс-лист не найден");
        }

        $service = GovServices::find()
            ->where(['id_pricelist' => $priceList->id])
            ->andWhere(['cod' => $this->requestDto->cod])
            ->one();

        if (empty($service)) {
            throw new \Exception("Цена на содержание животных в зоогостинице не найдена");
        }

        $this->priceForDay = $service->price;
    }

    /**
     * @return array|null
     */
    public function getAnimals(): ?array
    {
        return $this->animals;
    }

    /**
     * @return array|null
     */
    public function getAnimalType(): ?array
    {
        return $this->requestDto->animalType;
    }

    /**
     * @return string|null
     */
    public function getDateFrom(): ?string
    {
        return $this->requestDto->dateFrom;
    }

    /**
     * @return string|null
     */
    public function getDateTo(): ?string
    {
        return $this->requestDto->dateTo;
    }

    /**
     * @return float|null
     */
    public function getPriceForDay(): ?string
    {
        return $this->priceForDay;
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = null;
    }
}