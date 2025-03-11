<?php

namespace app\modules\v2\modules\pethotels\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * UploadForm is the model behind the upload form.
 */
class ContractUploadForm extends Model
{
    /**
     * @var int id attribute
     */
    public $id;

    /**
     * @var UploadedFile file attribute
     */
    public $file;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['id', 'file'], 'required'],
            [['id'], 'integer'],
            [['file'], 'file',
                'skipOnEmpty' => false,
                'checkExtensionByMimeType' => false,
                'extensions' => ['pdf'],
//                'mimeTypes' => 'application/pdf',
                'maxSize' => 10485760, // 10Mb
                'tooBig' => 'Limit is 10 Mb'
            ],
        ];
    }
}
