<?php
namespace app\models;

use app\traits\ActiveQueryTrait;

class BaseActiveQuery extends \yii\db\ActiveQuery
{
    use ActiveQueryTrait;
}
