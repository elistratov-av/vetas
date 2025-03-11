<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * Class Status
 * @package app\modules\foundPet\etp\status
 */
abstract class Status extends BaseObject
{
    /**
     * @var int
     */
    public $StatusCode;
    /**
     * @var string
     */
    public $StatusTitle;
    /**
     * @var string
     */
    public $StatusDate;
    /**
     * @var string
     */
    public $Note;
    /**
     * @var string
     */
    public $ServiceNumber;
    /**
     * @var string
     */
    public $ReasonCode;
    /**
     * @var string
     */
    public $StatusId;

    /**
     * @var \app\models\db\found_pet\Ad
     */
    protected $ad;

    /**
     * @return array
     */
    public function prepareRequestData()
    {
        $this->StatusDate = (new \DateTime())->add(new \DateInterval('PT1S'))->format('c');
        $this->prepareNote();

        return ArrayHelper::toArray($this);
    }

    /**
     * @return string
     */
    protected function prepareNote()
    {
        return $this->Note;
    }

    /**
     * @param \app\models\db\found_pet\Ad|null $ad
     */
    public function setAd(\app\models\db\found_pet\Ad $ad = null): void
    {
        $this->ad = $ad;
    }

    /**
     * @return string
     */
    protected function createUserAdsUrl()
    {
        $module = \Yii::$app->getModule('foundPet');
        $env = ArrayHelper::getValue($module->params, 'mosru_env');
        $url = ArrayHelper::getValue($module->params, 'mosru_urls.' . $env . '.user_ads');

        return empty($url) ? '' : $url;
    }

    /**
     * @param string $type
     * @param int    $id
     * @return string
     */
    protected function createAdUrl(string $type, int $id)
    {
        $module = \Yii::$app->getModule('foundPet');
        $env = ArrayHelper::getValue($module->params, 'mosru_env');
        $url = ArrayHelper::getValue($module->params, 'mosru_urls.' . $env . '.single_ad');
        $type = ($type == Ad::TYPE_LOST) ? 'lost' : 'found';

        return empty($url) ? '' : strtr($url, ['{type}' => $type, '{id}' => $id]);
    }
}
