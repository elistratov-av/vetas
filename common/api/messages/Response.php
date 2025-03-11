<?php

declare(strict_types = 1);

namespace app\common\api\messages;

/**
 * Response represents HTTP request response.
 *
 * @property bool $isOk Whether response is OK. This property is read-only.
 * @property string|null $statusCode Status code. This property is read-only.
 */
abstract class Response extends Message
{
    /**
     * @param mixed $data
     */
    abstract public function setPayload($data): void;

    /**
     * Returns status code.
     *
     * @return string status code.
     */
    public function getStatusCode(): ?string
    {
        $headers = $this->getHeaders();
        if (!$headers->has('http-code')) {
            return null;
        }

        // take into account possible 'follow location'
        $statusCodeHeaders = $headers->get('http-code', null, false);

        return empty($statusCodeHeaders) ? null : end($statusCodeHeaders);
    }

    /**
     * Checks if response status code is OK (status code = 20x)
     *
     * @return bool whether response is OK.
     */
    public function getIsOk(): bool
    {
        $code = $this->getStatusCode();

        return $code ? strncmp('20', $this->getStatusCode(), 2) === 0 : false;
    }
}
