<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto;

use app\common\components\reports\handlers\ActVaccineWriteOff\v1\definitions\TemplateFileDefinition;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Class MakeTmcRequestDto
 *
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto
 */
class MakeTmcRequestDto extends AbstractDto implements RequestDtoInterface
{

    /**
     * Название файла-шаблона
     *
     * @var string
     * @see TemplateFileDefinition
     */
    public $template = TemplateFileDefinition::TEMPLATE_XLS1;

    /**
     * @var ReportDto
     */
    public $data;
}
