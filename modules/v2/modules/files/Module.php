<?php


namespace app\modules\v2\modules\files;

use Yii;
use app\modules\v2\modules\files\models\FilesEventHandler;
use app\modules\v2\modules\files\models\FilesModel;
use yii\base\Module as YiiModule;

class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\v2\modules\files\controllers';

    public function init()
    {
        parent::init();

        Yii::$app->on(
            FilesModel::EVENT_FILE_ATTACHED,
            'app\modules\v2\modules\files\models\FilesEventRouter::eventFileAttached'
        );

        Yii::$app->on(
            FilesModel::EVENT_FILE_DELETED,
            'app\modules\v2\modules\files\models\FilesEventRouter::eventFileDeleted'
        );
    }
}
