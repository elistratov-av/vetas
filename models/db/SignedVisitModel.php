<?php

namespace app\models\db;

/**
 * Class SignedVisitModel
 * @property integer $id
 * @property integer $id_visit
 * @property string  $sign_hash
 * @property string  $document
 * @property string  $cert_number
 * @property string  $cert_owner
 * @property string  $valid_from
 * @property string  $valid_to
 * @property string  $created_at
 * @package app\models\db
 */
class SignedVisitModel extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'signed_visits';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['sign_hash', 'document'], 'required'],
            [['id', 'id_visit'], 'integer'],
            [['sign_hash'], 'string', 'max' => 20000],
            [['document'], 'string', 'max' => 20000],
            [['cert_number', 'cert_owner'], 'string', 'max' => 255],
            [['valid_from', 'valid_to'], function ($attribute, $params, $validator) {
                // передается с фронта в формате 09/01/2018 10:50:32
                $pattern = '#^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$#';
                if (preg_match($pattern, $this->$attribute) === 1) {
                    try {
                        $value = \DateTime::createFromFormat('d/m/Y H:i:s', $this->$attribute)->format('Y-m-d H:i:s');
                    } catch (\Throwable $e) {
                        $value = false;
                    }
                    if ($value !== false) {
                        $this->$attribute = $value;
                    }
                }
            }],
            [['valid_from', 'valid_to'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['created_at'], 'safe'],
        ];
    }
}
