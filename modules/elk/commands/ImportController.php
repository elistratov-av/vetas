<?php

namespace app\modules\elk\commands;

use app\modules\elk\models\PetsHandler;
use app\modules\elk\types\Animal;
use app\modules\elk\types\Animals;
use app\modules\elk\types\Owner;
use yii\console\Controller;

class ImportController extends Controller
{
    public $defaultAction = 'load';

    /**
     * @throws \Throwable
     */
    public function actionLoad()
    {
        $file = __DIR__ . "/data/elk_data.csv";
        if (file_exists($file)) {
            $parseCsv = function ($data) {
                $row = str_getcsv($data, ';');
                $result = [
                    'SsoId' => trim($row[0]),
                    'LastName' => trim($row[1]),
                    'FirstName'  => trim($row[2]),
                    'MiddleName'  => trim($row[3]),
                    'Phone'  => trim($row[4]),
                    'Email'  => trim($row[5]),
                    'Snils'  => trim($row[6]),
                    'ID'  => trim($row[7]),
                    'SpeciesID'  => trim($row[8]),
                    'BreedID'  => trim($row[9]),
                    'NickName'  => trim($row[10]),
                    'Chip'  => trim($row[11]),
                    'BirthDate'  => trim($row[12]),
                    'Sex'  => trim($row[13])
                ];
                return $result;
            };

            $data = array_map($parseCsv, file($file));

            foreach ($data as $item) {
                $owner = new Owner([
                    'SsoId' => $item['SsoId'],
                    'LastName' => $item['LastName'],
                    'FirstName' => $item['FirstName'],
                    'MiddleName' => $item['MiddleName'],
                    'Phone' => $item['Phone'],
                    'Email' => $item['Email'],
                    'Snils' => $item['Snils']
                ]);

                $animal = new Animal([
                    'ID' => $item['ID'],
                    'SpeciesID' => $item['SpeciesID'],
                    'BreedID' => $item['BreedID'],
                    'NickName' => $item['NickName'],
                    'Chip' => $item['Chip'],
                    'BirthDate' => $item['BirthDate'],
                    'Sex' => $item['Sex']
                ]);

                $animals = new Animals(['Animal' => $animal]);
                PetsHandler::save($owner, $animals);
            }
        }
    }

}
