<?php

namespace app\commands;

use app\models\db\Brood;
use app\models\db\Species;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Class CheckBroodController
 * @package app\commands
 */
class CheckBroodController extends Controller
{
    /**
     * автоматическая деактивация выводков по времени
     * @return int
     */
    public function actionCheck()
    {
        $dogSpeciesId = $this->getDogSpeciesId();
        $catSpeciesId = $this->getCatSpeciesId();

        $dogDateFrom = (new \DateTime())
            ->modify('- ' . Brood::DOG_TIME_MONTHS . 'months')
            ->format('Y-m-d');

        $catDateFrom = (new \DateTime())
            ->modify('- ' . Brood::CAT_TIME_MONTHS . 'months')
            ->format('Y-m-d');

        /* @var $broods \app\models\db\Brood[] */
        $broods = Brood::find()
            ->where(['is_active' => true])
            ->andWhere([
                'or',
                [
                    'and',
                    ['id_species' => $dogSpeciesId],
                    ['<', 'birthday', $dogDateFrom]
                ],
                [
                    'and',
                    ['id_species' => $catSpeciesId],
                    ['<', 'birthday', $catDateFrom]
                ]
            ])
            ->all();

        if (empty($broods)) {
            Console::output('Выводков не найдено');

            return ExitCode::OK;
        }

        $success = 0;
        $errors = 0;

        foreach ($broods as $brood) {
            $brood->is_active = false;
            if (!$brood->save()) {
                $errors++;
            } else {
                $success++;
            }
        }

        Console::output('Выводков проверено:     ' . count($broods));
        Console::output('Выводков деактивировано:' . $success);
        Console::output('Ошибок при обновлении:  ' . $errors);

        return ExitCode::OK;
    }

    /**
     * @return int
     */
    private function getDogSpeciesId()
    {
        return Species::find()
            ->select('id')
            ->where(['tech_name' => Species::TECH_NAME_DOG])
            ->scalar();
    }

    /**
     * @return int
     */
    private function getCatSpeciesId()
    {
        return Species::find()
            ->select('id')
            ->where(['tech_name' => Species::TECH_NAME_CAT])
            ->scalar();
    }
}
