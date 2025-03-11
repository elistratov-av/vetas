<?php


namespace app\modules\animalid\models;

use app\modules\animalid\models\db\ErrorsModel;
use app\modules\animalid\models\db\Id_Map;
use app\modules\animalid\skeletons\exceptions\FormatIntegrationException;
use app\modules\animalid\skeletons\exceptions\IntegrationException;
use app\modules\animalid\skeletons\exceptions\ValidationIntegrationException;
use app\modules\animalid\skeletons\interfaces\IntegrationModelInterface;

class DataConsumer
{
    public static $RESULT_UPDATED = 'updated';
    public static $RESULT_CREATED = 'created';
    public static $RESULT_MERGED = 'merged';
    public static $RESULT_ERROR = 'error';
    public static $RESULT_FILTERED = 'filtered';
    private $model;

    /**
     * @param string $dataIn
     * @return array
     * @throws \yii\db\Exception
     */
    public function consume($dataIn)
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            //преобразует в json
            $data = $this->decodeData($dataIn);

            $data = $this->validateMessageData($data);

            //зачищаем данные
            $data['data'] = array_map(function ($x) {
                return trim($x);
            }, $data['data']);

            $data = DataPreProcessor::filterMessageData($data, DataPreProcessor::DIRECTION_IN);
            if (!$data) {
                return [
                    'result' => self::$RESULT_FILTERED
                ];
            }

            $data = DataPreProcessor::convertMessageData($data, DataPreProcessor::DIRECTION_IN);

            //по типу сообщения создает модель
            $this->model = $this->createModelFromData($data);

            $our_id = Id_Map::getOurByTheir($data['id'], $data['type']);

            $this->model->load(array_merge($data['data'], ['id' => $data['id']]));
            $valid = $this->model->validate();
            if (!$valid) {
                $errors = $this->model->getErrorSummary(true);
                throw new ValidationIntegrationException($data, implode(' ', array_values($errors)));
            }
            if ($our_id) {
                $this->model->update();
                $transaction->commit();
                return [
                    'result' => self::$RESULT_UPDATED
                ];
            } elseif ($found = $this->model->findIdenty()) {
                $this->model->merge($found);
                $transaction->commit();
                return [
                    'result' => self::$RESULT_MERGED
                ];
            } else {
                $this->model->save();
                $transaction->commit();
                return [
                    'result' => self::$RESULT_CREATED
                ];
            }
        } catch (IntegrationException $e) {
            $transaction->rollBack();
            $errorModel = new ErrorsModel();
            if ($e->object) {
                $errorModel->data = isset($e->object['data']) ? $e->object['data'] : null;
                $errorModel->type = isset($e->object['type']) ? $e->object['type'] : null;
                $errorModel->action = isset($e->object['action']) ? $e->object['action'] : null;
            }
            $errorModel->reason = $e->getMessage();
            $errorModel->save();
            return [
                'result' => self::$RESULT_ERROR,
                'message' => $e->getMessage(),
                'error id' => $errorModel->getPrimaryKey()
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $errorModel = new ErrorsModel();
            if (is_string($dataIn)) {
                $errorModel->data = $dataIn;
            }
            $errorModel->reason = $e->getMessage();
            $errorModel->save();
            return [
                'result' => self::$RESULT_ERROR,
                'message' => $e->getMessage(),
                'error id' => $errorModel->getPrimaryKey()
            ];
        }
    }

    /**
     * @param $data
     * @return array|mixed
     * @throws FormatIntegrationException
     */
    private function decodeData($data)
    {
        $data = json_decode($data, true);
        if (!$data) {
            throw new FormatIntegrationException('Ошибка декодирования json: ' . json_last_error_msg());
        }

        //приводим строки ключи массива в нижний регистр
        $data = array_change_key_case($data, CASE_LOWER);
        $data['data'] = array_change_key_case($data['data'], CASE_LOWER);

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * @throws FormatIntegrationException
     */
    private function validateMessageData($data)
    {
        if ($data == null) {
            throw new FormatIntegrationException($data, 'Запрос не может быть пустым');
        }

        if (!isset($data['id']) || $data['id'] == null) {
            throw new FormatIntegrationException(null, 'Не задан id');
        }

        if (!isset($data['data']) || $data['data'] == null) {
            throw new FormatIntegrationException(null, 'Не задан data');
        }

        if (!isset($data['type']) || $data['type'] == null) {
            throw new FormatIntegrationException(null, 'Не задан type');
        }

        return $data;
    }



    /**
     * @param $data
     * @return IntegrationModelInterface
     * @throws FormatIntegrationException
     */
    private function createModelFromData($data)
    {
        $model = null;
        switch ($type = $data['type']) {
            case 'pet' :
                $model = new PetsModel();
                break;
            case 'company' :
                $model = new CompanysModel();
                break;
        }

        if (!$model) {
            throw new FormatIntegrationException('Ошибка типа сообщения: ' . $type);
        }

        return $model;
    }
}
