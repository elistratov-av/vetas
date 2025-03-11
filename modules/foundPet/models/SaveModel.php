<?php

namespace app\modules\foundPet\models;

use app\models\db\Breeds;
use app\models\db\Color;
use app\models\db\found_pet\Ad;
use app\models\db\found_pet\AdAddress;
use app\models\db\found_pet\AdAuthor;
use app\models\db\Species;
use app\modules\foundPet\service\CensorService;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use DateTimeImmutable;

/**
 * Class SaveModel
 *
 * @package app\modules\foundPet\models
 */
class SaveModel extends Model
{
    private const TYPE_LOST = 'lost';

    private const TYPE_FOUND = 'found';

    /**
     * @var string[] API -> model
     */
    protected static $typeMap = [
        self::TYPE_LOST => Ad::TYPE_LOST,
        self::TYPE_FOUND => Ad::TYPE_FOUND,
    ];

    /**
     * @var array
     */
    public $data;

    /**
     * @var \app\models\db\found_pet\Ad
     */
    public $ad;

    /**
     * @inheritDoc
     * TODO
     */
    public function rules()
    {
        return [
            ['data', 'safe'],
        ];
    }

    /**
     * @return bool
     */
    public function create()
    {
        $ad = Ad::findByServiceNumber($this->data['service_number']);

        if ($ad !== null) {
            $this->ad = $ad;

            return true;
        }

        $type = $this->data['type'] ?? '';
        if (!in_array($type, [self::TYPE_LOST, self::TYPE_FOUND], true)) {
            $this->addError('type', $type ? sprintf('Некорректный тип объявления "%s"', $type) : 'Не указан тип объявления');

            return false;
        }

        if ($existingId = self::findDuplicateId($this->data)) {
            $typeTxt = self::TYPE_FOUND === $this->data['type']
                ? 'об обнаружении'
                : 'о пропаже'
            ;
            $this->addError('chip', "Активное объявление {$typeTxt} по данному чипу уже существует (id: {$existingId})");

            return false;
        }

        $author = AdAuthor::findBySsoId($this->data['sso_id']);

        if ($author === null) {
            $author = new AdAuthor();
            $author->load($this->data, '');
            if (!$author->save()) {
                $this->addError('sso_id', 'Ошибка при сохранении автора объявления' . $this->formatErrors($author));

                return false;
            }
        }

        $address = $this->findAddress();
        if ($address === null) {
            $address = new AdAddress();
            $address->load($this->data['place'], '');
            if (!$address->save()) {
                $this->addError('place', 'Ошибка при сохранении адреса' . $this->formatErrors($address));

                return false;
            }
        } else {
            foreach ($address->attributes() as $attribute) {
                if (in_array($attribute, ['id', 'created_at', 'updated_at'], true)) {
                    continue;
                }
                if ($address->$attribute === null && !isset($this->data['place'][$attribute])) {
                    continue;
                }
                if (!isset($this->data['place'][$attribute]) || $address->$attribute != $this->data['place'][$attribute]) {
                    $address = new AdAddress();
                    $address->load($this->data['place'], '');
                    if (!$address->save()) {
                        $this->addError('place', 'Ошибка при сохранении адреса' . $this->formatErrors($address));

                        return false;
                    }
                    break;
                }
            }
        }

        if (!$this->validateSpeciesAndBreeds() || !$this->validateColor()) {
            return false;
        }

        $ad = new Ad();
        $ad->load(array_merge($this->data, ['type' => $type === self::TYPE_LOST ? Ad::TYPE_LOST : Ad::TYPE_FOUND]), '');
        $ad->is_active = true;
        $ad->active_till = (new DateTimeImmutable(sprintf('+ %d days', Ad::DAYS_ACTIVE)))->format('Y-m-d');
        $ad->id_author = $author->id;
        $ad->id_address = $address->id;
        $ad->date_event = (ArrayHelper::getValue($this->data, $ad->isLost() ? 'date_loss' : 'date_found')) ?? (new \DateTime())->format('Y-m-d');
        $ad->time_event = (ArrayHelper::getValue($this->data, $ad->isLost() ? 'time_loss' : 'time_found')) ?? (new \DateTime())->format('H:i:s');

        self::censorshipCheck($ad);

        if (!$ad->save()) {
            $this->addError('service_number', 'Ошибка при сохранении объявления' . $this->formatErrors($ad));

            return false;
        }

        $subscribe = $this->data['subscribe'] ?? null;
        if (is_array($subscribe)) {
            $subscribe['subscribe_id'] = $ad->id * 1000 + 1;
            $ad->subscriptions = $subscribe;
            $ad->save(false, ['subscriptions']);
        }

        $this->ad = $ad;
        return true;
    }

