<?php

namespace app\modules\v2\modules\files\components;

use app\common\components\media\MediaException;
use app\common\components\media\MediaRepositoryInterface;
use app\common\components\media\UploadFileRepository;
use app\models\db\Files;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class FileManager
{
    /**
     * @var MediaRepositoryInterface|UploadFileRepository
     */
    public $repository;
    /**
     * @var string
     */
    public $path;
    /**
     * @var array
     */
    public $availableTypes;

    /**
     * FilesModel constructor.
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function __construct()
    {
        $this->repository = \Yii::createObject([
            'class' => 'app\common\components\media\UploadFileRepository',
            'path' => '/upload/file',
            'depth' => 3,
        ]);

        $this->availableTypes = ['pdf', 'xls', 'xlsx', 'doc', 'docx', 'txt', 'jpg', 'png', 'zip', 'rar'];
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getFileById($id)
    {
        if (!$file = Files::findOne(['id' => $id])) {
            return null;
        }

        $filePath = $this->repository->getFilePath($file->path);
        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * @param string $hash
     * @return string|null
     */
    public function getFileByHash($hash)
    {
        if (!$file = Files::findOne(['hash' => $hash])) {
            return null;
        }

        $filePath = $this->repository->getFilePath($file->path);
        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * @param UploadedFile $file
     * @return Files
     * @throws MediaException
     */
    public function upload(UploadedFile $file)
    {
        $ext = $this->validateMime($file->type);
        $hash = $this->generateHash($file->tempName);
        $filePath = $this->repository->save($file->tempName, $hash, $ext);
        try {
            $Files = $this->saveFileData($hash, $filePath, $file->name);
        } catch (\Exception $e) {
            $this->repository->delete($filePath);
            throw new MediaException("Ошибка при сохранении файла: {$e->getMessage()}");
        }

        return $Files;
    }

    /**
     * @param string $mime
     * @return string
     * @throws MediaException
     */
    protected function validateMime(string $mime): string
    {
        $extensions = FileHelper::getExtensionsByMimeType($mime);
        $res = array_intersect($extensions, $this->availableTypes);
        if (empty($extensions) || !$res) {
            throw new MediaException("Неподдерживаемый тип файла");
        }

        return array_shift($res);
    }

    /**
     * @param string $name
     * @return string
     */
    public function generateHash(string $name): string
    {
        return sha1($name . microtime());
    }

    /**
     * @param $hash
     * @param $path
     * @param $name
     * @return Files
     */
    public function saveFileData($hash, $path, $name): Files
    {
        $file = new Files();
        $file->hash = $hash;
        $file->path = $path;
        $file->name = $name;
        $file->save();

        return $file;
    }

    /**
     * @param Files $file
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function attach(Files $file): void
    {
        $resourceRepository = $this->getRepository($file->entity_type, $file->entity_id);

        $filePath = $this->repository->getFilePath($file->path);
        $path = $resourceRepository->save($filePath, $file->hash, $this->repository->getFileExtension($filePath));

        $file->path = $path;
        $file->save();
    }

    /**
     * @param string $type
     * @param int $id
     * @return MediaRepositoryInterface|object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getRepository($type, $id)
    {
        /** @var MediaRepositoryInterface $resourceRepository */
        return \Yii::$container->get('resourceFileRepository', [$id], [
            'path' => \Yii::$app->params['resources_media_dir'],
            'entity_id' => $id,
            'entity_type' => $type,
        ]);
    }

    /**
     * @param Files $file
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\base\InvalidConfigException
     */
    public function delete(Files $file): void
    {
        $resourceRepository = $this->getRepository($file->entity_type, $file->entity_id);
        $filePath = $this->repository->getFilePath($file->path);
        $resourceRepository->delete($filePath);
    }
}
