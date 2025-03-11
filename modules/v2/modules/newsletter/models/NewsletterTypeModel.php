<?php

namespace app\modules\v2\modules\newsletter\models;

use app\models\db\NewsletterType;
use yii\web\BadRequestHttpException;

class NewsletterTypeModel
{
    public function get()
    {
        return NewsletterType::find()->all();
    }

    public function getNewsletter()
    {
        return NewsletterType::find()->where(['not like','type', 'newsletter_reception'])->all();
    }
}
