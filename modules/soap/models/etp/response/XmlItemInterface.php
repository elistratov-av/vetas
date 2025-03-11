<?php

namespace app\modules\soap\models\etp\response;

interface XmlItemInterface
{
    /**
     * @return string
     */
    public function getXmlTagName() : string;
}
