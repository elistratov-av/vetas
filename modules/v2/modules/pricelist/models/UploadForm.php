<?php

namespace app\modules\v2\modules\pricelist\models;

use yii\base\Model;

/**
 * UploadForm is the model behind the upload form.
 */
class UploadForm extends Model
{
    /**
     * @var int id attribute
     */
    public $pricelist_id;

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
            [['pricelist_id', 'file'], 'required'],
            [['pricelist_id'], 'integer'],
            [['file'], 'file',
                'skipOnEmpty' => false,
                'checkExtensionByMimeType' => false,
                'extensions' => ['xlsx'],
//                'mimeTypes' => 'application/xlsx',
                'maxSize' => 104857600, // 100Mb
                'tooBig' => 'Limit is 100 Mb'
            ],
        ];
    }
}
