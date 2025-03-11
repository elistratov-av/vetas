<?php

declare(strict_types = 1);

namespace app\common\api\transports;

use app\common\api\messages\Request;
use app\common\api\messages\Response;
use yii;
use yii\base\Exception;

/**
 * CurlTransport sends HTTP messages using [Client URL Library (cURL)](http://php.net/manual/en/book.curl.php)
 */
class CurlTransport implements Transport
{
    /**
     * @inheritdoc
     */
    public function send(Request $request, string $response): Response
    {
        $curlOptions = $this->prepare($request);
        $curlResource = $this->initCurl($curlOptions);

        $headers = [];
        $this->setHeaderOutput($curlResource, $headers);

        $content = curl_exec($curlResource);

        // check cURL error
        $errorNumber = curl_errno($curlResource);
        $errorMessage = curl_error($curlResource);

        curl_close($curlResource);

        if ($errorNumber > 0) {
            throw new Exception('Curl error: #' . $errorNumber . ' - ' . $errorMessage);
        }

        return Yii::createObject([
            'class' => $response,
            'headers' => $headers,
            'payload' => $content,
        ]);
    }

    /**
     * Prepare request for execution, creating cURL resource for it.
     *
     * @param Request $request request instance.
     * @return array cURL options.
     */
    private function prepare(Request $request): array
    {
        $curlOptions = $this->composeCurlOptions($request->getOptions());

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                break;
            default:
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }

        $content = $request->getContent();
        $headers = $request->composeHeaderLines();

        if ($method === 'HEAD') {
            $curlOptions[CURLOPT_NOBODY] = true;
        }
        if ($content !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $content;
        }
        if (count($headers)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        }
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;

        return $curlOptions;
    }

    /**
     * Initializes cURL resource.
     *
     * @param array $curlOptions cURL options.
     * @return resource prepared cURL resource.
     */
    private function initCurl(array $curlOptions)
    {
        $curlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }

        return $curlResource;
    }

    /**
     * Composes cURL options from raw request options.
     *
     * @param array $options raw request options.
     * @return array cURL options, in format: [curl_constant => value].
     */
    private function composeCurlOptions(array $options): array
    {
        $optionMap = [
            'protocolVersion' => CURLOPT_HTTP_VERSION,
            'maxRedirects' => CURLOPT_MAXREDIRS,
            'sslCapath' => CURLOPT_CAPATH,
            'sslCafile' => CURLOPT_CAINFO,
            'sslLocalCert' => CURLOPT_SSLCERT,
            'sslLocalPk' => CURLOPT_SSLKEY,
            'sslPassphrase' => CURLOPT_SSLCERTPASSWD,
        ];

        $curlOptions = [];
        foreach ($options as $key => $value) {
            if (is_int($key)) {
                $curlOptions[$key] = $value;
                continue;
            }
            if (array_key_exists($key, $optionMap)) {
                $curlOptions[$optionMap[$key]] = $value;
                continue;
            }
            $key = strtoupper($key);
            if (strpos($key, 'SSL') === 0) {
                $key = substr($key, 3);
                $constantName = 'CURLOPT_SSL_' . $key;
                if (!defined($constantName)) {
                    $constantName = 'CURLOPT_SSL' . $key;
                }
            } else {
                $constantName = 'CURLOPT_' . strtoupper($key);
            }
            $curlOptions[constant($constantName)] = $value;
        }

        return $curlOptions;
    }

    /**
     * Setup a variable, which should collect the cURL response headers.
     *
     * @param resource $curlResource cURL resource.
     * @param array $output variable, which should collection headers.
     */
    private function setHeaderOutput($curlResource, array &$output): void
    {
        curl_setopt($curlResource, CURLOPT_HEADERFUNCTION, static function ($resource, $headerString) use (&$output) {
            $header = trim($headerString, "\n\r");
            if ($header !== '') {
                $output[] = $header;
            }

            return mb_strlen($headerString, '8bit');
        });
    }
}
