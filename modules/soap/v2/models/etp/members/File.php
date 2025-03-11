<?php

namespace app\modules\soap\v2\models\etp\members;

use yii\base\Model;
use Yii;
use yii\web\UploadedFile;

/**
 * @property string Content
 * @property string FileName
 *
 * Class FactAddress
 * @package app\modules\soap\v2\models\etp\members
 */
class File extends Model
{
    const AVAILABLE_TYPES = [
        'image/png',
        'image/jpeg',
        'application/msword',
        'application/pdf',
    ];

    /**
     * 5 Mb in bytes
     */
    const FILE_SIZE_LIMIT = 5242880;

    /**
     * @var string
     */
    public $Content;

    /**
     * @var string;
     */
    public $FileName;

    /**
     * @var resource|null
     */
    private $tempFile;

    public function rules()
    {
        return [
            [['Content', 'FileName'], 'required'],
            ['Content', 'validateContent'],
            ['FileName', 'string'],
        ];
    }

    public function validateContent($attribute, $params, $validator)
    {
        if (!$this->getTempFile()) {
            $this->addError($attribute, 'Incorrect base64 format');
            return;
        }
        if (filesize($this->getUri()) > self::FILE_SIZE_LIMIT) {
            $this->addError($attribute, 'File exceeds filesize limit: ' . self::FILE_SIZE_LIMIT / 1024 / 1024 . ' Mb');
        }
        if (!in_array($this->getMimeType(), self::AVAILABLE_TYPES)) {
            $this->addError($attribute, 'Unavailable mimeType: ' . $this->getMimeType());
        }
    }

    public function getMimeType()
    {
        return mime_content_type($this->getUri());
    }

    public function getUri()
    {
        return stream_get_meta_data($this->getTempFile())['uri'];
    }

    /**
     * @return resource|null
     */
    private function getTempFile()
    {
        if (!$this->tempFile) {
            $file = base64_decode($this->Content);
            if (!$file) return null;

            $this->tempFile = tmpfile();
            file_put_contents(stream_get_meta_data($this->tempFile)['uri'], $file);
        }
        return $this->tempFile;
    }
}
