<?php

namespace app\modules\asur\components;

use app\common\soap\MessageParser;
use app\modules\asur\models\CoordinateSendTaskStatusesMessage;
use yii\base\Component;
use yii\base\UserException;

class ASUR extends Component
{
    /**
     * Путь до схемы xsd
     *
     * @var string
     */
    public $schemePath;

    /**
     * Обработка ответа от АС УР
     *
     * @param string $xml
     */
    public function handleRequest(string $xml)
    {
        try {
            //$this->validateXML($xml);

            $message = new CoordinateSendTaskStatusesMessage([
                'data' => MessageParser::xmlAsArray($xml)
            ]);

            $message->process();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), 'asur');
        }
    }

    /**
     * @param string $xml
     *
     * @throws UserException
     */
    protected function validateXML($xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        try {
            if (!file_exists(\Yii::getAlias($this->schemePath))) {
                throw new \Exception('Не найден файл ' .\Yii::getAlias($this->schemePath));
            }

            $valid = $dom->schemaValidate(\Yii::getAlias($this->schemePath));
        } catch (\Exception $e) {
            throw new UserException('Передаваемые параметры в xml не соответствуют схеме: ' . $e->getMessage(), 400);
        }

        if (!$valid) {
            throw new UserException('Передан невалидный xml', 400);
        }
    }
}
