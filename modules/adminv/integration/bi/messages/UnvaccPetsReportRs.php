<?php

declare(strict_types = 1);

namespace app\modules\adminv\integration\bi\messages;

use app\common\api\messages\Response;

/**
 * Class UnvaccPetsReportRs contains "unvacc pets" report from BI system
 *
 * @property string|null $mimeType Report's mime type. This property is read-only.
 * @property string|null $fileName Report's file name. This property is read-only.
 */
class UnvaccPetsReportRs extends Response
{
    /**
     * @var string Document raw data
     */
    public $data;

    /**
     * @inheritDoc
     */
    public function setPayload($data): void
    {
        $this->data = $data;
    }

    /**
     * @return string|null Get report mime type
     */
    public function getMimeType(): ?string
    {
        return $this->getHeaders()->get('content-type');
    }

    /**
     * @return string|null Get report file name
     */
    public function getFileName(): ?string
    {
        $disposition = $this->getHeaders()->get('content-disposition');
        if (!$disposition) {
            return null;
        }
        preg_match('/filename="(.+?)"/u', $disposition, $matches);

        return $matches[1] ?? null;
    }
}
