<?php

namespace app\common\components;

use app\common\components\media\MediaException;
use app\common\components\media\MediaRepositoryInterface;
use app\common\components\media\UploadFileRepository;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use finfo;
use yii\base\Component;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class FileService extends Component
{
    /** @var MediaRepositoryInterface|UploadFileRepository */
    public $repository;

    public $path;
    public $availableTypes;

    public $object;

    const LABORATORY_FILE_VISIT_DESCRIPTON = 88;

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
    public function upload(UploadedFile $file, $id_visit = null, $id_pet = null, $id_user = null, $user_fullname = null)
    {
        $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
        $mimeTypes = [
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ott'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp'  => 'application/vnd.oasis.opendocument.presentation',
            'otp'  => 'application/vnd.oasis.opendocument.presentation',
            'rtf'  => 'application/rtf',
            'txt'  => 'text/plain',
            'csv'  => 'text/csv',
            'zip'  => 'application/zip',
            'rar'  => 'application/vnd.rar',
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : $finfo->file($file->tempName);
        $ext = $this->validateMime($mimeType);
        $hash = $this->generateHash($file->tempName);
        $filePath = $this->repository->save($file->tempName, $hash, $ext);
        if ($id_visit) {
            try {
                $name = '№' . $id_pet . '_' . $file->name;
                $fileResource = $this->saveFileData($hash, $filePath, $name);
                $timestamp = date('Y-m-d H:i:s');
                if ($id_visit) \Yii::$app->db->createCommand("DELETE FROM public.visit_descriptions WHERE id_visit = $id_visit AND id_description_type IN (88, 95)")->execute();

                $insertLaboratoryFileToVisitDescription = \Yii::$app->db->createCommand('INSERT into public.visit_descriptions
                                                                (description, 
                                                                 id_visit,
                                                                 id_description_type,
                                                                 created_by,
                                                                 updated_by,
                                                                 created_at,
                                                                 updated_at,
                                                                 id_pet
                                                                 )
                                                                VALUES (:description,
                                                                        :id_visit,
                                                                        :id_description_type, 
                                                                        :created_by,
                                                                        :updated_by,
                                                                        :created_at,
                                                                        :updated_at,
                                                                        :id_pet
                                                                        )
                                                                        ');
                try {
                    $insertLaboratoryFileToVisitDescription->bindValues([
                        ':description' => $name,
                        ':id_visit' => (int)$id_visit,
                        ':id_description_type' => self::LABORATORY_FILE_VISIT_DESCRIPTON,
                        ':created_by' =>  $id_user,
                        ':updated_by' =>  $id_user,
                        ':created_at' =>  $timestamp,
                        ':updated_at' =>  $timestamp,
                        ':id_pet' => $id_pet,

                    ])->execute();
                    $secureUrl = str_replace('http://', 'https://', $fileResource->links['related']);
                    \Yii::$app->db->createCommand("INSERT INTO public.visit_descriptions (description, id_visit, id_description_type, created_by, updated_by, created_at, updated_at, id_pet) VALUES ('$secureUrl', $id_visit, 95, $id_user, $id_user, '$timestamp', '$timestamp', $id_pet)")->execute();
                    
                } catch (\Exception $e) {
                    file_put_contents('debug.txt', print_r($e->getMessage(), true), FILE_APPEND);
                }

            } catch (\Exception $e) {
                $this->repository->delete($filePath);
                throw new MediaException("Ошибка при сохранении файла: {$e->getMessage()}");
            }
        } else {
            try {
                $fileResource = $this->saveFileData($hash, $filePath, $file->name);
            } catch (\Exception $e) {
                $this->repository->delete($filePath);
                throw new MediaException("Ошибка при сохранении файла: {$e->getMessage()}");
            }
        }

        return $fileResource;
    }

    /**
     * @param string $mime
     * @return string
     * @throws MediaException
     */
    protected function validateMime(string $mime): string
    {
        $mimeMap = [
            'application/vnd.rar' => ['rar'],
            'application/x-rar' => ['rar']
        ]; 
        $extensions = isset($mimeMap[$mime]) ? $mimeMap[$mime] : FileHelper::getExtensionsByMimeType($mime);
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
    public function saveFileData($hash, $path, $name): FileResource
    {
//        $file = FileResource::findOne(['name' => $name]);
//        if (!$file){
        $file = new FileResource();
        $file->hash = $hash;
        $file->path = $path;
        $file->name = $name;
        $file->save();
//        }
        return $file;
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
    public function attach(FileResource $file): void
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
    public function delete($type, $id, $path): void
    {
        $resourceRepository = $this->getRepository($type, $id);
        $filePath = $this->repository->getFilePath($path);
        $resourceRepository->delete($filePath);
    }
}
