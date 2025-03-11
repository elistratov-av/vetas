<?php

namespace app\common\components\reports\dto;

use app\common\dto\AbstractDto;

/**
 * DTO для возврата результата по формированию отчета
 * Class ResponseFileDto
 *
 * @author Aleksandr Roik
 */
class ResponseFileDto extends AbstractDto
{
    /**
     * Название файла
     *
     * @var string
     */
    public $fileName;

    /**
     * Mime Type файла
     *
     * @var string
     */
    public $mimeType;

    /**
     * Относительный путь к файлу
     *
     * @var string
     */
    public $url;

    /**
     * Тип (расширение)
     *
     * @see \app\common\components\reports\definitions\FileTypeDefinition
     * @var string
     */
    public $type;
}
