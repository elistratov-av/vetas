<?

namespace app\commands\cron;

use app\common\models\ExternalApiLogs;
use DateTime;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class LogCleanController extends Controller
{

    public function getHelp()
    {
        return 'Контроллер для очистки различных логов';
    }

    public function externalApiHelp()
    {
        return 'Очистка логов внешнего API';
    }

    public function actionExternalApi()
    {
        if (!ExternalApiLogs::deleteAll(['<', 'created_at', (new DateTime())->modify('-7 days')->format('Y-m-d H:i:s')])) {
            Console::error("Ошибка удаления логов, либо записей нет");
            return ExitCode::SOFTWARE;
        }
        Console::output("Логи в таблицей external_api_controllers за последние 7 дней удалены");
        return ExitCode::OK;
    }
}
