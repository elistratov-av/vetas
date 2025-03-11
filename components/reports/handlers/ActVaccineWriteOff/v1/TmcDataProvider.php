<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1;

use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\MakeTmcRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\NumberGenerator;
use app\models\db\Specialists;
use app\modules\admin\models\Organization;
use Exception;
use DateTime;

/**
 * Class ByTmcDataProvider
 *
 * @property-read MakeTmcRequestDto $requestDto
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1
 */
class TmcDataProvider extends AbctractDataProvider
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = md5(
            $this->organization->id .
            $this->date->format('YmdHis')
        );
    }

    /**
     * @throws Exception
     */
    protected function init()
    {
        if (!array_key_exists('acceptor_date', $this->requestDto->data)) {
            throw new Exception('Отсутсвует параметр acceptor_date');
        }
        if (!array_key_exists('organization_id', $this->requestDto->data)) {
            throw new Exception('Отсутсвует параметр organization_id');
        }
        $this->date = new DateTime($this->requestDto->data['acceptor_date']);
        $this->organization = $this->getOrganizationById($this->requestDto->data['organization_id']);
    }

    /**
     * @return array
     */
    public function getTmcs(): array
    {
        if (!array_key_exists('tmc', $this->requestDto->data)) {
            throw new Exception('Отсутсвует параметр tmc');
        }

        if (!$this->requestDto->data['tmc']) {
            throw new Exception('Данные для отчета не найдены');
        }

        return $this->requestDto->data['tmc'];
    }

    /**
     * Опеределяем и возвращает дату акта
     *
     * @return DateTime
     * @throws Exception
     */
    public function getAcceptorDate(): DateTime
    {
        return $this->date;
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
            $this->date->format('m') .
            '-' .
            $this->date->format('Y') .
            '-' .
            NumberGenerator::generateWithPeriodYear(
                $this->handler->getReportId(),
                $this->handler->getVerion(),
                $this->getHash(),
                $this->date
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
     * @param array $list
     * @return array
     */
    public function getSpecialistsByTmc(array $list)
    {
        if (!array_key_exists('specialists', $list)) {
            throw new Exception('Отсутсвует параметр specialists');
        }

        return $list['specialists'];
    }

    /**
     * Возвращает специалиста
     *
     * @param array $list
     * @return Specialists
     */
    public function getSpecialistBySpecialist(array $list)
    {
        if (!array_key_exists('specialist_id', $list)) {
            throw new Exception('Отсутсвует параметр specialist_id');
        }

        return $this->getSpecialistById($list['specialist_id']);
    }

    /**
     * Возвращает организацию
     *
     * @param array $list
     * @return Organization
     */
    public function getOrganizationBySpecialist(array $list)
    {
        if (!array_key_exists('organization_id', $list)) {
            throw new Exception('Отсутсвует параметр organization_id');
        }

        return $this->getOrganizationById($list['organization_id']);
    }

    /**
     * Свозвращает специалиста по его id
     *
     * @param int $id
     * @return Specialists
     */
    private function getSpecialistById(int $id): Specialists
    {
        return Specialists::findOne($id);
    }

    /**
     * Свозвращает специалиста по его id
     *
     * @param int $id
     * @return Specialists
     */
    private function getOrganizationById(int $id): Organization
    {
        return Organization::findOne($id);
    }
}
