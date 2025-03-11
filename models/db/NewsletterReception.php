<?php

namespace app\models\db;

/**
 * This is the model class for table "newsletter_reception".
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property string|null $name_organizations
 * @property int $organization_id
 * @property string $text
 * @property bool $status
 * @property string|null $address
 *
 * @property Organizations $organization
 * @property NewsletterType $type_id
 */
class NewsletterReception extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'newsletter_reception';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type'], 'default', 'value' => null],
            [['type', 'organization_id'], 'integer'],
            ['type', 'in', 'range' => [
                NewsletterType::TYPE_RECEPTION,
            ]],
            [['name', 'organization_id', 'text'], 'required'],
            [['text'], 'string'],
            [['status'], 'boolean'],
            [['name', 'name_organizations', 'address'], 'string', 'max' => 255],
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
            'name_organizations' => 'Name Organizations',
            'text' => 'Text',
            'status' => 'Status',
            'address' => 'Address',
        ];
    }

    /**
     * Gets query for [[Organizations0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * Gets query for [[Type0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(NewsletterType::class, ['id' => 'type']);
    }
}
