<?php

declare(strict_types = 1);

namespace app\modules\adminv\integration\bi\messages;

use app\common\api\messages\Request;

/**
 * Class SearchPetsReportRq used to fetch "Search pets" report from BI system
 */
class SearchPetsReportRq extends Request
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
     * @var string
     */
    public $createdFrom;

    /**
     * @var string
     */
    public $createdTo;

    /**
     * @var string[]|null
     */
    public $type;

    /**
     * @var string[]|null
     */
    public $species;

    /**
     * @var string[]|null
     */
    public $status;

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
            $params = [
                'start' => $this->from,
                'end' => $this->to,
                'created_start' => $this->createdFrom,
                'created_end' => $this->createdTo,
                'type' => $this->type,
                'status' => $this->status,
                'species' => $this->species,
            ];
            foreach ($params as $n => $v) {
                if (is_array($v) && count($v)) {
                    $parts[$n] = implode(',', $v);
                } else if (!is_array($v)) {
                    $parts[$n] = $v;
                }
            }
            $this->url = 'ReportServer?/Emb/VetAS/AnimalSearch&' . http_build_query($parts);
        }

        return parent::getUrl();
    }
}
