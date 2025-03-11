<?php

declare(strict_types = 1);

namespace app\modules\adminv\integration\bi;

use app\common\api\BaseApi;
use app\common\api\messages\Request;
use app\common\api\messages\Response;
use yii\base\InvalidConfigException;

/**
 * Class Api is implementation of api to retrieve BI reports
 */
class Api extends BaseApi
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();
        if (!$this->username || !$this->password) {
            throw new InvalidConfigException('Please provide service credentials.');
        }
    }

    /**
     * @inheritDoc
     */
    public function sent(Request $rq): Response
    {
        $rq->addOptions([
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_UNRESTRICTED_AUTH => true,
            CURLOPT_USERPWD => implode(':', [$this->getUsername(), $this->getPassword()]),
        ]);

        return parent::sent($rq);
    }
}
