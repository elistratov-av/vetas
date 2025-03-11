<?php

use app\commands\migrate\Migration;

/**
 * Class m181012_070913_fix_mosu_vists_slot_range
 */
class m181012_070913_fix_mosu_vists_slot_range extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $visits = \app\modules\soap\models\Visits::find()
            ->joinWith('etpMessage')
            ->where(['time_range' => 'empty'])
            ->andWhere(new \yii\db\conditions\InCondition('visits.status', 'NOT IN', [
                \app\common\models\VisitStatus::CANCELED,
                \app\common\models\VisitStatus::IN_WORK,
                \app\common\models\VisitStatus::FINISHED,
                \app\common\models\VisitStatus::TIMEOUT,
            ]))
            ->all()
        ;

        $command = $this->db->createCommand("update visits set duration = :duration, cooldown = :cooldown
            where id = :id");
        foreach ($visits as $visit) {
            if ($services = $visit->services) {
                $duration = 0;
                $cooldown = 0;
                foreach ($services as $service) {
                    $duration += $service->duration;
                    $cooldown += $service->cooldown;
                }
                $visit->duration = $duration;
                $visit->cooldown = $cooldown;

                $command->bindValues([
                    'duration' => $duration,
                    'cooldown' => $cooldown,
                    'id' => $visit->id
                ]);
                print_r($command->rawSql . PHP_EOL);
                $command->execute();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
