<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "public.user_questions".
 *
 * @property int          $id
 * @property string       $create_date        Дата и время загрузки создания
 * @property int          $question_user_id   Идентификатор пользователя и в таблице user.id, который создал запись
 * @property string       $question_type      Тип записи (user - вопрос пользователя, support - инструкция от тех поддержки)
 * @property string       $question           Текст вопроса
 * @property string       $answer             Текст ответа  на вопрос или текст инструкции
 * @property int          $answer_user_id     Идентификатор пользователя и в таблице user.id, который создал ответ
 * @property string       $answer_date        Дата и время ответа или инструкции
 * @property int          $answer_status      Статус ответа ( 0 - ответ не дан, 1 - ответ дан)
 * @property int          $search_status      Статус использования вопроса для поиска ( 0 - не использовать поиском, 1 - использовать поиском)
 * @property string       $keywords           Ключевые слова
 *
 * @property-read  Users  $questionUser       Пользователь, который создал вопрос (question_user_id)
 */
class UserQuestions extends ActiveRecord
{
    const TYPE_USER = 'user';
    const TYPE_SUPPORT = 'support';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.user_questions';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->attachBehavior(
            'create_date',
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s'),
                'createdAtAttribute' => 'create_date',
                'updatedAtAttribute' => false
            ]
        );

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['create_date'], 'safe'],
            [['question_user_id', 'question_type', 'question'], 'required'],
            ['question_type', 'in', 'range' => [self::TYPE_USER, self::TYPE_SUPPORT]],
            [['question_user_id', 'answer_user_id'], 'integer'],
            //[['question'], 'unique'],
            [['question', 'answer', 'keywords'], 'string'],
            [['question', 'answer', 'keywords'], FullTrimValidator::class],
            ['answer_status', 'in', 'range' => [0, 1]],
            ['search_status', 'in', 'range' => [0, 1]],
            [
                ['question_user_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Users::class,
                'targetAttribute' => ['question_user_id' => 'id']
            ],
        ];
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuestionUser()
    {
        return $this->hasOne(Users::class, ['id' => 'question_user_id']);
    }

    /**
     * @return array
     */
    public static function types()
    {
        return [
            self::TYPE_USER,
            self::TYPE_SUPPORT
        ];
    }

    /**
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_USER => 'Вопрос пользователя',
            self::TYPE_SUPPORT => 'Инструкция от тех. поддержки'
        ];
    }

    /**
     * @return bool
     */
    public function isQuestionUser()
    {
        return $this->question_type == self::TYPE_USER;
    }

    /**
     * @return bool
     */
    public function isQuestionSupport()
    {
        return $this->question_type == self::TYPE_SUPPORT;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'create_date' => 'Дата и время загрузки создания',
            'question_user_id' => 'Идентификатор пользователя и в таблице user.id, который создал запись',
            'question_type' => 'Тип записи (user - вопрос пользователя, support - инструкция от тех поддержки)',
            'question' => 'Текст вопроса',
            'answer' => 'Текст ответа  на вопрос или текст инструкции',
            'answer_user_id' => 'Идентификатор пользователя и в таблице user.id, который создал ответ',
            'answer_date' => 'Дата и время ответа или инструкции',
            'answer_status' => 'Статус ответа ( 0 - ответ не дан, 1 - ответ дан)',
            'search_status' => 'Статус использования вопроса для поиска ( 0 - не использовать поиском, 1 - использовать поиском)',
            'keywords' => 'Ключевые слова',
            'real_file_name' => 'Имя загруженного файла',
            'intr_file_name' => 'Внетреннее имя файла'
        ];
    }
}
