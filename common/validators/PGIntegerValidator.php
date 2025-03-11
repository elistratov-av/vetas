<?php


namespace app\common\validators;

use Yii;
use yii\helpers\Json;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\JsExpression;

/**
 * Валидатор для полей postgres integer
 *
 * (на основе yii\validators\NumberValidator)
 * @package app\common\validators
 */
class PGIntegerValidator extends Validator
{
    /**
     *  PSQL INTEGER	4 bytes
     */
    const PSQL_INTEGER_MIN = -2147483648;
    /**
     *  PSQL INTEGER	4 bytes
     */
    const PSQL_INTEGER_MAX = 2147483647;

    /**
     * @var string user-defined error message used when the value is bigger than [[max]].
     */
    public $tooBig;
    /**
     * @var string user-defined error message used when the value is smaller than [[min]].
     */
    public $tooSmall;
    /**
     * @var string the regular expression for matching integers.
     */
    public $integerPattern = '/^[+-]?\d{1,10}$/';

    /**
     * @var int min value
     */
    public $min = 0;

    /**
     * @var int max value
     */
    public $max = self::PSQL_INTEGER_MAX;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be an integer.');
        }
        if ($this->tooSmall === null) {
            $this->tooSmall = Yii::t('yii', '{attribute} must be no less than {min}.');
        }
        if ($this->tooBig === null) {
            $this->tooBig = Yii::t('yii', '{attribute} must be no greater than {max}.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->isNotNumber($value)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        if (!preg_match($this->integerPattern, $value)) {
            $this->addError($model, $attribute, $this->message);
        }
        if ($value < $this->min || $value < self::PSQL_INTEGER_MIN) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->min]);
        }
        if ($value > $this->max || $value > self::PSQL_INTEGER_MAX) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->max]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if ($this->isNotNumber($value)) {
            return [Yii::t('yii', '{attribute} is invalid.'), []];
        }

        if (!preg_match($this->integerPattern, $value)) {
            return [$this->message, []];
        } elseif ($value < $this->min) {
            return [$this->tooSmall, ['min' => $this->min]];
        } elseif ($value > $this->max) {
            return [$this->tooBig, ['max' => $this->max]];
        }

        return null;
    }

    /*
     * @param mixed $value the data value to be checked.
     */
    private function isNotNumber($value)
    {
        return is_array($value)
            || (is_object($value) && !method_exists($value, '__toString'))
            || (!is_object($value) && !is_scalar($value) && $value !== null);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.number(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'pattern' => new JsExpression($this->integerPattern),
            'message' => $this->formatMessage($this->message, [
                'attribute' => $label,
            ]),
        ];

        if ($this->min !== null) {
            $options['min'] =  $this->min;
            $options['tooSmall'] = $this->formatMessage($this->tooSmall, [
                'attribute' => $label,
                'min' => $this->min,
            ]);
        }
        if ($this->max !== null) {

            $options['max'] = $this->max;
            $options['tooBig'] = $this->formatMessage($this->tooBig, [
                'attribute' => $label,
                'max' => $this->max,
            ]);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
