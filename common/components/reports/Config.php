<?php

namespace app\common\components\reports;

use app\common\dto\AbstractDto;
use Yii;

/**
 * Основной (общий) конфиг сервиса
 * Class Config
 *
 * @package app\common\components\reports
 * @author Aleksandr Roik
 */
class Config extends AbstractDto
{
    /**
     * @var string
     */
    public $filePath;

    /**
     * @param $value
     */
    protected function setFilePath($value)
    {
        $this->filePath = Yii::getAlias($value);
    }
}
