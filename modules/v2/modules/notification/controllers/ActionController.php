<?php


namespace app\modules\v2\modules\notification\controllers;

use app\modules\v2\modules\BaseController;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

class ActionController extends BaseController
{
    public function behaviors(): array
    {
        return [
            'contentNegotiator' => parent::behaviors()['contentNegotiator'],
        ];
    }


    /**
     * @throws ForbiddenHttpException
     * @throws Exception
     */
    public function actionCreateForVetSpecGos()
    {
        if (!empty($_ENV['CRON_TOKEN'])) {
            $cronToken = $_ENV['CRON_TOKEN'];
        } else {
            $cronToken = '8225df8a-b04b-49ce-873b-c683c7176c25';
        }
        $cronTokenParam = $_GET['cronToken'];
        if ($cronTokenParam != $cronToken) {
            throw new ForbiddenHttpException("Invalid cronToken");
        }

        if (!empty($_GET['date'])) {
            $date = $_GET['date'];
        } else {
            $date = date(' Y-m-d');
        }

        $query_requests = new Query();
        $query_requests = $query_requests
            ->select([
                'u.id as id_user', 's.id_organization',
                'ph.id as id_hotel', 'aa.item_name as role',
                'phr.id as id_room', 'phr2.id as id_request',
                'date(phr2.date_from) as date_from',
                'p.name as animal_nickname',
                'concat_ws(\' \', po.i_fio, po.o_fio, po.f_fio) as owner_fio',
                'phao.phone_number as owner_phone',
                'phrs.name as status_name',
                'phr.name as room_name',
                'ph.name as hotel_name'
            ])
            ->distinct(true)
            ->from('public.users u')
            ->leftJoin('specialists s', 'u.id = s.id_user')
            ->leftJoin('auth_assignment aa', 'aa.id_specialist = s.id')
            ->leftJoin('pet_hotel ph', 'ph.id_organization = s.id_organization')
            ->leftJoin('pet_hotel_room phr', 'phr.id_pet_hotel = ph.id')
            ->leftJoin('pet_hotel_request phr2', 'phr2.id_room = phr.id')
            ->leftJoin('pets p', 'p.id = phr2.id_animal')
            ->leftJoin('pet_hotel_animal_owner phao', 'phao.id_owner = phr2.id_owner')
            ->leftJoin('pet_owners po', 'po.id = phr2.id_owner')
            ->leftJoin('pet_hotel_request_status  phrs', 'phrs.id = phr2.id_status')
            ->where(['and',
                ['not', ['s.id_organization' => null]],
                ['not', ['ph.id' => null]],
                ['not', ['phr.id' => null]],
                ['not', ['phr2.id' => null]],
                ['aa.item_name' => 'vetSpecGos'],
                ['date(phr2.date_from)' => new Expression(
                    'date(\'' . $date . '\')'
                )]
            ]);
        $requests = $query_requests->all();
        if (!empty($requests)) {
            $query_notifications = new Query();
            $query_notifications = $query_notifications
                ->select([
                    'title', 'text',
                    'id_user', 'date(created_at) as date_created',
                ])
                ->from('notifications')
                ->where(['date(created_at)' => new Expression(
                    'date(\'' . $date . '\')'
                )]);
            $notifications = $query_notifications->all();
            foreach ($requests as $request) {
                $animal_nickname = $request['animal_nickname'];
                $owner_fio = $request['owner_fio'];
                $owner_phone = $request['owner_phone'];
                $room_name = $request['room_name'];
                $hotel_name = $request['hotel_name'];
                $found = false;
                $title = "Предстоит заезд";
                $text = "Ожидается предстоящее размещение животного $animal_nickname (владелец $owner_fio, тел. - $owner_phone) в помещение $room_name зоогостиницы $hotel_name";
                \Yii::info('$text=' . $text);
                foreach ($notifications as $notification) {
                    if ($notification['id_user'] == $request['id_user']) {
                        if ($notification['title'] == $title) {
                            if ($notification['text'] == $text) {
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                if (!$found) {
                    \Yii::info("do insert");
                    $command = \Yii::$app->db->createCommand();
                    $command->insert('public.notifications', [
                        'title' => $title,
                        'text' => $text,
                        'id_user' => $request['id_user'],
                        'read' => false,
                        'created_at' => $date,
                    ])->execute();
                }
            }
        }
        return [
            'result' => 'success',
        ];
    }

}