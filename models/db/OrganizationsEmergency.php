<?php

namespace app\models\db;

use yii\db\Expression;

/**
 * This is the model class for table "organizations_emergency".
 *
 * @property int $id
 * @property int $id_organization Ссылка на организацию
 * @property string $date_from Время начала экстренного случая
 * @property string $date_to Время окончания экстренного случая
 * @property string $reason Причина
 * @property int $count_live_queue_visits Количество измененных визитов из живой очереди
 * @property int $count_not_live_queue_visits Количество измененных визитов НЕ из живой очереди
 *
 * @property Organizations $organization
 */
class OrganizationsEmergency extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'organizations_emergency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization', 'date_from', 'date_to'], 'required'],
            [['id_organization', 'count_live_queue_visits', 'count_not_live_queue_visits'], 'integer'],
            [['count_live_queue_visits', 'count_not_live_queue_visits'], 'default', 'value' => 0],
            [['date_from', 'date_to'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['reason'], 'string'],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            ['date_from', function($attribute, $params, $validator) {
                $df = new \DateTime($this->date_from);
                $dt = new \DateTime($this->date_to);

                $diff = $dt->getTimestamp() - $df->getTimestamp();
                if ($diff <= 0) {
                    $this->addError($attribute, "Указан неверный промежуток времени (#1)");
                }

                // изначально задумывалось что перерыв будет 3 часа
                // возможно в дальнейшем эту проверку надо будет заменить, если появятся разные типы
                // экстренных ситуаций
                if ($diff > 3600*3) {
                    $this->addError($attribute, "Указан неверный промежуток времени (#2)");
                }

                // не позволяем создавать записи слишком давно в прошлом или будущем
                // разумный промежуток считаю за час до текущего времени или час после
                $now = new \DateTime();
                $diff = $now->getTimestamp() - $df->getTimestamp();
                if (abs($diff) > 3600) {
                    $this->addError($attribute, "Указан неверный промежуток времени (#3)");
                }
            }],
            ['date_from', function($attribute, $params, $validator) {
                $query = OrganizationsEmergency::find()
                    ->where(['id_organization' => $this->id_organization])
                    ->andWhere(new Expression("tsrange(date_from, date_to, '[)') && tsrange(:from, :to, '[)')", [
                        'from' => $this->date_from,
                        'to' => $this->date_to
                    ]));

                if (!$this->isNewRecord) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }

                if ($query->exists()) {
                    $this->addError($attribute, "В настоящий момент в организации уже зарегистрирована экстренная ситуация");
                }
            }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_organization' => 'Организация',
            'date_from' => 'Дата начала',
            'date_to' => 'Дата окончания',
            'reason' => 'Причина',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::className(), ['id' => 'id_organization']);
    }
}
