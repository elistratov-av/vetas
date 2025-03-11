<?php

declare(strict_types = 1);

namespace app\modules\adminv\integration\bi\messages;

use app\common\api\messages\Request;

/**
 * Class FoundPetReportRq used to fetch "Found pet" report from BI system
 */
class VetServicesReportRq extends Request
{
    /**
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $to;

    /**
     * @var string[]|null
     */
    public $channels;

    /**
     * @var string[]|null
     */
    public $types;

    /**
     * @var string[]|null
     */
    public $services;

    /**
     * @var string[]|null
     */
    public $organizations;

    /**
     * @var string[]|null
     */
    public $specialists;

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();
        $this->addHeaders([
            'Accept' => '*/*',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        if (!$this->url) {
            $parts = [
                'rs:Format' => 'EXCEL',
                'start' => $this->from,
                'end' => $this->to,
            ];
            foreach (['channel' => $this->channels, 'ServiceTypes' => $this->types, 'service' => $this->services, 'organizations' => $this->organizations, 'specialist' => $this->specialists] as $n => $v) {
                if (is_array($v) && count($v)) {
                    $parts[$n] = implode(',', $v);
                } else if (!is_array($v)) {
                    $parts[$n] = $v;
                }
            }
            $this->url = 'ReportServer?/Emb/VetAS/ProvisionVeterinaryServices&' . http_build_query($parts);
        }

        return parent::getUrl();
    }
}
