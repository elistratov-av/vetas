<?php

namespace app\common\components\odopm;

use yii\helpers\FileHelper;

/**
 * Class ODOPMSoapClient
 * @package app\common\components\odopm
 *
 * @method getAllDict
 * @method getCatalogItems($params)
 * @method getCatalogItemsNew
 * @method getCatalogList
 * @method getCatalogSpec($params)
 * @method getCatalogSpecNew($params)
 * @method getCatalogStat
 * @method getDictItem($params)
 * @method getDictItemV2($params)
 * @method getUserBySession
 * @method setDataIn
 */
class ODOPMSoapClient extends \SoapClient
{
    public function __doRequest ($request, $location, $action, $version, $one_way = 0)
    {
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);

        $this->log($request, $response);

        return $response;
    }

    /**
     * @param string $request
     * @param string $response
     * @throws \yii\base\Exception
     */
    private function log($request, string $response): void
    {
        if (YII_DEBUG === false) {
            return;
        }

        $dir = \Yii::getAlias('@runtime/logs/odopm');
        if (FileHelper::createDirectory($dir)) {
            $time = microtime(true);
            file_put_contents($dir . '/' . 'request_' . $time . '.xml', $request);
            file_put_contents($dir . '/' . 'response_' . $time . '.xml', $response);
        }
    }
}
