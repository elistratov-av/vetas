<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\common\components\entity\EntityResourceCache;

/**
 * Class EntityCacheController
 * @package app\commands
 */
class EntityCacheController extends Controller
{
    /**
     * @return int
     */
    public function actionFlush()
    {
        $result = EntityResourceCache::flush();

        return $result === true ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * @param string $tag
     * @return int
     */
    public function actionInvalidateTag($tag)
    {
        EntityResourceCache::invalidate($tag);

        return ExitCode::OK;
    }
}