    /**
     * @return bool
     */
    public function update()
    {
        $ad = Ad::findOne(['id' => $this->data['id']]);

        if ($ad === null) {
            $this->addError('id', 'Объявление не найдено по id');

            return false;
        }

        unset($this->data['id']);

        if ($ad->service_number !== $this->data['service_number']) {
            $this->addError('service_number', 'Переданный service_number не соответствует service_number объявления');

            return false;
        }

        if ($ad->is_active !== true) {
            $this->addError('id', 'Объявление неактивно');

            return false;
        }

        $author = AdAuthor::findOne(['id' => $ad->id_author]);

        if ($author === null) {
            $this->addError('sso_id', 'Автор объявления не найден');

            return false;
        }

        if ($author->sso_id !== $this->data['sso_id']) {
            $this->addError('sso_id', 'Переданный sso_id не соответствует sso_id автора объявления');

            return false;
        }

        if (($existingId = self::findDuplicateId($this->data, $ad)) && ($existingId != $ad->id)) {
            $typeTxt = self::TYPE_FOUND === ($this->data['type'] ?? $ad->type)
                ? 'об обнаружении'
                : 'о пропаже'
            ;
            $this->addError('chip', "Активное объявление {$typeTxt} по данному чипу уже существует (id: {$existingId})");

            return false;
        }

        foreach (['first_name', 'middle_name', 'last_name', 'phone', 'email'] as $attribute) {
            if ($author->$attribute !== $this->data[$attribute]) {
                $author->load($this->data, '');
                if (!$author->save()) {
                    $this->addError('sso_id', 'Ошибка при сохранении автора объявления' . $this->formatErrors($author));

                    return false;
                }
                break;
            }
        }

        $address = AdAddress::findOne(['id' => $ad->id_address]);

        if ($address === null) {
            $address = new AdAddress();
            $address->load($this->data['place'], '');
            if (!$address->save()) {
                $this->addError('place', 'Ошибка при сохранении адреса' . $this->formatErrors($address));

                return false;
            }
        } else {
            foreach ($address->attributes() as $attribute) {
                if (in_array($attribute, ['id', 'created_at', 'updated_at'], true)) {
                    continue;
                }
                if ($address->$attribute === null && !isset($this->data['place'][$attribute])) {
                    continue;
                }
                if (!isset($this->data['place'][$attribute]) || $address->$attribute != $this->data['place'][$attribute]) {
                    $address = new AdAddress();
                    $address->load($this->data['place'], '');
                    if (!$address->save()) {
                        $this->addError('place', 'Ошибка при сохранении адреса' . $this->formatErrors($address));

                        return false;
                    }
                    break;
                }
            }
        }

        if (!$this->validateSpeciesAndBreeds() || !$this->validateColor()) {
            return false;
        }

        $ad->load($this->data, '');
        $ad->id_address = $address->id;

        foreach (['photo', 'stamp_photo'] as $attribute) {
            if (!isset($this->data[$attribute])) {
                $ad->$attribute = null;
            }
        }

        if ($ad->verify_status === true) {
            // Если пользователь редактирует объявление, то статус объявления восстанавливается до статуса "На модерации"
            $ad->verify_status = null;
            $ad->verify_at = null;
        }
        $ad->processed = false;
        $ad->active_till = (new DateTimeImmutable(sprintf('+ %d days', Ad::DAYS_ACTIVE)))->format('Y-m-d');
        $ad->date_event = (ArrayHelper::getValue($this->data, $ad->isLost() ? 'date_loss' : 'date_found')) ?? $ad->date_event;
        $ad->time_event = (ArrayHelper::getValue($this->data, $ad->isLost() ? 'time_loss' : 'time_found')) ?? $ad->time_event;

        self::censorshipCheck($ad);
        $result = $ad->save();

        if ($result === true) {
            $this->ad = $ad;
        } else {
            $this->addError('service_number', 'Ошибка при сохранении объявления' . $this->formatErrors($ad));
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function close()
    {
        $ad = Ad::findOne(['id' => $this->data['id']]);

        if ($ad === null) {
            $this->addError('id', 'Объявление не найдено по id');

            return false;
        }

        if ($ad->service_number !== $this->data['service_number']) {
            $this->addError('service_number', 'Переданный service_number не соответствует service_number объявления');

            return false;
        }

        $author = AdAuthor::findOne(['id' => $ad->id_author]);

        if ($author === null) {
            $this->addError('sso_id', 'Автор объявления не найден');

            return false;
        }

        if ($author->sso_id !== $this->data['sso_id']) {
            $this->addError('sso_id', 'Переданный sso_id не соответствует sso_id автора объявления');

            return false;
        }

        if ($ad->is_active === false) {
            $this->ad = $ad;

            return true;
        }

        $ad->is_active = false;
        $ad->processed = true;
        $ad->closed_reason = $this->data['reason'];
        if (!empty($this->data['notice'])) {
            $ad->notice = $this->data['notice'];
        }
        $ad->closed_at = date('Y-m-d H:i:s');
        $ad->closed_by = Ad::CLOSED_BY_AUTHOR;

        $result = $ad->save();

        if ($result === true) {
            $this->ad = $ad;
        } else {
            $this->addError('service_number', 'Ошибка при сохранении объявления');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function extend()
    {
        $ad = Ad::findOne(['id' => $this->data['id']]);

        if ($ad === null) {
            $this->addError('id', 'Объявление не найдено по id');

            return false;
        }

        if ($ad->service_number !== $this->data['service_number']) {
            $this->addError('service_number', 'Переданный service_number не соответствует service_number объявления');

            return false;
        }

        $author = AdAuthor::findOne(['id' => $ad->id_author]);

        if ($author === null) {
            $this->addError('sso_id', 'Автор объявления не найден');

            return false;
        }

        if ($author->sso_id !== $this->data['sso_id']) {
            $this->addError('sso_id', 'Переданный sso_id не соответствует sso_id автора объявления');

            return false;
        }

        $ad->is_active = true;
        $ad->closed_reason = null;
        $ad->closed_at = null;
        $ad->closed_by = null;
        $ad->active_till = (new DateTimeImmutable(sprintf('+ %d days', Ad::DAYS_ACTIVE)))->format('Y-m-d');

        $result = $ad->save();

        if ($result === true) {
            $this->ad = $ad;
        } else {
            $this->addError('service_number', 'Ошибка при сохранении объявления');
        }

        return $result;
    }

    /**
     * @param bool $isNew
     * @return bool
     */
    public function subscribe($isNew = true)
    {
        if (!isset($this->data['id_ad'])) {
            $this->addError('id_ad', 'Не указан id объявления');

            return false;
        }

        $ad = Ad::findOne(['id' => (int)$this->data['id_ad']]);

        if ($ad === null) {
            $this->addError('id_ad', 'Объявление не найдено по id');

            return false;
        }

        if ($ad->service_number !== $this->data['service_number']) {
            $this->addError('service_number', 'Переданный service_number не соответствует service_number объявления');

            return false;
        }

        if ($ad->is_active !== true) {
            $this->addError('id', 'Объявление неактивно');

            return false;
        }

        if ($ad->author === null) {
            $this->addError('sso_id', 'Автор объявления не найден');

            return false;
        }

        if ($ad->author->sso_id !== $this->data['sso_id']) {
            $this->addError('sso_id', 'Переданный sso_id не соответствует sso_id автора объявления');

            return false;
        }

        $subscription = $this->data;
        unset($subscription['id_ad'], $subscription['service_number'], $subscription['sso_id']);
        $subscription['subscribe_id'] = (isset($ad->subscriptions['subscribe_id']) ? $ad->subscriptions['subscribe_id'] : $ad->id * 1000) + 1;
        $ad->subscriptions = $subscription;

        $result = $ad->save();

        if ($result === true) {
            $this->ad = $ad;
        } else {
            $this->addError('subscriptions', 'Ошибка при сохранении подписки');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function unsubscribe()
    {
        if (!isset($this->data['id_ad'])) {
            $this->addError('id_ad', 'Не указан id объявления');

            return false;
        }

        $ad = Ad::findOne(['id' => (int)$this->data['id_ad']]);

        if ($ad === null) {
            $this->addError('id_ad', 'Объявление не найдено по id');

            return false;
        }

        if ($ad->author === null) {
            $this->addError('sso_id', 'Автор объявления не найден');

            return false;
        }

        if ($ad->author->sso_id !== $this->data['sso_id']) {
            $this->addError('sso_id', 'Переданный sso_id не соответствует sso_id автора объявления');

            return false;
        }

        $ad->subscriptions = null;

        $result = $ad->save();

        if ($result === true) {
            $this->ad = $ad;
        } else {
            $this->addError('subscriptions', 'Ошибка при сохранении отписки от рассылки');
        }

        return $result;
    }

    /**
     * Проверка на цензурность
     *
     * @param Ad $ad
     */
    private static function censorshipCheck(Ad $ad): void
    {
        $censorNotice = CensorService::check($ad->notice ?: '');
        $censorAnimalName = CensorService::check($ad->animal_name ?: '');
        $censorGeneral = $censorNotice < $censorAnimalName ? $censorNotice : $censorAnimalName;
        switch ($censorGeneral) {
            case 0: // Приоритетно к проверке - возможно наличие нецензурности
                $ad->is_priority_for_moderation = true;
                break;
            case -1: // Отклонение - явная нецензурность
                $ad->is_active = false;
                $ad->processed = true;
                $ad->active_till = null;
                $ad->verify_status = false;
                $ad->verify_at = date('Y-m-d H:i:s');
                $ad->closed_at = date('Y-m-d H:i:s');
                $ad->closed_reason = 'Объявление удалено по причине несоответствия правилам предоставления электронного сервиса';
                $ad->closed_by = Ad::CLOSED_AUTO_CENSOR;
                break;
            default:
                break;
        }
    }

    /**
     * @return \app\models\db\found_pet\AdAddress|null
     */
    private function findAddress()
    {
        foreach (['fias_id', 'kladr_id'] as $field) {
            if (isset($this->data['place'][$field])) {
                $address = AdAddress::findOne([$field => $this->data['place'][$field]]);
                if ($address !== null) {
                    return $address;
                }
            }
        }

        $condition = [
            'city' => $this->data['place']['city'],
            'area' => $this->data['place']['area'],
            'street' => $this->data['place']['street'],
            'is_map' => $this->data['place']['is_map'],
            'is_manually_set' => $this->data['place']['is_manually_set'],
        ];

        foreach (['fias_id', 'kladr_id', 'settlement', 'house', 'postcode', 'geo_lat', 'geo_lon', 'radius', 'pobox'] as $column) {
            if (isset($this->data['place'][$column])) {
                $condition[$column] = $this->data['place'][$column];
            } else {
                $condition[$column] = null;
            }
        }

        return AdAddress::find()
            ->where($condition)
            ->one();
    }

    /**
     * @param \yii\base\Model $model
     * @return string
     */
    private function formatErrors($model)
    {
        $str = implode("\n", $model->getErrorSummary(true));

        return empty($str) ? '' : ("\n" . $str);
    }

    /**
     * @return bool
     */
    private function validateSpeciesAndBreeds()
    {
        if (!isset($this->data['id_species']) || $this->data['id_species'] == 0) {
            $this->addError('id_species', 'Не указан вид животного');

            return false;
        }

        $this->data['id_species'] = (int)$this->data['id_species'];

        $ids = (new Query())
            ->select('id')
            ->from(Species::tableName())
            ->where([
                'or',
                ['tech_name' => Species::TECH_NAME_CAT],
                ['tech_name' => Species::TECH_NAME_DOG],
            ])
            ->column();

        if (!in_array($this->data['id_species'], $ids, true)) {
            $this->addError('id_species', 'Вид животного может быть только "собаки" или "кошки"');

            return false;
        }

        if (!isset($this->data['id_breed'])) {
            return true;
        }
        if ($this->data['id_breed'] == 0) {
            $this->data['id_breed'] = null;

            return true;
        }

        $this->data['id_breed'] = (int)$this->data['id_breed'];

        $breed = (new Query())
            ->select(['id', 'species_id AS id_species'])
            ->from(Breeds::tableName())
            ->where(['id' => $this->data['id_breed']])
            ->one();

        if ($breed === false) {
            $this->addError('id_breed', 'Порода животного не найдена');

            return false;
        }

        if (!in_array($breed['id_species'], $ids, true)) {
            $this->addError('id_breed', 'Порода животного не соответствует виду животного');

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function validateColor(): bool
    {
        $q = (new Query())
            ->from(Color::tableName())
            ->where(['id' => $this->data['id_color'] ?? null]);

        $this->data['id_color'] = $q->exists() ? (int)$this->data['id_color'] : null;

        // $this->addError('id_color', 'Окрас животного не найден');

        return true;
    }

    /**
     * Поиск ранее созданного об-ния, для которого новое будет "дублем"
     * 
     * На данный момент проверяет уникальности двойки (не-пустой-чип, тип) для активных об-ний
     * 
     * @param array $apiData
     * @param \app\models\db\found_pet\Ad|null Обновляемое объявление
     * 
     * return int|null ID дублицируемого об-ния
     */
    public static function findDuplicateId(array $apiData, Ad $ad = null)
    {
        $newType = array_key_exists('type', $apiData) ? self::mapType($apiData['type']) : null;
        $newChip = array_key_exists('chip', $apiData) ? $apiData['chip'] : null;

        if (null !== $ad) {
            if (
                // если новые данные не указаны
                (!array_key_exists('type', $apiData) && !array_key_exists('chip', $apiData))
                // ... или не меняют текущие
                || ($newType == $ad->type && $newChip == $ad->chip)
            ) {
                // значения интересующих атрибутов не меняются
                return null;
            }
        } elseif (null === $newChip) {
            // Нет данных о чипе
            return null;
        }

        // есть данные по чипу и типу, проверим в базе
        return Ad::find()
            ->select('id')
            ->where([
                'chip' => $newChip,
                'type' => $newType ?? $ad->type,
                'is_active' => true,
            ])
            ->scalar()
        ;
    }

    /**
     * @param string $apiType Значение типа из запроса АПИ (см. self::TYPE_*)
     * 
     * @return string|null соответствующее значение типа модели Ad (см. Ad::TYPE_*)
     */
    public static function mapType(string $apiType)
    {
        return self::$typeMap[$apiType] ?? null;
    }
}
