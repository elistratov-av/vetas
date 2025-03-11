<?php

namespace app\controllers;

use PhpOffice\PhpWord\Reader\HTML;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class DocumentationController extends Controller
{
    /**
     * Displays Swagger UI.
     *
     * @return string
     */
    public function actionIndex()
    {
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <title>Документация для разработчика</title>
            <link rel="stylesheet" type="text/css" href="/docs/swagger-ui.css" />
            <link rel="stylesheet" type="text/css" href="/docs/index.css" />
            <link rel="shortcut icon" href="/images/favicon.svg" />
        </head>

        <body>
            <header class="sw-header">
                <div class="logo">
                    <img src="/docs/logo.svg">
                </div>
            </header>
            <div id="swagger-ui"></div>
            <script src="/docs/swagger-ui-bundle.js" charset="UTF-8"> </script>
            <script src="/docs/swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
            <script src="/docs/swagger-initializer.js" charset="UTF-8"> </script>
        </body>
        </html>
        HTML;
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $html;
    }
}
