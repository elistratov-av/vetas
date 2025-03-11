<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\console\ExitCode;

class CsvImportController extends Controller
{

    public function actionRun(string $filename) {
        ini_set("memory_limit","256M");

        if (!file_exists($filename)) {
            $this->stdout(sprintf("Файл %s не найден.\n", $filename));

            return ExitCode::DATAERR;
        }
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
            $this->stdout(sprintf("Файл должен быть csv.\n", $filename));

            return ExitCode::DATAERR;
        }
        
        $start = microtime(true);
        
        $fh = fopen($filename, 'r');        
        
        $db = Yii::$app->getDb();
        $transaction = $db->beginTransaction();
        
        try {
            
            // проверить округ
            $checkArea = Yii::$app->db->createCommand('SELECT id FROM areas WHERE "OkrugID" = :OkrugID');
            // создать округ
            $insertArea = Yii::$app->db->createCommand('INSERT INTO areas 
                ("name", "short_name", "area", "bti_code", "OkrugID") 
                VALUES (:name, \'\', 0, 0, :OkrugID)
            ');
            
            // проверить район
            $checkDistrict = Yii::$app->db->createCommand('SELECT id FROM districts WHERE "RayonID" = :RayonID');
            // создать район
            $insertDistrict = Yii::$app->db->createCommand('INSERT INTO districts 
                ("name", "bti_code", "id_area", "RayonID")
                VALUES (:name, 0, :id_area, :RayonID)
            ');
            
            $cache = ['areas' => [], 'districts' => []];
            $address_data = [];
            
            $row = 0;
            $queryCount = 0;
            while (($data = fgetcsv($fh, 4096, ';')) !== FALSE) {
                
                // пропустить первую строку с заголовками
                if ($row == 0) {
                    $row++;
                    continue;
                }
                // проверить округ
                $OkrugID = $data[5];
                if (!array_key_exists((string)$OkrugID, $cache['areas'])) {
                    $area = $checkArea->bindValue(':OkrugID', $OkrugID)->queryColumn();
                    $queryCount++;
                    
                    if (!$area) {
                        $insertArea->bindValues([
                            ':name' => $data[6],
                            ':OkrugID' => $OkrugID,
                        ])->execute();
                        $id_area = $db->getLastInsertID();
                        $queryCount++;
                    }
                    else {
                        $id_area = $area[0];
                    }
                    $cache['areas'][$OkrugID] = $id_area;
                }
                else {
                    $id_area = $cache['areas'][$OkrugID];
                }
                
                // проверить район
                $RayonID = $data[3];
                if (!array_key_exists((string)$RayonID, $cache['districts'])) {
                    $district = $checkDistrict->bindValue(':RayonID', $RayonID)->queryColumn();
                    $queryCount++;
                    
                    if (!$district) {
                        $insertDistrict->bindValues([
                            ':name' => $data[4],
                            ':id_area' => $id_area,
                            ':RayonID' => $RayonID,
                        ])->execute();
                        $id_district = $db->getLastInsertID();
                        $queryCount++;
                    }
                    else {
                        $id_district = $district[0];
                    }
                    $cache['districts'][$RayonID] = $id_district;
                }
                else {
                    $id_district = $cache['districts'][$RayonID];
                }
                
                // адрес
                $AdrID = $data[0];
                $address_data[] = [$data[1], '', '', $id_area, $id_district, $AdrID];
            
                $this->stdout("$row\n");
                
                $row++;
            }
            
            fclose($fh);
            
            if ($address_data) {
                
                $cmd = $db->createCommand()->batchInsert('addresses', 
                    ['name', 'latitude', 'longitude', 'id_area', 'id_district', 'AdrID'], $address_data);
                $db->createCommand($cmd->sql . ' ON CONFLICT ("AdrID") DO NOTHING')->execute();
                $queryCount++;
            }
            
            $transaction->commit();
            $this->stdout("Выполнено. query count: $queryCount. Время выполнения: " . round(microtime(true) - $start, 4) . " сек.\n");
            
        } catch(\Throwable $e) {
            $transaction->rollBack();
            $this->stdout($e);
        }

        return ExitCode::OK;
    }
    
}

