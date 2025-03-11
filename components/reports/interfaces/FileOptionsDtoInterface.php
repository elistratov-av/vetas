<?php

namespace app\common\components\reports\interfaces;

/**
 * Интерфейс для определения параметров файла
 *
 * Interface FileOptionsDtoInterface
 *
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
interface FileOptionsDtoInterface
{
    /**
     * Название файла
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Полный петь к файлу
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Относительный путь к файлу. Используется для формирования урлов
     *
     * @return string
     */
    public function getRelativePath(): string;

    /**
     * Полный путь к файлу: getPath()|getRelativePath() + getName()
     * @param boolean $relative Возвращать полный или относительный путь
     *
     * @return string
     */
    public function getFullFileName($relative = false): string;

    /**
     * Mime тип файла
     *
     * @return string
     */
    public function getMimeType(): string;

    /**
     * Тип (расширение) для файла
     *
     * @return string
     */
    public function getType(): string;
}
