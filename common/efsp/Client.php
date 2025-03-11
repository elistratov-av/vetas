<?php

namespace app\common\efsp;

// use yii\authclient\OAuth2;


/**
 *
 * Пример настройки:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'efsp' => [
 *                 'class' => 'app\efsp\Client',
 *                 'clientId' => 'efsp_client_id',
 *                 'clientSecret' => 'efsp_client_secret',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 * 
 * @todo TODO:efsp:token-cache Кэшировать токен
 */
class Client extends \yii\authclient\OAuth2
{
    /**
     * Кеш для хранения токена между запросами
     * 
     * @var \yii\caching\Cache
     */
    protected $cache;


    /**
     * Затребован интерфейсом
     * 
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'efsp';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'ЕФСП';
    }

    /**
     * Добавляет заголовок Authorization
     * 
     * @param \yii\httpclient\Request $request
     * @param \yii\authclient\OAuthToken $accessToken
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $request->addHeaders([
            'Authorization' => "Bearer {$accessToken->getToken()}",
        ]);
    }
}
