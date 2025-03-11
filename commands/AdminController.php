<?php

namespace app\commands;

use app\common\validators\UniqueValidator;
use app\models\db\admin\AdminUser;
use app\modules\adminfstek\traits\PasswordTrait;
use yii\base\DynamicModel;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Class AdminController
 * @package app\commands
 */
class AdminController extends Controller
{
    use PasswordTrait;

    /**
     * Создание нового привилегированного пользователя из консоли:
     * ```
     *   php yii admin/create
     * ```
     *
     * @return int
     */
    public function actionCreate()
    {
        \Yii::$app->language = 'ru-RU';

        if (!$this->confirm('Создать нового привилегированного пользователя?')) {
            return ExitCode::OK;
        }

        $options = AdminUser::roleOptions();
        $options[':q'] = 'отмена';
        $prompt = 'Выберите роль' . PHP_EOL;
        foreach ($options as $key => $option) {
            $prompt .= $key;
            $prompt .= ' - ';
            $prompt .= $option;
            $prompt .= PHP_EOL;
        }
        $role = $this->select($prompt, $options);

        if ($role == ':q') {
            return ExitCode::OK;
        }

        $login = Console::input('Введите логин: ' . PHP_EOL);

        if ($login == ':q') {
            return ExitCode::OK;
        }

        $model = $this->validateLogin($login);

        while ($model->hasErrors()) {
            Console::output(Console::ansiFormat(implode(PHP_EOL, $model->getErrorSummary(true)), [Console::FG_RED]));
            $login = Console::input('Введите логин: ' . PHP_EOL);

            if ($login == ':q') {
                return ExitCode::OK;
            }

            $model = $this->validateLogin($login);
        }

        unset($model);

        $email = Console::input('Введите email: ' . PHP_EOL);

        if ($email == ':q') {
            return ExitCode::OK;
        }

        $model = $this->validateEmail($email);

        while ($model->hasErrors()) {
            Console::output(Console::ansiFormat(implode(PHP_EOL, $model->getErrorSummary(true)), [Console::FG_RED]));
            $email = Console::input('Введите email: ' . PHP_EOL);

            if ($email == ':q') {
                return ExitCode::OK;
            }

            $model = $this->validateEmail($email);
        }

        unset($model);

        foreach (['f_fio', 'i_fio', 'o_fio'] as $attribute) {
            $$attribute = Console::input('Введите "' . $attribute . '": ' . PHP_EOL);

            if ($$attribute == ':q') {
                return ExitCode::OK;
            }

            $model = $this->validateFio($attribute, $$attribute);

            while ($model->hasErrors()) {
                Console::output(Console::ansiFormat(implode(PHP_EOL, $model->getErrorSummary(true)), [Console::FG_RED]));
                $$attribute = Console::input('Введите "' . $attribute . '": ' . PHP_EOL);

                if ($$attribute == ':q') {
                    return ExitCode::OK;
                }

                $model = $this->validateFio($attribute, $$attribute);
            }

            unset($model);
        }

        $user = new AdminUser(compact('role', 'login', 'email', 'f_fio', 'i_fio', 'o_fio'));

        $password = $this->generatePassword();
        $user->setPassword($password, true);
        $user->setAuthKey();

        if (!$user->save()) {
            Console::output(Console::ansiFormat('Ошибка при сохранении пользователя' . PHP_EOL . implode(PHP_EOL, $user->getErrorSummary(true)), [Console::FG_RED]));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($this->sendPassword($user->email, $password)) {
            Console::output('Временный пароль отправлен на email пользователя');
        } else {
            Console::output(Console::ansiFormat('Ошибка при отправке временного пароля на email пользователя', [Console::FG_RED]));
        }

        return ExitCode::OK;
    }

    /**
     * @param string $login
     * @return \yii\base\DynamicModel
     */
    private function validateLogin(string $login): \yii\base\DynamicModel
    {
        $model = new DynamicModel(['login' => $login]);
        $model->addRule('login', 'required')
            ->addRule('login', 'filter', ['filter' => 'trim'])
            ->addRule('login', 'filter', ['filter' => 'strip_tags'])
            ->addRule('login', UniqueValidator::class, ['targetClass' => AdminUser::class, 'targetAttribute' => 'login'])
            ->addRule('login', 'string', ['min' => 2, 'max' => 255])
            ->validate();

        return $model;
    }

    /**
     * @param string $email
     * @return \yii\base\DynamicModel
     */
    private function validateEmail(string $email): \yii\base\DynamicModel
    {
        $model = new DynamicModel(['email' => $email]);
        $model->addRule('email', 'email')
            ->addRule('email', UniqueValidator::class, ['targetClass' => AdminUser::class, 'targetAttribute' => 'email'])
            ->validate();

        return $model;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return \yii\base\DynamicModel
     */
    private function validateFio(string $attribute, string $value): \yii\base\DynamicModel
    {
        $model = new DynamicModel([$attribute => $value]);
        $model->addRule($attribute, 'required')
            ->addRule($attribute, 'filter', ['filter' => 'trim'])
            ->addRule($attribute, 'filter', ['filter' => 'strip_tags'])
            ->addRule($attribute, 'string', ['min' => 2, 'max' => 255])
            ->validate();

        return $model;
    }
}
