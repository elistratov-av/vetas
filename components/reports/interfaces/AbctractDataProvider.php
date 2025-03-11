<?php

namespace app\common\components\reports\interfaces;

/**
 * Абстрактный класс для провайдера данных отчета.
 * В наследние должны быть основные методы, что подготавилают и возвращаю данные для отчета
 * Class AbctractDataProvider
 *
 * @package app\common\components\reports\interfaces
 * @author  Aleksandr Roik
 */
abstract class AbctractDataProvider
{
    /**
     * @var ReportHandlerInterface
     */
    public $handler;

    /**
     * @var RequestDtoInterface
     */
    public $requestDto;

    /**
     * Хеш отчета исходя из его данных
     *
     * @var string
     */
    protected $hash;

    /***
     * AbstractDirector constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param RequestDtoInterface $requestDto
     */
    public function __construct(ReportHandlerInterface $handler, RequestDtoInterface $requestDto)
    {
        $this->handler = $handler;
        $this->requestDto = $requestDto;

        $this->init();
        $this->initHash();
    }

    /**
     * Инициализация доп параметров
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Инициализация хеша отчета.
     * Хеш должен идентифицировать уникальность отчета, в т.ч. и по его входящим параметрах
     *
     * @return void
     */
    abstract protected function initHash();

    /**
     * Возвращает хеш отчета
     *
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

}
