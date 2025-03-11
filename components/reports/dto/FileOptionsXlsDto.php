<?php

namespace app\common\components\reports\dto;

use app\common\components\reports\interfaces\AbstractFileOptionsDto;
use Yii;

/**
 * Параметры файла формата XLS
 * Class FileOptionsXlsDto
 *
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1
 * @author Aleksandr Roik
 */
class FileOptionsXlsDto extends AbstractFileOptionsDto
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var
     */
    protected $relativePath;

    /**
     * @var string
     */
    protected $mimeType = 'application/vnd.ms-excel';

    /**
     * @var string
     */
    protected $type = 'xls';

    /**
     * FileOptionsXlsDto constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->path = Yii::getAlias('@app/web/upload/reports');
        $this->relativePath = '/upload/reports';

        parent::__construct($data);
    }
}
