<?php

namespace app\common\components\reports\handlers\PetHotelStayReport\v1\builders;

use app\common\components\reports\handlers\PetHotelStayReport\v1\DataProvider;
use app\common\components\reports\handlers\PetHotelStayReport\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;


/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 * * @property ReportDto $reportDto
 *
 * @property DataProvider $provider
 * @package app\common\components\reports\handlers\PetHotelStayReport\v1\builders
 */
class DataBuilder extends AbctractDataBuilder
{

    /**
     * Запуск построения
     *
     * @return $this|AbctractDataBuilder
     */
    public function build(string $reportDtoClass)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $provider = $this->provider;
            $this->reportDto = new $reportDtoClass([
                'dateFrom' => $provider->getDateFrom(),
                'dateTo' => $provider->getDateTo(),
                'animals' => $provider->getAnimals(),
                'animalType' => $provider->getAnimalType(),
                'priceForDay' => $provider->getPriceForDay()
            ]);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }
}