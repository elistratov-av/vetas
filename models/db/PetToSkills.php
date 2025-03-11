<?php

namespace app\models\db;

/**
 * This is the model class for table "pet_to_skills".
 *
 * @property int $id
 * @property int|null $id_pet
 * @property int|null $id_skill
 *
 * @property Pets $pet
 * @property PetRefSkill $skill
 */
class PetToSkills extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_to_skills';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pet', 'id_skill'], 'default', 'value' => null],
            [['id_pet', 'id_skill'], 'integer'],
            [['id_skill'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefSkill::class, 'targetAttribute' => ['id_skill' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_pet' => 'Id Pet',
            'id_skill' => 'Id Skill',
        ];
    }

    /**
     * Gets query for [[Pet]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkill()
    {
        return $this->hasOne(PetRefSkill::class, ['id' => 'id_skill']);
    }
}
