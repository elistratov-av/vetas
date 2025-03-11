<?php

namespace app\models\db;

use app\common\validators\FilterUcwordsValidator;
use app\common\validators\FioValidator;
use app\common\validators\FullTrimValidator;
use app\common\validators\UniqueValidator;
use app\modules\admin\models\Specialist;
use app\modules\admin\models\User;
use DateTime;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\DataReader;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "public.users".
 *
 * @property integer $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property string $auth_key
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $is_blocked
 * @property boolean $is_system_user пользователь для логирования изменений произведенных автоматически приложением
 * @property string $last_login
 * @property string $block_until
 * @property string $f_fio
 * @property string $i_fio
 * @property string $o_fio
 * @property string $fullname
 * @property string $sex
 * @property string $birthday
 * @property int $photo
 */
class Users extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login', 'f_fio', 'i_fio', 'o_fio', 'email'], 'filter', 'filter' => 'trim'],
            [['login', 'f_fio', 'i_fio', 'sex', 'birthday', 'email'], 'required'],
            [['login', 'email'], 'string', 'max' => 255],
            [['f_fio'], 'string', 'max' => 150],
            [['i_fio', 'o_fio'], 'string', 'max' => 50],
            [['f_fio', 'i_fio', 'o_fio'], FioValidator::class],
            [['f_fio', 'i_fio', 'o_fio'], FullTrimValidator::class],
            [['f_fio', 'i_fio', 'o_fio'], FilterUcwordsValidator::class],
            [['login'], UniqueValidator::class],
            [['birthday'], 'date', 'format' => 'php:Y-m-d'],
            [['is_system_user', 'is_blocked'], 'boolean'],
            [['password', 'auth_key', 'last_login', 'photo', 'fullname', 'block_until'], 'safe'],
            [[
                'created_by', 'updated_by', 'created_at',
                'updated_at', 'password_valid_till', 'password_valid_till_min'
            ], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Логин',
            'email' => 'Email',
            'password' => 'Пароль',
            'f_fio' => 'Фамилия',
            'i_fio' => 'Имя',
            'o_fio' => 'Отчество',
            'birthday' => 'Дата рождения',
            'sex' => 'Пол',
            'photo' => 'Фото',
            'fullname' => 'Ф.И.О.',
            'is_blocked' => 'Заблокирован постоянно',
            'block_until' => 'Заблокирован до',
            'last_login' => 'Последний визит',
            'auth_key' => 'Ключ шифрования JWT',
        ];
    }

    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function all(array $filter, int $limit, int $page): array
    {
        $query = self::find()
            ->select([
                'users.id',
                'users.login',
                'users.email',
                'users.fullname',
                'users.sex',
                'array_agg(organizations.id) AS organizationsId',
                'array_agg(organizations.name) AS organizationsName',
                'array_agg(organizations.short_name) AS organizationsShortName'
            ])
            ->leftJoin('specialists', 'specialists.id_user = users.id AND specialists.expel_date IS NULL')
            ->leftJoin('organizations', 'organizations.id = specialists.id_organization')
            ->orderBy('users.id ASC')
            ->groupBy('users.id');

        if (empty($filter) === false) {

            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'fullname':
                        $query->andFilterWhere(['ilike', 'users.fullname', mb_strtolower($value)]);
                        break;
                    case 'login':
                        $query->andFilterWhere(['ilike', 'users.login', mb_strtolower($value)]);
                        break;
                    case 'id':
                        $query->andFilterWhere(['users.id' => $value]);
                        break;
                    case 'email':
                        $query->andFilterWhere(['ilike', 'users.email', mb_strtolower($value)]);
                        break;
                }
            }
        }

        $totalCount = $query->count();

        return [
            'pages_count' => (int) (($totalCount + $limit - 1) / $limit),
            'total_count' => $totalCount,
            'users' => $query->limit($limit)
                ->offset($page * $limit - $limit)
                ->asArray()
                ->all(),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     */
    public function get(int $id): array
    {
        $user = User::findOne($id);

        if (empty($user)) {
            throw new BadRequestHttpException(
                sprintf('Нет пользователя с ID: %d', $id)
            );
        }

        return $user->toArray();
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function organization(int $id): array
    {
        $user = User::findOne($id);

        if (empty($user)) {
            throw new BadRequestHttpException(
                sprintf('Нет пользователя с ID: %d', $id)
            );
        }

        $organizations = [];
        foreach ($user->getSpecialist()->all() as $item) {

            $organization = $item->getOrganization()->one();

            $organizations[] = [
                'id' => $organization->id,
                'name' => $organization->name,
                'short_name' => $organization->short_name,
                'reg_date' => $item->reg_date,
                'roles' => $this->getOrganizationRoles($id, $organization->id)
            ];
        }

        return $organizations;
    }

    /**
     * @param int $id_user
     * @param int $id_organization
     * @return array|DataReader
     * @throws \yii\db\Exception
     */
    private function getOrganizationRoles(int $id_user, int $id_organization)
    {
        return \Yii::$app->db->createCommand('SELECT auth_assignment.item_name
                FROM auth_assignment
                    LEFT JOIN specialists s ON auth_assignment.id_user = s.id_user AND s.expel_date IS NULL
                    LEFT JOIN users u ON auth_assignment.id_user = u.id
                WHERE auth_assignment.id_user = :id_user 
                  AND s.id_organization = :id_organization 
                  AND auth_assignment.id_specialist = s.id 
                  ',
            [
                'id_user' => $id_user,
                'id_organization' => $id_organization,
            ]
        )->queryAll();
    }

    /**
     * @param array $data
     * @return bool
     * @throws BadRequestHttpException
     */
    public function addRole(array $data): bool
    {
        $specialist = Specialist::find()
            ->where(['id_user' => $data['id_user']])
            ->andWhere(['id_organization' => $data['id_organization']])
            ->one();

        if ($specialist instanceof Specialist === false) {
            $specialist = new Specialist();
            $specialist->reg_date = (new DateTime())->format('Y-m-d');
            $specialist->id_organization = $data['id_organization'];
            $specialist->id_user = $data['id_user'];
            $specialist->save();
        }

        if ($specialist->expel_date !== null) {
            throw new BadRequestHttpException('Пользователь уволен из организации');
        }

        try {
            \Yii::$app->db->createCommand()->insert('auth_assignment', [
                'item_name' => $data['item_name'],
                'id_user' => $data['id_user'],
                'id_specialist' => $specialist->id,
            ])->execute();
        } catch (Exception $e) {
            throw new BadRequestHttpException('Ошибка добавления роли у пользователя');
        }

        $this->markUserUpdated($data['id_user']);

        return true;
    }

    public function deleteRole(array $data): bool
    {
        $specialist = Specialist::find()
            ->where(['id_user' => $data['id_user']])
            ->andWhere(['id_organization' => $data['id_organization']])
            ->one();

        if ($specialist instanceof Specialist === false) {
            throw new BadRequestHttpException('Пользователь нет ролей в этой организации');
        }

        if ($specialist->expel_date !== null) {
            throw new BadRequestHttpException('Пользователь уволен из организации');
        }

        try {
            \Yii::$app->db->createCommand()->delete('auth_assignment', [
                'item_name' => $data['item_name'],
                'id_user' => $data['id_user'],
                'id_specialist' => $specialist->id,
            ])->execute();
        } catch (Exception $e) {
            throw new BadRequestHttpException('Ошибка удаления роли у пользователя');
        }

        $this->markUserUpdated($data['id_user']);

        return true;
    }

    /**
     * @param int $id
     * @return array
     */
    public function specialization(int $id): array
    {
        $query = Specializations::find()
            ->select([
                'specializations.*',
            ])
            ->leftJoin('users_specializations', 'users_specializations.id_specialization = specializations.id')
            ->leftJoin('users', 'users.id = users_specializations.id_user')
            ->where(['users.id' => $id])
            ->orderBy('specializations.id ASC');

        return $query->asArray()->all();
    }

    /**
     * @param array $data
     * @param int $userId
     * @return array
     * @throws BadRequestHttpException
     */
    public function addSpecialization(array $data, int $userId): array
    {
        if ($this->usersSpecializationsNotExist($data['id_user'], $data['id_specialization']) === false) {
            throw new BadRequestHttpException('У пользователя уже есть данная специализация');
        }

        $usersSpecializations = new UsersSpecializations();
        $usersSpecializations->id_user = $data['id_user'];
        $usersSpecializations->id_specialization = $data['id_specialization'];
        $usersSpecializations->created_by = $userId;

        if ($usersSpecializations->save() === false) {
            $errors = $usersSpecializations->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка добавления специализации у пользователя' : implode("\n", array_values($errors)));
        }

        $this->markUserUpdated($data['id_user']);

        return $usersSpecializations->toArray();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function deleteSpecialization(array $data): bool
    {
        UsersSpecializations::deleteAll(
            'id_user = :id_user AND id_specialization = :id_specialization',
            [
                'id_user' => $data['id_user'],
                'id_specialization' => $data['id_specialization'],
            ]
        );

        $this->markUserUpdated($data['id_user']);

        return true;
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function create(array $data): array
    {
        $user = new User() ;
        $user->attributes = $data;

        if ($user->validate()) {
            $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
            if (empty($keyString)) {
                throw new InvalidConfigException('You should specify passwordEncryptionKey');
            }
            $key = base64_decode($keyString);
            $user->password = base64_encode(\Yii::$app->security->encryptByKey(
                $data['password'], $key
            ));
        }

        if ($user->save() === false) {
            $errors = $user->getErrors();
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании пользователя' : json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return $user->toArray();
    }

    /**
     * @param int $id
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function updateById(int $id, array $data): array
    {
        $user = User::findOne($id);

        if (empty($user)) {
            throw new BadRequestHttpException(
                sprintf('Нет пользователя с ID: %d', $id)
            );
        }

        $user->attributes = $data;

        if ($user->update() === false) {
            $errors = $user->getErrors();
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при обновлении пользователя' : json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return $user->toArray();
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function regOrganization(array $data): array
    {
        if ($this->specialistNotExist($data['id_user'], $data['id_organization']) === false) {
            throw new BadRequestHttpException('У пользователя уже есть данная организация');
        }

        $specialist = new Specialist();
        $specialist->id_user = $data['id_user'];
        $specialist->id_organization = $data['id_organization'];
        $specialist->reg_date = (new DateTime())->format('Y-m-d');

        if ($specialist->save() === false) {
            $errors = $specialist->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка добавления оганизации у пользователя' : implode("\n", array_values($errors)));
        }

        $this->markUserUpdated($data['id_user']);

        return $specialist->toArray();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function expelOrganization(array $data): bool
    {
        Specialist::updateAll(
            ['expel_date' => (new DateTime())->format('Y-m-d')]
            , 'id_user = :id_user AND id_organization = :id_organization',
            [
                ':id_user' => $data['id_user'],
                ':id_organization' => $data['id_organization'],
            ]);

        $this->markUserUpdated($data['id_user']);

        return true;
    }

    /**
     * @param int $id_user
     * @param int $id_organization
     * @return bool
     */
    private function specialistNotExist(int $id_user, int $id_organization): bool
    {
        $specialistExist = Specialist::find()
            ->where(['id_user' => $id_user])
            ->andWhere(['id_organization' => $id_organization])
            ->andWhere(['expel_date' => null])
            ->one();

        if ($specialistExist instanceof Specialist === true) {
            return false;
        }

        return true;
    }

    /**
     * @param int $id_user
     * @param int $id_specialization
     * @return bool
     */
    private function usersSpecializationsNotExist(int $id_user, int $id_specialization): bool
    {
        $usersSpecializations = UsersSpecializations::find()
            ->where(['id_user' => $id_user])
            ->andWhere(['id_specialization' => $id_specialization])
            ->one();

        if ($usersSpecializations instanceof UsersSpecializations === true) {
            return false;
        }

        return true;
    }

    private function markUserUpdated($id_user)
    {
        \Yii::$app->db->createCommand()
            ->update('users',
                [
                    'updated_at' => date('Y-m-d H:i:s'),
                ], [
                    'id' => $id_user,
                ])
            ->execute();
    }

}
