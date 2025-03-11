<?php

namespace app\common\components\reports\dto;

use app\common\components\reports\interfaces\AbstractFileOptionsDto;
use Yii;

/**
 * Параметры файла формата PDF
 * Class FileOptionsXlsDto
 *
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1
 * @author Aleksandr Roik
 */
class FileOptionsPdfDto extends AbstractFileOptionsDto
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
    protected $mimeType = 'application/pdf';

    /**
     * @var string
     */
    protected $type = 'pdf';

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
