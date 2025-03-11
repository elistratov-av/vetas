<?php

namespace app\common\components\media;

use yii\base\Component;
use yii\helpers\FileHelper;

class UploadFileRepository extends Component implements MediaRepositoryInterface
{
    public $path;
    public $depth;

    /**
     * @param $hash
     * @return string
     */
    public function getPath(string $hash, string $ext) : string
    {
        $relPath = implode(DIRECTORY_SEPARATOR, array_slice(str_split($hash, 2), 0, $this->depth));
        return $this->path . DIRECTORY_SEPARATOR . $relPath . DIRECTORY_SEPARATOR . $hash . '.' . $ext;
    }

    /**
     * @param string $filePath
     * @return string
     */
    public function getFilePath(string $filePath) : string
    {
        $webroot = \Yii::getAlias('@webroot', false);
        if (!$webroot) $webroot = \Yii::getAlias('@app/web');
        return $webroot . FileHelper::normalizePath(\Yii::getAlias($filePath));
    }

    public function getFileExtension(string $filePath) : string
    {
        $info = pathinfo($filePath);
        return $info['extension'];
    }

    /**
     * Сохраняет загружаемый файл и возвращает путь относительно корневой директории
     * @param string $file Путь до файла
     * @param string $name Имя сохраняемого файла
     * @param string $ext Расширение сохраняемого файла
     * @return string
     * @throws MediaException
     */
    public function save(string $file, string $name, string $ext) : string
    {
        $filePath = $this->getPath($name, $ext);
        $savePath = $this->getFilePath($filePath);
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            try {
                FileHelper::createDirectory($dir);
            } catch (\Exception $e) {
                throw new MediaException("Не хватает прав на запись в директорию {$savePath}. {$e->getMessage()}");
            }
        }

        rename($file, $savePath);
        chmod($savePath, 0644);

        return $filePath;
    }

    /**
     * @param string $filePath
     */
    public function delete(string $filePath) : void
    {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}
