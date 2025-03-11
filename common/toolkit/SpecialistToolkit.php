<?php

namespace app\common\toolkit;

use app\models\db\Organizations;
use app\models\db\Shifts;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\Users;
use app\modules\v2\modules\user\models\LoginModel;
use yii\base\Exception;
use yii\console\widgets\Table;
use yii\db\Expression;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class SpecialistToolkit
{
    private $password = 123456;

    private $loginTemplate = 'testing';
    /**
     * @param $id_organization
     * @param int $limit
     * @throws Exception
     */
    public function add($id_organization, $limit = 2000)
    {
        $organization = $this->getOrganization($id_organization);
        $workShift = $this->getWorkShift($organization);
        $shifts = $this->getShifts($organization);
        for ($i = 1; $i <= $limit; $i++) {
            $this->addSpecialist($organization, $i, $workShift, $shifts);
        }
    }

    /**
     * @param $id_organization
     * @throws Exception
     */
    public function delete($id_organization)
    {
        $organization = $this->getOrganization($id_organization);
        /** @var Specialists[] $specialists */
        $specialists = Specialists::find()
            ->joinWith(['user'], false)
            ->where(['id_organization' => $organization->id])
            ->andWhere(['like', 'users.login', new Expression("'{$this->loginTemplate}%'")])
            ->all();

        foreach ($specialists as $specialist) {
            Console::output(Console::ansiFormat("{$specialist->fullname}:", [Console::FG_YELLOW, Console::BOLD]));
            $this->deleteAuthAssignment($specialist->id);
            $this->deleteTimeSheets($specialist->id);
            $this->deleteVisists($specialist->id);
            $this->deleteSpecialist($specialist->id);
            $this->deleteUser($specialist->id_user);
        }
    }

    /**
     * @param $id_organization
     * @throws Exception
     * @throws \Exception
     */
    public function loginUsers($id_organization)
    {
        $organization = $this->getOrganization($id_organization);

        $dir = \Yii::getAlias('@runtime/export');
        if (!is_dir($dir)) {
            throw new Exception("Директория {$dir} не существует");
        }

        if (!is_writable($dir)) {
            throw new Exception("Не возможна запись в директорию {$dir}");
        }

        $fileName = "{$dir}/login-tokens.csv";
        if (!$file = fopen($fileName, 'w')) {
            throw new Exception("Не удалось создать/открыть файл {$fileName} для записи");
        }

        /** @var Specialists[] $specialists */
        $specialists = Specialists::find()
            ->joinWith(['user'])
            ->where(['id_organization' => $organization->id])
            ->all();

        $count = count($specialists);
        Console::startProgress(0, $count);
        $c = 0;
        foreach ($specialists as $specialist) {
            $c++;
            $user = $specialist->user;
            $login = new LoginModel([
                'login' => $user->login,
                'password' => $this->password
            ]);
            $res = $login->login();
            $res = $login->selectOrganization($id_organization);

            fputcsv($file, [$user->login, $res['token']['token']], ';');
            Console::updateProgress($c, $count);
        }
        Console::endProgress();

        Console::output("Токены сохранены в файл {$fileName}");
    }

    /**
     * @param Organizations $organization
     * @param int $number
     * @param Shifts $workShift
     * @param Shifts[] $shifts
     * @throws Exception
     */
    protected function addSpecialist(
        Organizations $organization,
        int $number,
        Shifts $workShift,
        $shifts
    )
    {
        Console::output(Console::ansiFormat($number, [Console::FG_YELLOW]));
        $user = $this->createUser($number);
        $specialist = $this->createSpecialist($organization, $user);
        $this->createTimeSheet($specialist, $workShift, $shifts);
        $this->addAuthAssigments($user, $specialist);
        Console::output(PHP_EOL);
    }

    /**
     * @param int $number
     * @return Users
     * @throws Exception
     */
    protected function createUser(int $number)
    {
        $user = new Users([
            'login' => "{$this->loginTemplate}{$number}",
            'f_fio' => "Тестов{$number}",
            'i_fio' => "Тест{$number}",
            'o_fio' => "Тестович{$number}",
            'sex' => 'm',
            'birthday' => '1990-01-01',
            'password' => \Yii::$app->security->generatePasswordHash($this->password)
        ]);
        if (!$user->validate()) {
            throw new Exception('Ошибка добавления пользователя в БД:' . PHP_EOL . Console::errorSummary($user));
        }
        $user->save(false);
        Console::output(Console::ansiFormat("Создан пользователь {$user->f_fio} {$user->i_fio} {$user->o_fio}", [Console::FG_YELLOW]));
        return $user;
    }

    /**
     * @param Organizations $organization
     * @param Users $user
     * @return Specialists
     * @throws Exception
     */
    protected function createSpecialist(Organizations $organization, Users $user)
    {
        $specialist = new Specialists([
            'id_organization' => $organization->id,
            'id_user' => $user->id,
            'reg_date' => '2019-01-01'
        ]);

        if (!$specialist->validate()) {
            throw new Exception('Ошибка добавления специалиста в БД: ' . PHP_EOL . Console::errorSummary($specialist));
        }
        $specialist->save(false);
        Console::output(Console::ansiFormat("Добавлен специалист", [Console::FG_YELLOW]));
        return $specialist;
    }

    /**
     * @param Specialists $specialist
     * @param Shifts $workShift
     * @param Shifts[] $shifts
     * @throws Exception
     */
    protected function createTimeSheet(Specialists $specialist, Shifts $workShift, $shifts)
    {
        $today = new \DateTime();
        Console::output(Console::ansiFormat("Создание расписания", [Console::FG_YELLOW]));
        Console::startProgress(0, 14);
        for ($day = 0; $day <= 14; $day++) {
            $today->add(new \DateInterval("P1D"));
            $startDate = new \DateTime($today->format('Y-m-d') . ' ' . $workShift->from_time);
            $endDate = clone $startDate;
            $endDate = $endDate->add(new \DateInterval("PT{$workShift->duration}M"));

            $parentTimesheet = new Timesheets([
                'id_specialist' => $specialist->id,
                'id_shift' => $workShift->id,
                'date' => '["' . $startDate->format('Y-m-d H:i:s') . '", "' . $endDate->format('Y-m-d H:i:s') . '")'
            ]);

            if (!$parentTimesheet->validate()) {
                throw new Exception('Ошибка создания родительской рабочей смены: ' . PHP_EOL . Console::errorSummary($specialist));
            }

            $parentTimesheet->save();

            foreach ($shifts as $shift) {
                $startDate = new \DateTime($today->format('Y-m-d') . ' ' . $shift->from_time);
                $endDate = clone $startDate;
                $endDate = $endDate->add(new \DateInterval("PT{$shift->duration}M"));

                $timesheet = new Timesheets([
                    'id_specialist' => $specialist->id,
                    'id_shift' => $shift->id,
                    'parent_id' => $parentTimesheet->id,
                    'date' => '["' . $startDate->format('Y-m-d H:i:s') . '", "' . $endDate->format('Y-m-d H:i:s') . '")'
                ]);

                if (!$timesheet->validate()) {
                    throw new Exception('Ошибка создания рабочей смены: ' . PHP_EOL . Console::errorSummary($specialist));
                }

                $timesheet->save();
            }

            Console::updateProgress($day, 14);
        }

        Console::endProgress(Console::ansiFormat("done", [Console::FG_YELLOW]));
    }

    /**
     * @param Users $user
     * @param Specialists $specialist
     * @throws \yii\db\Exception
     */
    protected function addAuthAssigments(Users $user, Specialists $specialist)
    {
        $sql = <<<SQL
INSERT INTO auth_assignment(item_name, created_at, id_user, id_specialist)
VALUES (:item_name, :created_at, :id_user, :id_specialist);
SQL;

        \Yii::$app->db->createCommand($sql, [
            'item_name' => 'vetSpecGos',
            'created_at' => time(),
            'id_user' => $user->id,
            'id_specialist' => $specialist->id
        ])->execute();

        \Yii::$app->db->createCommand($sql, [
            'item_name' => 'registryGos',
            'created_at' => time(),
            'id_user' => $user->id,
            'id_specialist' => $specialist->id
        ])->execute();
    }

    /**
     * @param $id
     * @return Organizations
     * @throws Exception
     */
    protected function getOrganization($id)
    {
        if (!$organization = Organizations::findOne(['id' => $id])) {
            throw new Exception('Организация не найдена');
        }

        return $organization;
    }

    /**
     * @param Organizations $organization
     * @return Shifts
     * @throws Exception
     */
    protected function getWorkShift(Organizations $organization)
    {
        if (!$workShift = Shifts::findOne(['id_organization' => $organization->id, 'id_type' => 1])) {
            throw new Exception('Рабочая смена не найдена');
        }

        return $workShift;
    }

    /**
     * @param Organizations $organization
     * @return Shifts[]
     * @throws Exception
     */
    protected function getShifts(Organizations $organization)
    {
        $shifts = Shifts::find()
            ->where(['id_organization' => $organization->id])
            ->andWhere(['!=', 'id_type', 1])
            ->all();

        if (empty($shifts)) {
            throw new Exception('Рабочие смены не найдены');
        }

        return $shifts;
    }

    /**
     * @param $id_specialist
     * @throws \yii\db\Exception
     */
    protected function deleteTimeSheets($id_specialist)
    {
        Console::output(Console::ansiFormat("Удаление расписания", [Console::FG_YELLOW]));
        \Yii::$app->db->createCommand("delete from timesheets where id_specialist = :id_specialist", [
            ':id_specialist' => $id_specialist
        ])->execute();
    }

    /**
     * @param $id_specialist
     * @throws \yii\db\Exception
     */
    protected function deleteAuthAssignment($id_specialist)
    {
        Console::output(Console::ansiFormat("Удаление прав", [Console::FG_YELLOW]));
        \Yii::$app->db->createCommand("delete from auth_assignment where id_specialist = :id_specialist", [
            ':id_specialist' => $id_specialist
        ])->execute();
    }

    /**
     * @param $id_specialist
     * @throws \yii\db\Exception
     */
    protected function deleteVisists($id_specialist)
    {
        Console::output(Console::ansiFormat("Удаление приемов", [Console::FG_YELLOW]));
        \Yii::$app->db->createCommand(
        "delete from balance_flow where id_visitservice in(
                select id from visits_gov_services where id_visit in (
                    select id_visit from visits_specialists where id_specialist = :id_specialist
                )
            )",
            [
                ':id_specialist' => $id_specialist
            ])->execute();

        \Yii::$app->db->createCommand("update visits set author = null where author = :id_specialist", [
            ':id_specialist' => $id_specialist
        ])->execute();

        \Yii::$app->db->createCommand(
            "delete from visits where id in(select id_visit from visits_specialists where id_specialist = :id_specialist)",
            [
                ':id_specialist' => $id_specialist
            ])->execute();
    }

    /**
     * @param $id
     * @throws \yii\db\Exception
     */
    protected function deleteSpecialist($id)
    {
        Console::output(Console::ansiFormat("Удаление специалиста", [Console::FG_YELLOW]));
        \Yii::$app->db->createCommand("delete from specialists where id = :id", [
            ':id' => $id
        ])->execute();
    }

    /**
     * @param $id
     * @throws \yii\db\Exception
     */
    protected function deleteUser($id)
    {
        if (\Yii::$app->db->createCommand("select count(*) from specialists where id_user = {$id}")->queryScalar() > 0) {
            return;
        }
        Console::output(Console::ansiFormat("Удаление пользователя", [Console::FG_YELLOW]));
        \Yii::$app->db->createCommand("delete from users where id = :id", [
            ':id' => $id
        ])->execute();
    }
}
