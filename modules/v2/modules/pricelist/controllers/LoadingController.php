<?php

namespace app\modules\v2\modules\pricelist\controllers;

use app\modules\v2\modules\BaseController;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\pricelist\models\UploadForm;
use yii\web\UploadedFile;
use DateTime;

class LoadingController extends BaseController
{
    public function actionFlc($pricelist_id) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
		if (\Yii::$app->request->isPost) {
            $form = new UploadForm();
            $form->pricelist_id = $pricelist_id;
            $form->file = UploadedFile::getInstanceByName('file');

            if ($form->validate()) {
                $upload_path = \Yii::getAlias('@webroot/upload/pricelistloading/');
                if (!file_exists($upload_path)) { mkdir($upload_path, 0777, true); }
				$filename = \Yii::$app->getSecurity()->generateRandomString(32) . '.xlsx';
                $uploaded_file = $upload_path . $filename;
                $form->file->saveAs($uploaded_file);
				
				\Yii::$app->db->createCommand()->insert('admin.pricelist_loading', [
					'pricelist_id' => $pricelist_id,
					'user_id' => \Yii::$app->user->identity->getId(),
					'file_path' => $filename                    
				])->execute();
				
				$data = pg_escape_bytea(file_get_contents($uploaded_file));
				$func = \Yii::$app->db->createCommand("select admin.load_pricelist(".\Yii::$app->db->getlastinsertid().",'{".$data."}', 'flc') as c1");
				$funcres = $func->queryOne();
				
				return $funcres['c1'];
            } else {
                $errors = $form->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка загрузки файла' : implode("\n", array_values($errors)));
            }
        } else {
            return [
                'result' => false,
                'error' => 'POST expected',
            ];
        }
    }

    public function actionLoad($id) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
		if (!\Yii::$app->request->isPost) 
            return ['errors' => ['POST expected'],'status' => 'error'];

        $command = \Yii::$app->db->createCommand('select file_path FROM admin.pricelist_loading WHERE id=:id');
        $command->bindValue(':id', $id);
        $upload_path = \Yii::getAlias('@webroot/upload/pricelistloading/').$command->queryOne()['file_path'];

        if(!file_exists($upload_path)) 
            return ['errors' => ['Файл на севере не найден'],'status' => 'error'];

        $data = pg_escape_bytea(file_get_contents($upload_path));
        $func = \Yii::$app->db->createCommand("select admin.load_pricelist(".$id.",'{".$data."}', 'load') as c1");
        return $func->queryOne()['c1'];
    }

    public function actionLoadondate($id, $plan_load_date) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
		if (!\Yii::$app->request->isPost) 
            return ['errors' => ['POST expected'],'status' => 'error'];

        if(!$plan_load_date)
            return ['errors' => ['Необходимо указать дату планируемой загрузки'],'status' => 'error'];

        \Yii::$app->db->createCommand()->update(
            'admin.pricelist_loading', 
            ['status_loading' => 5, 'plan_load_date' => $plan_load_date],
            'id=:id',
            [':id' => $id]
    	)->execute();

        $logfile =\Yii::getAlias('@runtime/logs/cron.log');
        
        $temppath = \Yii::getAlias('@runtime/crontmp/');
        if (!file_exists($temppath)) { mkdir($temppath, 0777, true); }
		$filename = \Yii::$app->getSecurity()->generateRandomString(32);
        $cron_file = $temppath . $filename;

        $dt = new DateTime($plan_load_date);
            
        exec("crontab -l > {$cron_file} && [ -f {$cron_file} ] || > {$cron_file}");
        exec("echo '". $dt->format('i H d m *')." /usr/bin/php /var/www/html/yii pricelist/load {$id} \"".$dt->format('i H d m *')."\" >>".$logfile." 2>&1' >>".$cron_file);
        exec("crontab {$cron_file}");
        exec("rm {$cron_file}");
        
        return ['errors' => [], 'status' => 'ok' ];
    }

    public function actionCronlist(){
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        exec("crontab -l",$output);
        return $output;
    }

    public function actionCronclear($v){
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        if($v!="qjwegfyuqvxy65rfDDHgvq6t###22@") return "";
        exec("crontab -r",$output);
        return $output;
    }
}
