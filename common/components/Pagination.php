<?php

namespace app\common\components;

use yii\data\Pagination as YiiPagination;
use Yii;
use yii\web\Request;

class Pagination extends YiiPagination
{

    public $pageSizeLimit = [1, 100];

    /**
     * Creates the URL suitable for pagination with the specified page number.
     * This method is mainly called by pagers when creating URLs used to
     * perform pagination.
     *
     * @param int $page the zero-based page number that the URL should point
     *   to.
     * @param int $pageSize the number of items on each page. If not set, the
     *   value of [[pageSize]] will be used.
     * @param bool $absolute whether to create an absolute URL. Defaults to
     *   `false`.
     *
     * @return string the created URL
     * @see params
     * @see forcePageParam
     */
    public function createUrl($page, $pageSize = null, $absolute = false)
    {
        $page = (int)$page;
        $pageSize = (int)$pageSize;

        if (($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }
        if ($page > 0 || $page == 0 && $this->forcePageParam) {
            $params['page']['offset'] = $page + 1;
        } else {
            unset($params['page']['offset']);
        }
        if ($pageSize <= 0) {
            $pageSize = $this->getPageSize();
        }
        if ($pageSize != $this->defaultPageSize) {
            $params['page']['limit'] = $pageSize;
        } else {
            unset($params['page']['limit']);
        }
        $params[0] = $this->route === null ? Yii::$app->controller->getRoute() : $this->route;
        $urlManager = $this->urlManager === null ? Yii::$app->getUrlManager() : $this->urlManager;
        if ($absolute) {
            $res = $urlManager->createAbsoluteUrl($params);
            $res = str_replace('%5B', '[', $res);
            $res = str_replace('%5D', ']', $res);
            return $res;
        }

        return $urlManager->createUrl($params);
    }
}
