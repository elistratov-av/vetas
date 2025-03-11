<?php

namespace app\common\components\wordGenerator;

use app\common\components\media\ResourceFileRepository;
use Yii;
use yii\web\NotFoundHttpException;
use yii\helpers\FileHelper;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\CopyFileException;
use app\modules\mdm\models\Pet;
use app\models\db\Pets;
use app\common\components\FileService;
use app\modules\v1\models\FileResource;
use yii\web\ServerErrorHttpException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use app\common\helpers\DateHelper;


class WordGenerator extends ResourceFileRepository
{
    /** Шаблон акта о смерти животного */
    const DEATH_TEMPLATE = 'death';

    /** @var string */
    private $templateDir;

    /** Формат файла */
    const FILE_FORMAT = 'docx';
    const PDF_FORMAT = 'pdf';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->path = Yii::$app->params['resources_media_dir'];
        $this->templateDir = Yii::getAlias('@app') . '/common/components/wordGenerator/template/';
    }

    /**
     * @param int|string $code шаблон документа
     * @param string      $fileName имя файла
     * @param array      $data значения
     * @param int|null     $id_pet 
     *
     * @return null|FileResource 
     * @throws \Exception
     * @throws NotFoundHttpException
     * @throws CreateTemporaryFileException
     * @throws CopyFileException
     */
    public function createDocument(string $code, string $fileName, array $data, int $id_pet=null)
    {
        try {
            $document = new TemplateProcessor($this->templateDir . $code . '.' . self::FILE_FORMAT);
            $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');

            foreach($data as $key => $val) {
                $document->setValue($key, $val);
            }
            
            $newdir = '/upload/word/' . Yii::$app->getSecurity()->generateRandomString(32) . '/';
            $dir = Yii::getAlias('@webroot') . $newdir;
            FileHelper::createDirectory($dir);

            $document->saveAs($dir . '/' . $fileName . '.' . self::FILE_FORMAT);

        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Ошибка при генерации документа ' . $fileName);
        }

        /** @var FileService $fileService */
        $fileService = Yii::$app->fileService;
        $hash = $fileService->generateHash($fileName);

        $fileResource = new FileResource();
        $fileResource->hash = $hash;
        $fileResource->path = $newdir . $fileName . '.' . self::FILE_FORMAT;
        $fileResource->name = $fileName . '.' . self::FILE_FORMAT;
        $fileResource->entity_id = $id_pet;
        $fileResource->entity_type = 'docx';

        return $fileResource;
    }
}
