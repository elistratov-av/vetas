<?php

namespace app\models\db;

use app\common\models\UserModel;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "public.specialists".
 *
 * @property integer                              $id
 * @property string                               $reg_date
 * @property string                               $expel_date
 * @property integer                              $id_organization
 * @property integer                              $created_by
 * @property integer                              $updated_by
 * @property string                               $created_at
 * @property string                               $updated_at
 * @property integer                              $id_user
 *
 * Virtual properties (kept for BC, retrieved from user object):
 * @property string                               $f_fio
 * @property string                               $i_fio
 * @property string                               $o_fio
 * @property string                               $fullname
 * @property string                               $birthday
 * @property string                               $sex
 * @property integer                              $photo
 *
 * @property Organizations                        $organization
 * @property UserModel                            $user
 * @property Visits[]                             $visits
 * @property array                                $allOrganizations
 * @property VisitsSpecialists[]                  $visitsSpecialists
 * @property-read VaccinationStation[]            $vaccinationStations
 * @property-read Specialist2VaccinationStation[] $specialist2VaccinationStation
 */
class Specialists extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.specialists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['reg_date', 'required'],
            [['reg_date', 'expel_date', 'created_at', 'updated_at'], 'safe'],
            [['id_organization', 'created_by', 'updated_by', 'id_user'], 'integer'],
            [
                ['id_organization'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Organizations::class,
                'targetAttribute' => ['id_organization' => 'id']
            ],
            [
                ['id_user'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::class,
                'targetAttribute' => ['id_user' => 'id']
            ],
            [
                'expel_date',
                'validateExpel',
            ]
        ];
    }

    public function validateExpel($attribute, $params, $validator)
    {
        if (!$this->isAttributeChanged('expel_date') || empty($this->expel_date)) {
            return true;
        }

        if ($this->balanceNotEmpty()) {
            $this->addError($attribute, 'У пользователя на балансе остались ТМЦ. Передайте их или спишите перед увольнением');
            return false;
        }
        return true;
    }

    /**
     * Проверяет баланс пользователя
     * Используется при увольнении и отзыве некоторых ролей
     * (см app\common\components\rbac\DbManager revoke и revokeAll)
     *
     * @return bool TRUE если на балансе что то осталось
     */
    public function balanceNotEmpty()
    {
        return (new Query())
            ->from('tmc.balance')
            ->where([
                'AND',
                ['id_organization' => $this->id_organization],
                ['id_specialist' => $this->id],
                ['>', 'count', 0],
            ])
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reg_date' => 'Дата приема',
            'expel_date' => 'Дата увольнения',
            'id_organization' => 'ID организации',
            'id_user' => 'ID пользователя',
            'f_fio' => 'Фамилия',
            'i_fio' => 'Имя',
            'o_fio' => 'Отчество',
            'birthday' => 'Дата рождения',
            'sex' => 'Пол',
            'photo' => 'Фото',
            'fullname' => 'Ф.И.О.',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }

    /**
     * @param bool $excludeShelters
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getAllOrganizations($excludeShelters = false)
    {
        if (empty($this->id_organization)) {
            return [];
        }

        $sql = <<<SQL
WITH RECURSIVE r AS (
  SELECT o.id, o.parent_id, o.name, o.short_name, o.inn, o.kpp, o.ogrn, o.reg_number, o.id_fias_address, o.id_org_type
  FROM organizations o
  WHERE o.id = :id_organization
  UNION ALL
  SELECT o.id, o.parent_id, o.name, o.short_name, o.inn, o.kpp, o.ogrn, o.reg_number, o.id_fias_address, o.id_org_type
  FROM organizations o
         JOIN r
              ON o.parent_id = r.id
)
SELECT r.* FROM r
SQL;

        if ($excludeShelters === true) {
            $sql .= <<<SQL
    LEFT JOIN org_types ot ON r.id_org_type = ot.id
WHERE (ot.is_tech = false OR ot.is_tech ISNULL)
SQL;
        }

        return Yii::$app->db
            ->createCommand($sql, [':id_organization' => $this->id_organization])
            ->queryAll();
    }

    /**
     * Проверка, является ли специалист уволенным на определенную дату (если не передана - то на текущую)
     *
     * @param string $date Строка даты в формате Y-m-d
     *
     * @return bool
     * @throws \Exception
     */
    public function isExpelledAtDate($date = null)
    {
        return static::isExpelledAt($this->expel_date, $date);
    }

    /**
     * Проверка, является ли специалист уволенным на определенную дату (если не передана - то на текущую)
     *
     * @param string $expel_date Строка даты в формате Y-m-d
     * @param string $date       Строка даты в формате Y-m-d
     *
     * @return string
     */
    public static function isExpelledAt($expel_date, $date = null)
    {
        if (empty($expel_date)) {
            return false;
        }

        // принудительно добавляем часы:минуты:секунды во избежание глюков при сравнении текущей даты
        $dateAt = date_create_from_format('Y-m-d H:i:s', (isset($date) ? $date : date('Y-m-d')) . ' 23:59:59');
        if ($dateAt === false) {
            throw new InvalidArgumentException('Некорретный формат даты');
        }

        $dateExpel = date_create_from_format('Y-m-d H:i:s', $expel_date . ' 23:59:59');

        // считаем, что работник еще работает в день увольнения (других разъяснений от аналитиков пока не было)
        // статья 84.1 ТК РФ
        // Днем прекращения трудового договора во всех случаях является последний день работы работника
        // http://www.consultant.ru/document/cons_doc_LAW_34683/cc8071b6b37792778d4ce9fba7e76c373edc0618/

        return $dateAt > $dateExpel;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserModel::class, ['id' => 'id_user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this->hasMany(Visits::class, ['id' => 'id_visit'])
            ->viaTable('visits_specialists', ['id_specialist' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsSpecialists()
    {
        return $this->hasOne(VisitsSpecialists::class, ['id_specialist' => 'id']);
    }

    /**
     * @return array
     */
    public static function personalAttributes()
    {
        return [
            'f_fio',
            'i_fio',
            'o_fio',
            'fullname',
            'birthday',
            'sex',
            'photo',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $attributes = self::personalAttributes();

        return array_merge($attributes, parent::extraFields());
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $expand = array_merge($this->extraFields(), $expand);

        $result = parent::toArray($fields, $expand, $recursive);

        if (!isset($expand['user']) && array_key_exists('user', $result)) {
            unset($result['user']);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getF_fio()
    {
        return $this->getUserAttribute('f_fio');
    }

    /**
     * @return string
     */
    public function getI_fio()
    {
        return $this->getUserAttribute('i_fio');
    }

    /**
     * @return string
     */
    public function getO_fio()
    {
        return $this->getUserAttribute('o_fio');
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->getUserAttribute('fullname');
    }

    /**
     * @return string|null
     */
    public function getFullnameInitials()
    {
        $fullName = $this->getUserAttribute('fullname');
        if (!$fullName) {
            return;
        }

        $first = true;
        $result = [];
        foreach (explode(' ', $fullName) as $item) {
            if ($first) {
                $result[] = $item . ' ';
            } else {
                $result[] = mb_substr(trim($item), 0, 1) . '.';
            }

            $first = false;
        }

        return $result ? implode('', $result) : null;
    }

    /**
     * @return string
     */
    public function getBirthday()
    {
        return $this->getUserAttribute('birthday');
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return $this->getUserAttribute('sex');
    }

    /**
     * @return int
     */
    public function getPhoto()
    {
        return $this->getUserAttribute('photo');
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    private function getUserAttribute($name)
    {
        return ($this->user === null) ? null : $this->user->getAttribute($name);
    }

    public function getVaccinationStations(): ActiveQuery
    {
        return $this->hasMany(VaccinationStation::class, ['id' => 'vaccination_station_id'])
            ->viaTable('specialist2vaccination_station', ['specialist_id' => 'id']);
    }

    public function getSpecialist2VaccinationStation(): ActiveQuery
    {
        return $this->hasMany(Specialist2VaccinationStation::class, ['specialist_id' => 'id']);
    }
}
