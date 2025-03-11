<?php

namespace app\models\db;

/**
 * Class AndroidBuild
 * @package app\models\db
 *
 * @property int     $id
 * @property string  $filename
 * @property string  $version
 * @property bool    $update_required
 * @property integer $created_by
 * @property integer $updated_by
 * @property string  $created_at
 * @property string  $updated_at
 */
class AndroidBuild extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'admin.android_builds';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['filename', 'version'], 'required'],
            [['filename', 'version'], 'string', 'max' => 255],
            ['update_required', 'boolean'],
            ['update_required', 'default', 'value' => false],
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'filename' => 'Файл',
            'version' => 'Версия',
            'update_required' => 'Обновление обязательно',
        ];
    }
}
