<?

namespace app\common\models;

use app\models\db\ActiveRecord;

class ExternalApiLogs extends ActiveRecord {
    
    public static function tableName()
    {
        return 'external_api_logs';
    }


    public function rules()
    {
        return [
            [['request_ip', 'request_url', 'request_type', 'request_headers', 'request_body', 'response_body'], 'required'],
            ['request_type', 'in', 'range' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']],
            [['request_ip', 'request_url', 'request_headers', 'request_body', 'response_body'], 'string'],
        ];
    }

}