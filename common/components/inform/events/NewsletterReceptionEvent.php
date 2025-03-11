<?php

namespace app\common\components\inform\events;

use app\models\db\Addresses;
use app\models\db\GovServices;
use app\models\db\NewsletterReception;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\models\db\Visits;

class NewsletterReceptionEvent extends SubscriptionEvent
{
    private const TEMPLATE_DATETIME = '<дата, время>';
    private const TEMPLATE_ORGANIZATION_NAME = '<наименование организации>';
    private const TEMPLATE_ORGANIZATION_ADDRESS = '<адрес организации>';
    private const TEMPLATE_DOCTOR_NAME = '<ФИО врача из приема>';
    private const TEMPLATE_SERVICES = '<услуги>';

    const EVENT_CODE = 'newsletter_reception';

    /** @var Visits */
    public $visit;

    /** @var NewsletterReception */
    public $newsletterReception;

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token): array
    {
        $title = $this->newsletterReception->name;

        $text = $this->newsletterReception->text;
        $text = $this->mb_str_replace(PHP_EOL, '<br/>', $text);

        if ($visit = $this->visit) {
            if ($time_range = $visit->time_range) {
                $time_range = $this->mb_str_replace('["', 'с ', $time_range);
                $time_range = $this->mb_str_replace('","', ' по ', $time_range);
                $time_range = $this->mb_str_replace('")', '', $time_range);
                $title = $this->mb_str_replace(self::TEMPLATE_DATETIME, $time_range, $title);
                $text = $this->mb_str_replace(self::TEMPLATE_DATETIME, $time_range, $text);
            }

            /** @var Organizations $organization */
            if ($organization = $visit->getOrganization()->one()) {
                $text = $this->mb_str_replace(self::TEMPLATE_ORGANIZATION_NAME, $organization->name, $text);

                /** @var Addresses $addresses */
                if ($addresses = $organization->getAddress()->one()) {
                    if ($addresses_name = $addresses->name) {
                        $text = $this->mb_str_replace(self::TEMPLATE_ORGANIZATION_ADDRESS, $addresses_name, $text);
                    }
                }
            }

            /** @var Specialists $specialists */
            if ($specialists = $visit->getSpecialists()->one()) {
                $text = $this->mb_str_replace(self::TEMPLATE_DOCTOR_NAME, $specialists->fullname, $text);
            }

            if (mb_strpos($text, self::TEMPLATE_SERVICES) !== false) {
                /** @var GovServices[] $services */
                if ($services = $visit->getServices()->all()) {
                    if (!empty($services)) {
                        $htmlList = '<ul>';

                        foreach ($services as $service) {
                            $htmlList .= '<li>' . $service->name . '</li>';
                        }

                        $htmlList .= '</ul>';
                        $text = $this->mb_str_replace(self::TEMPLATE_SERVICES, $htmlList, $text);
                    }
                }
            }
        }

        return [
            'title' => $title,
            'message' => $text,
        ];
    }

    public function mb_str_replace($search, $replace, $subject)
    {
        if (is_array($subject)) {
            $ret = array();
            foreach ($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }

        foreach ((array)$search as $key => $s) {
            if ($s == '' && $s !== 0) {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s, 0, 'UTF-8');
            while ($pos !== false) {
                $subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject, $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
            }
        }
        return $subject;
    }

}
