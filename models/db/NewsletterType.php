<?php

namespace app\models\db;

/**
 * This is the model class for table "newsletter_type".
 *
 * @property int $id
 * @property string $type
 * @property string $name
 *
 * @property NewsletterInfo[] $newsletterInfos
 */
class NewsletterType extends ActiveRecord
{
    public const TYPE_USER = 1;
    public const TYPE_EVENT = 2;
    public const TYPE_RECEPTION = 3;

    public const TYPE_USER_LABEL = 'newsletter_user';
    public const TYPE_EVENT_LABEL = 'newsletter_event';
    public const TYPE_RECEPTION_LABEL = 'newsletter_reception';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'newsletter_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type'], 'required'],
            [['type', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
        ];
    }
}
