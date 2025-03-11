<?php

declare(strict_types = 1);

namespace app\modules\adminv\integration\bi\messages;

use app\common\api\messages\Request;

/**
 * Class UnvaccPetsReportRq used to fetch "unvacc pets" report from BI system
 */
class UnvaccPetsReportRq extends Request
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
    public $area;

    /**
     * @var string[]|null
     */
    public $district;

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
            ];
            $params = ['area' => $this->area, 'district' => $this->district];
            foreach ($params as $n => $v) {
                if (is_array($v) && count($v)) {
                    $parts[$n] = implode(',', $v);
                } else if (!is_array($n)) {
                    $parts[$n] = $v;
                }
            }
            $this->url = 'ReportServer?/Emb/VetAS/UnvaccinatedAnimals&' . http_build_query($parts);
        }

        return parent::getUrl();
    }
}
