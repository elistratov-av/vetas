<?php

namespace app\common\validators;

use yii\validators\UniqueValidator as YiiUniqueValidator;

class UniqueValidator extends YiiUniqueValidator
{


    public function init()
    {
        parent::init();
        $this->filter = $this->filter_func();

        $this->skipOnEmpty = true;
        $this->skipOnError = true;
    }

    /**
     * @return \Closure
     */
    private function filter_func()
    {
        return function ($query) {
            /** @var \yii\db\Query $query */
            foreach ($query->where as $key => $rule) {
                if (is_array($rule)) {
                    $pattern = key($rule);
                    $value = reset($rule);
                    if (preg_match("/^\d+$/", $value) === 1) {
                        $query->where[$key] = ['=', $pattern, $value];
                    } else {
                        $value = str_replace(['_', '%'], ['\_', '\%'], $value);  //экранируем, т.к. _ в pgsql означает любой смвол

                        if (preg_match("/[\\\]+$/", $value, $matches)) {
                            // SQLSTATE[22025]: Invalid escape sequence: 7 ERROR:  LIKE pattern must not end with escape character
                            // экранируем каждый бэкслэш (просто удваиваем их)
                            $value .= $matches[0];
                        }

                        $query->where[$key] = ['ilike', $pattern, $value, false];
                    }
                }
            }

            return $query;
        };
    }

    public function validateAttribute($model, $attribute)
    {
        if(is_array($this->attributes)){
            foreach ($this->attributes as $k => $v){
                if($model->$v === '' || $model->$v === null){
                    $this->addError($model, $this->attributes[$k],  "{$this->attributes[$k]} is empty.");
                    return false;
                }
            }
        }
        return parent::validateAttribute($model, $attribute);
    }

}
