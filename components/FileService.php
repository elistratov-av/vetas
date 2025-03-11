<?php

namespace app\common\components;

use app\common\components\media\MediaException;
use app\common\components\media\MediaRepositoryInterface;
use app\common\components\media\UploadFileRepository;
use app\modules\v1\models\FileResource;
use yii\base\Component;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class FileService extends Component
{
    /** @var MediaRepositoryInterface|UploadFileRepository  */
    public $repository;

    public $path;
    public $availableTypes;

    public $object;

    /**
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->repository = \Yii::createObject($this->repository);
        if (!$this->repository instanceof MediaRepositoryInterface) {
            throw new \Exception('`' . get_class($this) . '::repository` should be an instance of `' . MediaRepositoryInterface::class . '` or its DI compatible configuration.');
        }
    }

    public function getFileById($id)
    {
        if (!$file = FileResource::findOne(['hash' => $id])) {
            return null;
        }

        $filePath = $this->repository->getFilePath($file->path);
        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    public function getFileByHash($hash)
    {
        if (!$file = FileResource::findOne(['hash' => $hash])) {
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
     * @return FileResource
     * @throws MediaException
     */
    public function upload(UploadedFile $file)
    {
        $ext = $this->validateMime($file->type);
        $hash = $this->generateHash($file->tempName);
        $filePath = $this->repository->save($file->tempName, $hash, $ext);
        try {
            $fileResource = $this->saveFileData($hash, $filePath, $file->name);
        } catch (\Exception $e) {
            $this->repository->delete($filePath);
            throw new MediaException("Ошибка при сохранении файла: {$e->getMessage()}");
        }

        return $fileResource;
    }

    /**
     * @param string $mime
     * @return string
     * @throws MediaException
     */
    protected function validateMime(string $mime) : string
    {
        $extensions = FileHelper::getExtensionsByMimeType($mime);
        $res = array_intersect($extensions, $this->availableTypes);
        if (empty($extensions) || !$res) {
            throw new MediaException("Неподдерживаемый тип файла");
        }

        return array_shift($res);
    }

    /**
     * @param $hash
     * @param $path
     * @param $name
     * @return FileResource
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\IntegrityException
     */
    public function saveFileData($hash, $path, $name) : FileResource
    {
        $file = new FileResource();
        $file->hash = $hash;
        $file->path = $path;
        $file->name = $name;
        $file->save();

        return $file;
    }

    /**
     * @param string $name
     * @return string
     */
    public function generateHash(string $name) : string
    {
        return sha1($name . microtime());
    }

    /**
     * @param $type
     * @param $id
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
            'entity_type' => $type
        ]);
    }

    /**
     * @param FileResource $file
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\IntegrityException
     * @throws \yii\di\NotInstantiableException
     */
    public function attach(FileResource $file) : void
    {
        $resourceRepository = $this->getRepository($file->entity_type, $file->entity_id);

        $filePath = $this->repository->getFilePath($file->path);
        $path = $resourceRepository->save($filePath, $file->hash, $this->repository->getFileExtension($filePath));

        $file->path = $path;
        $file->save();
    }

    /**
     * @param $type
     * @param $id
     * @param $path
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function delete($type, $id, $path) : void
    {
        $resourceRepository = $this->getRepository($type, $id);
        $filePath = $this->repository->getFilePath($path);
        $resourceRepository->delete($filePath);
    }
}
