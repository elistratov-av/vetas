<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1;

use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\MakePeriodRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\models\db\Diseases;
use app\models\db\Organizations;
use app\models\db\Pets;
use app\models\db\ServiceTypes;
use app\models\db\Specialists;
use app\models\db\Species;
use app\models\db\tmc\TmcBase;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitServiceTmcPet;
use yii\db\Exception;
use DateTime;
use yii\db\Expression;
use yii\db\Query;

/**
 * Провайдер данных отчета за период.
 * Class PerionDataProvider
 *
 * @property MakePeriodRequestDto $requestDto
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1
 * @author Aleksandr Roik
 */
class PerionDataProvider extends AbctractDataProvider
{
    /**
     * @var Specialists
     */
    protected $specialist;

    /**
     * @var Organizations
     */
    protected $organization;

    /**
     * DataProvider constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param MakePeriodRequestDto $requestDto
     */
    public function __construct(ReportHandlerInterface $handler, RequestDtoInterface $requestDto)
    {
        parent::__construct($handler, $requestDto);
        $this->specialist = Specialists::findOne($requestDto->idSpecialist);
        $this->organization = Organizations::findOne($requestDto->idOrganization);
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = md5(
            $this->requestDto->idOrganization .
            $this->requestDto->idSpecialist .
            $this->requestDto->date->format('YmdHis')
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function findTmc(): array
    {
        // Отбираем ТМЦ и услуги в приеме
        $tmc = TmcBase::find()
            ->joinWith('visitServiceTmc.visit')
            ->joinWith('visitServiceTmc.visit.visitsSpecialists')
            ->joinWith('visitServiceTmc.visitServiceTmcPet.pet.species')
            ->joinWith('visitServiceTmc.visitsGovService.service.serviceType')
            ->with([
                'visitServiceTmc' => function (Query $query) {
                    $query
                        ->joinWith('visit')
                        ->joinWith('visit.visitsSpecialists')
                        ->joinWith('visitServiceTmcPet.pet.species')
                        ->joinWith('visitsGovService.service.serviceType')
                        ->andWhere(
                            new Expression("fact_start_dttm::date between '" .
                                $this->requestDto->date->format('Y-m-d 00:00:00') . "' AND '" .
                                $this->requestDto->date->format('Y-m-d 23:59:59') . "'"
                            )
                        )
                        ->andWhere(['id_organization' => $this->organization->id])
                        ->andWhere(['visits_specialists.id_specialist' => $this->specialist->id])
                        ->andWhere(['service_types.id' => ServiceTypes::TYPE_VACCINATION])
                        ->andWhere(['in', 'species.tech_name', [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]]);
                }
            ])
            ->andWhere(['tmc.type' => TmcBase::TYPE_VACCINE])
            ->andWhere(
                new Expression("visits.fact_start_dttm::date between '" .
                    $this->requestDto->date->format('Y-m-d 00:00:00') . "' AND '" .
                    $this->requestDto->date->format('Y-m-d 23:59:59') . "'"
                )
            )
            ->andWhere(['visits.id_organization' => $this->organization->id])
            ->andWhere(['visits_specialists.id_specialist' => $this->specialist->id])
            ->andWhere(['service_types.id' => ServiceTypes::TYPE_VACCINATION])
            ->andWhere(['in', 'species.tech_name', [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]])
            ->andWhere([
                'exists',
                (new Query())
                    ->select('diseases.id')
                    ->from('tmc.tmc_to_diseases')
                    ->leftJoin('diseases', 'diseases.id = tmc.tmc_to_diseases.id_disease')
                    ->andWhere(['ilike', 'diseases.name', Diseases::NAME_RABIES])
                    ->andWhere('tmc.tmc_to_diseases.id_tmc = tmc.tmc.id')
            ])
            ->all();

        if (!$tmc) {
            throw new Exception('Данные для отчета не найдены');
        }

        return $tmc;
    }

    /**
     * Возвращает список услуг по оперделенному ТМЦ и балансу
     *
     * @param int $idTmc
     * @param array $vstIds
     * @return VisitServiceTmc[]
     */
    public function findVisitServiceTmcByIds(array $vstIds, int $tmcId, ?int $balanceId): array
    {
        return VisitServiceTmc::find()
            ->with([
                'visit',
                'visit.visitsSpecialists',
                'visitServiceTmcPet.pet.species' => function (Query $query) {
                    $query->andWhere(['in', 'tech_name', [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]]);
                },
                'visitsGovService.service.serviceType'
            ])
            ->andWhere([
                'id'             => $vstIds,
                'id_tmc'         => $tmcId,
                'id_balance_tmc' => $balanceId,
            ])
            ->all();
    }

    /**
     * Опеределяем и возвращает дату акта
     *
     * @return DateTime
     * @throws Exception
     */
    public function getAcceptorDate(): DateTime
    {
        return $this->requestDto->date;
    }

    /**
     * Номер.
     * Маска:
     * {акт списания материальных запасов}{номер акта}{дата}.
     * Номер акта генерировать по маске АС{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Акт списания материальных запасов № АС77-01-01/03-2021-001 от 01.03.2021
     *
     * @return string
     */
    public function getNumber(): string
    {
        return
            'АВ' .
            $this->organization->reg_number .
            '/' .
            $this->requestDto->date->format('m') .
            '-' .
            $this->requestDto->date->format('Y') .
            '-' .
            NumberGenerator::generateWithPeriodYear(
                $this->handler->getReportId(),
                $this->handler->getVerion(),
                $this->getHash(),
                $this->requestDto->date
            );
    }

    /**
     * Название организации
     *
     * @return string|null
     */
    public function getOrganizationName(): ?string
    {
        return $this->organization->short_name;
    }

    /**
     * Возвращает список специалистов
     *
     * @param TmcBase $tmc
     */
    public function getSpecialists(TmcBase $tmc)
    {
        return [
            $this->specialist, //TODO: пока один специалист, он же из услуги ТМЦ
        ];
    }

    /**
     * Считаем собак
     *
     * @param VisitServiceTmcPet[] $visitServiceTmcPets
     * @return int
     */
    public function getDogCount(array $visitServiceTmcPets): int
    {
        $count = 0;

        foreach ($visitServiceTmcPets as $visitServiceTmcPet) {
            if ($visitServiceTmcPet->pet->species->tech_name == Species::TECH_NAME_DOG) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Считаем маленьких собак
     *
     * @param VisitServiceTmcPet[] $visitServiceTmcPets
     * @return int
     */
    public function getDogIsSmallCount(array $visitServiceTmcPets): int
    {
        $count = 0;
        foreach ($visitServiceTmcPets as $visitServiceTmcPet) {
            if ($visitServiceTmcPet->pet->size_id === Pets::SIZE_SMALL && $visitServiceTmcPet->pet->species->tech_name == Species::TECH_NAME_DOG) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Считаем котов
     *
     * @param VisitServiceTmcPet[] $visitServiceTmcPets
     * @return int
     */
    public function getCatCount(array $visitServiceTmcPets): int
    {
        $count = 0;
        foreach ($visitServiceTmcPets as $visitServiceTmcPet) {
            if ($visitServiceTmcPet->pet->species->tech_name == Species::TECH_NAME_CAT) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param VisitServiceTmc[] $visitServiceTmcs
     * @return string[]
     */
    public function getOwnerNames(array $visitServiceTmcs)
    {
        $result = [];
        foreach ($visitServiceTmcs as $visitServiceTmc) {
            $result[] = $visitServiceTmc->visit->owner->fullname;
        }

        return $result;
    }

}
