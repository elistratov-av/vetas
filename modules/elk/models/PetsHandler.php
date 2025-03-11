<?php

namespace app\modules\elk\models;

use app\modules\elk\exceptions\ELKException;
use app\modules\elk\types\Animal;
use app\modules\elk\types\Animals;
use app\modules\elk\types\Owner;
use app\modules\elk\types\Response;

class PetsHandler
{
    /**
     * @param Owner $owner
     * @param Animals $animals
     * @return Response
     * @return Response
     * @throws \Throwable
     */
    public static function save($owner, $animals)
    {
        $response = new Response();

        try {
            \Yii::$app->db->transaction(function() use($owner, $animals) {
                // Преобразуем массив животных в массив объектов Animal
                $animalObjects = [];
                if (is_array($animals->Animal)) {
                    foreach ($animals->Animal as $animal) {
                        $animalObjects[] = new Animal((array)$animal);
                    }
                } else {
                    $animalObjects[] = new Animal((array)$animals->Animal);
                }

                $savePet = new SavePet(['owner' => $owner, 'animals' => $animalObjects]);
                if (!$savePet->validate()) {
                    throw new ELKException(self::prepareErrors($savePet->getErrorSummary(true)));
                }
                $savePet->handle();
            });

            $response->message = 'Ok';
        } catch (\Exception $e) {
            $response->message = 'Fail: ' . $e->getMessage();
        }

        return $response;
    }

    /**
     * @param string $id
     * @return Response
     * @throws \Throwable
     */
    public static function delete(string $id)
    {
        $response = new Response();
        try {
            \Yii::$app->db->transaction(function() use($id) {
                $delete = new DeletePet(['id' => $id]);
                if (!$delete->validate()) {
                    throw new ELKException(self::prepareErrors($delete->getErrorSummary(true)));
                }
                $delete->handle();
            });
            $response->message = 'Ok';
        } catch (\Exception $e) {
            $response->message = 'Fail: ' . $e->getMessage();
        }

        return $response;
    }

    /**
     * @param array $errors
     * @param string $message
     * @return string
     */
    public static function prepareErrors(array $errors, $message = '')
    {
        return "{$message}\n- " . implode("\n- ", $errors) . "\n";
    }
}
