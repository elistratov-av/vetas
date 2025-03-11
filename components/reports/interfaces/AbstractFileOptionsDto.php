<?php

namespace app\common\components\reports\interfaces;

use app\common\dto\AbstractDto;
use LogicException;

/**
 * Class AbstractFileOptionsDto
 *
 * @property string $name
 * @property string $path
 * @property string $relativePath
 * @property string $mimeType
 * @property string $type
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
class AbstractFileOptionsDto extends AbstractDto implements FileOptionsDtoInterface
{
    /**
     * Название файла
     *
     * @return string
     */
    public function getName(): string
    {
        if (!$this->name) {
            throw new LogicException('Имя файла не задано');
        }

        return $this->name;
    }

    /**
     * Полный путь к файлу
     *
     * @return string
     */
    public function getPath(): string
    {
        if ($this->path && !file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        return $this->path;
    }

    /**
     * Относительный путь к файлу. Используется для формирования урлов
     *
     * @return string
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * Полный путь к файлу: getPath()|getRelativePath() + getName()
     *
     * @param boolean $relative Возвращать полный или относительный путь
     * @return string
     */
    public function getFullFileName($relative = false): string
    {
        return $relative ? $this->relativePath . '/' . $this->name : $this->path . '/' . $this->name;
    }

    /**
     * Mime тип файла
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Тип (расширение) для файла
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
