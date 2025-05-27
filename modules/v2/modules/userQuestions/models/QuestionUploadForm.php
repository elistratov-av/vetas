<?php

namespace app\modules\v2\modules\userQuestions\models;

use yii\base\Model;

/**
 * UploadForm is the model behind the upload form.
 */
class QuestionUploadForm extends Model
{
    /**
     * @var string question attribute
     */
    public $question;

    /**
     * @var UploadedFile file attribute
     */
    public $file;
	
    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['question'], 'required'],
            [['question'], 'string'],
            [['file'], 'file',
                'skipOnEmpty' => true,
                'maxSize' => 10485760, // 10Mb
                'tooBig' => 'Limit is 10 Mb'
            ]
        ];
    }
}