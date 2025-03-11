<?php

namespace app\commands;

use app\models\db\gost_diseases\GostDiseaseCategories;
use app\models\db\gost_diseases\GostDiseases;
use app\models\db\gost_diseases\GostDiseaseSubCategories;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Class GostDiseaseDictionaryFillDataCommand
 * @package app\commands
 *
 * > php yii gost-disease-dictionary/fill
 *
 * Парсинг документа в таблицы словарей ГОСТ болезней, классов и подклассов болезней
 */
class GostDiseaseDictionaryController extends Controller
{
    const PARSE_FILE_PATH = 'web/gostDiseasesImport/GostDiseases.xlsx';

    public function actionFill()
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(self::PARSE_FILE_PATH);

        $spreadsheet->getSheet(0);
        $sheet = $spreadsheet->getActiveSheet();
        $i = 2;
        while ($sheet->getCell('A'.$i)->getValue()) {
            $categoryName = trim($sheet->getCell('A'.$i)->getValue());
            if (!$category = GostDiseaseCategories::findOne(['name' => $categoryName])) {
                $category = new GostDiseaseCategories();
                $category->name = $categoryName;
                $category->save();
            }
            $subCategoryName = trim($sheet->getCell('B'.$i)->getValue());
            if (!$subCategory = GostDiseaseSubCategories::findOne(['name' => $subCategoryName])) {
                $subCategory = new GostDiseaseSubCategories();
                $subCategory->id_category = $category->id;
                $subCategory->name = $subCategoryName;
                $subCategory->save();
            }
            $diseaseName = trim($sheet->getCell('D'.$i)->getValue());
            $diseaseCode = trim($sheet->getCell('C'.$i)->getValue());
            if (GostDiseases::findOne(['name' => $diseaseName, 'gost_code' => $diseaseCode])) {
                $i++;
                continue;
            }
            $disease = new GostDiseases();
            $disease->id_sub_category = $subCategory->id;
            $disease->name = $diseaseName;
            $disease->gost_code = $diseaseCode;
            $disease->save();

            $i++;
        }

        return ExitCode::OK;
    }
}
