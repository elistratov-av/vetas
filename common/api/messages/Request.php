<?php

declare(strict_types = 1);

namespace app\common\api\messages;

use yii\helpers\ArrayHelper;

/**
 * Request represents HTTP request.
 *
 * @property string $method Request method.
 * @property array $options Request options. This property is read-only.
 */
abstract class Request extends Message
{
    /**
     * @var string target URL
     */
    protected $url;

    /**
     * @var string request method
     */
    protected $method = 'GET';

    /**
     * @var array request options.
     */
    private $options = [];

    /**
     * Sets target URL.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Sets request method.
     *
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Following options are supported:
     * - timeout: int, the maximum number of seconds to allow request to be executed.
     * - proxy: string, URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     * - userAgent: string, the contents of the "User-Agent: " header to be used in a HTTP request.
     * - followLocation: bool, whether to follow any "Location: " header that the server sends as part of the HTTP header.
     * - maxRedirects: int, the max number of redirects to follow.
     * - protocolVersion: float|string, HTTP protocol version.
     * - sslVerifyPeer: bool, whether verification of the peer's certificate should be performed.
     * - sslCafile: string, location of Certificate Authority file on local filesystem which should be used with
     *   the 'sslVerifyPeer' option to authenticate the identity of the remote peer.
     * - sslCapath: string, a directory that holds multiple CA certificates.
     * You may set options using keys, which are specific to particular transport, like `[CURLOPT_VERBOSE => true]` in case
     * there is a necessity for it.
     *
     * @param array $options request options.
     * @return $this self reference.
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array request options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns HTTP message raw content.
     *
     * @return string|null raw body.
     */
    public function getContent(): ?string
    {
        return null;
    }

    /**
     * Adds more options to already defined ones.
     * Please refer to [[setOptions()]] on how to specify options.
     *
     * @param array $options additional options
     * @return $this
     */
    public function addOptions(array $options): self
    {
        // `array_merge()` will produce invalid result for cURL options,
        // while `ArrayHelper::merge()` is unable to override cURL options
        foreach ($options as $key => $value) {
            if (is_array($value) && isset($this->options[$key])) {
                $value = ArrayHelper::merge($this->options[$key], $value);
            }
            $this->options[$key] = $value;
        }

        return $this;
    }
}
