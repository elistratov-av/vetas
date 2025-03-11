<?php

namespace app\common\components\asurService;

use Psr\Http\Message\StreamInterface;
use yii\base\Component;
use yii\helpers\FileHelper;

/**
 * Class LocalDocumentStorage
 * @package app\common\components\asurService
 */
class LocalDocumentStorage extends Component
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    protected $storePath;
    /**
     * @var string
     */
    protected $uriPath;

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        $this->storePath = \Yii::getAlias('@upload' . $this->path);
        $this->uriPath = \Yii::getAlias('@webUpload' . $this->path);
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFileUri(string $fileName): string
    {
        return $this->uriPath . '/' . $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFilePath(string $fileName): string
    {
        return $this->storePath . '/' . $fileName;
    }

    /**
     * @param string $fileName
     * @param string $document
     * @return string
     * @throws \Exception
     */
    public function save(string $fileName, $document): string
    {
        if (!FileHelper::createDirectory($this->storePath)) {
            throw new \Exception("Не удалось создать директорию {$this->storePath}");
        }
        if (!is_writable($this->storePath)) {
            throw new \Exception("Нет прав на запись в директорию {$this->storePath}");
        }

        $filePath = $this->getFilePath($fileName);

        if ($document instanceof StreamInterface) {
            $fh = fopen($filePath, 'w');
            while (!$document->eof()) {
                $res = fwrite($fh, $document->read(1024), 1024);
                if ($res === false) {
                    throw new \Exception("Ошибка записи в файл {$filePath}");
                }
            }
            fclose($fh);
        } else {
            $res = file_put_contents($filePath, $document);
            if ($res === false) {
                throw new \Exception("Ошибка записи в файл {$filePath}");
            }
        }

        return $filePath;
    }

    /**
     * @param string $fileName
     * @throws \Exception
     */
    public function delete(string $fileName)
    {
        $filePath = $this->getFilePath($fileName);
        if (!is_file($filePath)) {
            throw new \Exception("Файл {$filePath} не существует");
        }
        if (!unlink($filePath)) {
            throw new \Exception("Не удалось удалить файл {$filePath}");
        }
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function isFile(string $fileName): bool
    {
        return is_file($this->getFilePath($fileName));
    }
}
