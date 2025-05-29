<?php

namespace app\commands\cron;

use DateTime;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class PricelistController extends Controller
{

    public function getHelp()
    {
        return 'Контроллер для загрузки прайс листов';
    }

    public function LoadHelp()
    {
        return 'Загруска прайс листа по расписанию';
    }

    public function actionLoad($id, $date)
    {
        $logfile =\Yii::getAlias('@runtime/logs/cron.log');
        
        $temppath = \Yii::getAlias('@runtime/crontmp/');
		$filename = \Yii::$app->getSecurity()->generateRandomString(32);
        $cron_file = $temppath . $filename;
        $basedir = \Yii::getAlias('@app');
        $task = "{$date} php {$basedir}/yii pricelist/load {$id} \"{$date}\" >>".$logfile." 2>&1";       

        exec("crontab -l > {$cron_file} && [ -f {$cron_file} ] || > {$cron_file}");
        $cron_array = file($cron_file, FILE_IGNORE_NEW_LINES);
        $original_count = count($cron_array);
        $cron_array = array_diff($cron_array,[$task]);
        if(!($original_count === count($cron_array))){
            exec("crontab -r");
            if(count($cron_array)>0){
                $original_count = file_put_contents($cron_file,implode("\n", $cron_array)."\n");
                if($original_count>2){
                    exec("crontab {$cron_file}"); //install cron
                }
                else{
                    Console::output(date('d-m-Y H:i:s')." ERROR: Ошибка записи списка задач: ".$task);
                    exec("rm {$cron_file}");
                    return ExitCode::OK;    
                }
            }
        }
        else{
            Console::output(date('d-m-Y H:i:s')." ERROR: Не найдена CRON задача: ".$task);
            exec("rm {$cron_file}");
            return ExitCode::OK;
        } 
        exec("rm {$cron_file}");

        // Основная задача
        $command = \Yii::$app->db->createCommand('select file_path FROM admin.pricelist_loading WHERE id=:id');
        $command->bindValue(':id', $id);
        $upload_path = "{$basedir}/web/upload/pricelistloading/".$command->queryOne()['file_path'];

        if(!file_exists($upload_path)){
            Console::output(date('d-m-Y H:i:s')." ERROR: Файл на севере не найден");
            return ExitCode::OK;
        }

        $data = pg_escape_bytea(file_get_contents($upload_path));
        $func = \Yii::$app->db->createCommand("select admin.load_pricelist(".$id.",'{".$data."}', 'load') as c1");
        Console::output(date('d-m-Y H:i:s')." ".$func->queryOne()['c1']);

        Console::output(date('d-m-Y H:i:s')." OK: Задача выполнена: ".$task);
        return ExitCode::OK;
    }
}
