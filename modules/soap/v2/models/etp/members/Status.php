<?php

namespace app\modules\soap\v2\models\etp\members;

use yii\base\Model;

/**
 * Class Status
 * @package app\modules\soap\v2\models\etp\members
 */
class Status extends Model
{
    /**
     * @var string
     */
    public $StatusCode;
    /**
     * @var string
     */
    public $StatusTitle;
    /**
     * @var string
     */
    public $StatusDate;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['StatusCode', 'required'],
            [['StatusTitle', 'StatusDate'], 'string'],
        ];
    }
}
