<?

namespace app\modules\v3\modules\externalServices\models;

use app\modules\v1\models\FileResource;
use GuzzleHttp\Client;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

use function GuzzleHttp\Promise\settle;

/**
 * Модель Ched.
 *
 * Эта модель предназначена для работы с сервисом ЦХЭД и обработки файлов.
 *
 * @package app\modules\v3\modules\externalServices\controllers
 */

class Ched extends FileResource
{

    public static function tableName()
    {
        return 'files'; // Замените на реальное имя вашей таблицы
    }

    protected $alias = 'files';

    protected $excludedFields = ['id', 'hash', 'created_by', 'updated_by', 'created_at', 'updated_at'];

    /**
     * Сохраняет файл по его ID.
     *
     * Метод выполняет запрос к удаленному серверу для получения содержимого файла по его идентификатору.
     *
     * @param string[] $ids Идентификаторы файлов.
     * @return array|bool Возвращает массив с данными файлов или false в случае ошибки.
     */
    public static function saveFiles(array $ids = [])
    {
        $client = new Client();
        $promises = [];

        foreach ($ids as $id) {
            $url = "{$_ENV['CHED_URL']}/universal-form/uform3.0/service/getcontent?os=GU_DOCS&id=$id";
            $promises[$id] = $client->getAsync($url);
        }
        $responses = settle($promises)->wait();
        $files = [];
        foreach ($responses as $id => $response) {
            if ($response['state'] !== 'fulfilled' || $response['value']->getStatusCode() !== 200) return false;
            $files[$id] = [
                'content' => $response['value']->getBody()->getContents(),
                'filename' =>  self::getFilenameFromDisposition($response['value']->getHeaderLine('Content-Disposition'))
            ];
            if (!$files[$id]) return false;
        }
        $resources = [];
        foreach ($files as $id => $fileData) {
            $fileService = Yii::$app->fileService;
            $tempFilePath = Yii::getAlias('@runtime') . '/' . uniqid('upload_', true);
            file_put_contents($tempFilePath, $fileData['content']);
            $mimeType = mime_content_type($tempFilePath);
            $type = FileHelper::getExtensionsByMimeType($mimeType . "dsfsdfsdf");
            $type = sizeof($type) ? $type[0] : 'tmp';
            $filename = $fileData['filename'] ?? "$id.$type";
            $uploadedFile = new UploadedFile([
                'name' => $filename,
                'tempName' => $tempFilePath,
                'type' => $mimeType,
                'size' => filesize($tempFilePath),
                'error' => UPLOAD_ERR_OK,
            ]);
            $resource = $fileService->upload($uploadedFile);
            $resource->entity_id = null;
            $resource->entity_type = 'visit';
            if (!$resource->save()) return false;
            $resources[] = $resource;
        }
        return $resources;
    }

    /**
     * Извлекает имя файла из заголовка Content-Disposition.
     *
     * @param string $contentDisposition Заголовок Content-Disposition.
     * @return string|null Имя файла или null, если имя не удалось извлечь.
     */
    private static function getFilenameFromDisposition(string $contentDisposition)
    {
        
        if (preg_match("/(?<=')[^']*\..*/", $contentDisposition, $matches)) {
            return urldecode($matches[0]);
        }
        return null;
    }
}
