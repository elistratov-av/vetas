<?php

namespace app\common\components\reports\interfaces;

use app\common\components\reports\dto\ResponseFileDto;

/**
 * Абстрактный класс для билдера по формированию файла отчета (документа)
 * Class AbstractPrintBuilder
 *
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
abstract class AbstractPrintBuilder
{
    /**
     * @var AbstractPrintDirector
     */
    protected $director;

    /**
     * Параметры файла
     *
     * @var FileOptionsDtoInterface
     */
    protected $fileOptions;

    /**
     * AbstractPrintBuilder constructor.
     *
     * @param AbstractPrintDirector $director
     */
    public function __construct(AbstractPrintDirector $director, FileOptionsDtoInterface $fileOptions)
    {
        $this->director = $director;
        $this->fileOptions = $fileOptions;
    }
    /**
     * Результат для созданого файла
     *
     * @return ResponseFileDto
     */
    public function getResponseFileDto(): ResponseFileDto
    {
        return new ResponseFileDto(
            [
                'fileName' => $this->fileOptions->getName(),
                'url'      => $this->fileOptions->getFullFileName(true),
                'mimeType' => $this->fileOptions->getMimeType(),
                'type'     => $this->fileOptions->getType(),
            ]
        );
    }
}
