<?php

namespace app\common\components\inform\events;

use app\models\db\Visits;

/**
 * Событие: Уведомления о готовности результатов исследований
 *
 * Class ReadyResearchEvent
 * @package app\common\components\inform\events
 */
class ReadyResearchEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'research';

    /** @var Visits */
    public $visit;

    /**
     * Типы исследований (список услуг), через запятую
     * @var string
     */
    public $researchTypes;

    /** @var null|string  */
    public $research_link_helmint = null;

    /** @var null|string  */
    public $research_link_biochemistry_feces = null;

    /** @var null|string  */
    public $research_link_biochemistry_blood = null;

    /** @var null|string  */
    public $research_link_hormonal_blood = null;

    /** @var null|string  */
    public $research_link_urine = null;

    /** @var null|string  */
    public $research_link_microscopic_cytology = null;

    /** @var null|string  */
    public $research_link_microscopic_bloodparasites = null;

    /** @var null|string  */
    public $research_link_microscopic_ectoparasites = null;

    /** @var null|string  */
    public $research_link_blood = null;

    /**
     * @return array
     */
    public function getEventData($token) : array
    {
        $data = [
            'io' => $this->visit->owner->getNameForInformation(),
            'pet_name' => implode(',', array_map(function($pet){
                    return $pet->name;
                }, $this->visit->pets)
            ),
            'research_types' => $this->researchTypes,
            'link' => $this->getUnsubscribeLink($token)
        ];

        if (!is_null($this->research_link_helmint)) {
            $data['research_link_helmint'] = $this->research_link_helmint;
        }

        if (!is_null($this->research_link_biochemistry_feces)) {
            $data['research_link_biochemistry_feces'] = $this->research_link_biochemistry_feces;
        }

        if (!is_null($this->research_link_biochemistry_blood)) {
            $data['research_link_biochemistry_blood'] = $this->research_link_biochemistry_blood;
        }

        if (!is_null($this->research_link_hormonal_blood)) {
            $data['research_link_hormonal_blood'] = $this->research_link_hormonal_blood;
        }

        if (!is_null($this->research_link_urine)) {
            $data['research_link_urine'] = $this->research_link_urine;
        }

        if (!is_null($this->research_link_microscopic_cytology)) {
            $data['research_link_microscopic_cytology'] = $this->research_link_microscopic_cytology;
        }

        if (!is_null($this->research_link_microscopic_bloodparasites)) {
            $data['research_link_microscopic_bloodparasites'] = $this->research_link_microscopic_bloodparasites;
        }

        if (!is_null($this->research_link_microscopic_ectoparasites)) {
            $data['research_link_microscopic_ectoparasites'] = $this->research_link_microscopic_ectoparasites;
        }

        if (!is_null($this->research_link_blood)) {
            $data['research_link_blood'] = $this->research_link_blood;
        }

        return $data;
    }

}
