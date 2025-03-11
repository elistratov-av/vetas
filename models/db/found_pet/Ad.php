<?php

namespace app\models\db\found_pet;

use app\models\db\ActiveRecord;
use app\models\db\Breeds;
use app\models\db\Color;
use app\models\db\Specialists;
use app\models\db\Species;
use DateTimeImmutable;

/**
 * Class Ad
 * @package app\models\db\found_pet
 *
 * @property int          $id
 * @property string       $service_number             Единый номер обращения (ЕНО)
 * @property string       $type                       Тип объявления - F - found (данные об обнаруженных), L - lost (данные о потерянных)
 * @property string       $date_event                 Дата нахождения / пропажи животного
 * @property string       $time_event                 Время нахождения / пропажи животного
 * @property int          $id_address                 Адрес нахождения / пропажи животного
 * @property string       $animal_name                Кличка животного
 * @property string(15)   $chip                       Номер чипа
 * @property string       $stamp                      Клеймо или татуировка
 * @property int          $id_species                 Вид животного
 * @property int          $id_breed                   Порода
 * @property int          $id_color                   Окрас
 * @property string       $age                        Возраст животного
 * @property string       $sex                        Пол
 * @property string       $notice                     Дополнительная информация
 * @property boolean      $is_active                  Признак активности объявления (true - объявление активно, false - объявление в архиве)
 * @property boolean      $verify_status              Признак модерации объявления (null - на модерации, true - одобрено модератором, false - закрыто модератором)
 * @property string       $verify_at                  Дата прохождения модерации
 * @property string       $closed_at                  Дата и время закрытия объявления, в формате “yyyy-mm-dd hh:mm:ss”
 * @property string       $closed_reason              Причина закрытия объявления
 * @property int          $id_author                  Автор объявления
 * @property array|string $photo                      Массив идентификаторов фотографии ЦХЕД (в json)
 * @property string       $stamp_photo                Фотография клейма животного. Идентификатор фотографии ЦХЕД
 * @property array|string $subscriptions              Подписки на рассылку по объявлению (в json)
 * @property string       $created_at                 Дата и время создания объявления, в формате “yyyy-mm-dd hh:mm:ss”
 * @property string       $updated_at                 Дата и время изменения объявления, в формате “yyyy-mm-dd hh:mm:ss”
 * @property string       $active_till                Дата, до которой объявление будет активно (+90 дней от даты размещения)
 * @property string       $closed_by                  AUTHOR - зыкрыто пользователем, MODERATOR - модератором, AUTO - истек срок публикации
 * @property int          $id_specialist              Модератор объявления
 * @property bool         $is_priority_for_moderation Приоритетно для модерации
 * @property bool         $processed                  Статус обработки для отправки похожих объявлений(8021.1)
 * @property string|null  $outwardType                Обратный тип объявления
 *
 * @property \app\models\db\Species             $species
 * @property \app\models\db\Breeds              $breed
 * @property \app\models\db\Color               $color
 * @property \app\models\db\found_pet\AdAddress $address
 * @property \app\models\db\found_pet\AdAuthor  $author
 * @property \app\models\db\Specialists         $moderator
 */
class Ad extends ActiveRecord
{
    public const TYPE_FOUND = 'F';

    public const TYPE_LOST = 'L';

    public const DAYS_ACTIVE = 30;

    public const CLOSED_BY_MODERATOR = 'MODERATOR';

    public const CLOSED_BY_AUTHOR = 'AUTHOR';

    public const CLOSED_AUTO = 'AUTO';

    public const CLOSED_AUTO_CENSOR = 'AUTO_CENSOR';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'found_pet.ads';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verify_status', 'verify_at'], 'default', 'value' => null],
            [['is_priority_for_moderation'], 'default', 'value' => false],
            [['type', 'is_active', 'id_author', 'service_number'], 'required'],
            [['id_address', 'id_species', 'id_breed', 'id_author', 'id_color'], 'integer'],
            [['is_active', 'verify_status', 'is_priority_for_moderation'], 'boolean'],
            [['age', 'stamp', 'sex', 'notice', 'closed_reason', 'service_number', 'stamp_photo'], 'string'],
            [['chip'], 'string', 'max' => 15],
            [['sex'], 'string', 'max' => 1],
            [['sex'], 'in', 'range' => ['1', '2'], 'strict' => true],
            [['animal_name'], 'string'],
            [['type'], 'in', 'range' => [self::TYPE_FOUND, self::TYPE_LOST]],
            [['id_address'], 'exist', 'skipOnError' => true, 'targetClass' => AdAddress::class, 'targetAttribute' => ['id_address' => 'id']],
            [['id_author'], 'exist', 'skipOnError' => true, 'targetClass' => AdAuthor::class, 'targetAttribute' => ['id_author' => 'id']],
            [['id_species'], 'exist', 'skipOnError' => true, 'targetClass' => Species::class, 'targetAttribute' => ['id_species' => 'id']],
            [['id_breed'], 'exist', 'skipOnError' => true, 'targetClass' => Breeds::class, 'targetAttribute' => ['id_breed' => 'id']],
            [
                'id_breed',
                function ($attribute, $params, $validator) {
                    return Breeds::find()
                        ->where([
                            'id' => $this->id_breed,
                            'species_id' => $this->id_species,
                        ])->exists();
                },
                'when' => function ($model) {
                    /* @var $model \app\models\db\found_pet\Ad */
                    return !empty($model->id_species);
                },
                'skipOnEmpty' => true,
                'message' => 'Выбранная порода не соответствует выбранному виду'
            ],
            [['id_color'], 'exist', 'skipOnError' => true, 'targetClass' => Color::class, 'targetAttribute' => ['id_color' => 'id']],
            [['date_event', 'time_event', 'verify_at', 'closed_at', 'closed_by'], 'safe'], // TODO
            [['photo', 'subscriptions'], 'safe'], // TODO
            [['created_at', 'updated_at', 'active_till', 'id_specialist'], 'safe'],
        ];
    }

    /**
     * @param string $service_number
     * @return \app\models\db\found_pet\Ad|null
     */
    public static function findByServiceNumber($service_number)
    {
        return static::findOne(['service_number' => $service_number]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasOne(Species::class, ['id' => 'id_species']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreed()
    {
        return $this->hasOne(Breeds::class, ['id' => 'id_breed']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getColor()
    {
        return $this->hasOne(Color::class, ['id' => 'id_color']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(AdAddress::class, ['id' => 'id_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(AdAuthor::class, ['id' => 'id_author']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModerator()
    {
        return $this->hasOne(Specialists::class, ['id' => 'id_specialist']);
    }

    public function getOutwardType(): ?string
    {
        if (!$this->type) {
            return null;
        }

        return $this->isLost() ? self::TYPE_FOUND : self::TYPE_LOST;
    }

    public function isLost(): bool
    {
        return $this->type === self::TYPE_LOST;
    }

    public function isFound(): bool
    {
        return $this->type === self::TYPE_FOUND;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->created_at);

        return $dt ?: null;
    }
}
