<?php

namespace app\modules\elk\types;

use yii\base\Model;

class Animals extends Model
{
    /**
     * @var \app\modules\elk\types\Animal[] {minOccurs=1, maxOccurs=unbounded}
     * @soap
     */
    public $Animal;
}
