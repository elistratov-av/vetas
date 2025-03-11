<?php

use app\commands\migrate\Migration;

/**
 * Class m210430_194001_delete_from_gov_services_params
 */
class m210430_194001_delete_from_gov_services_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('gov_services_params', 'id_param = 27');
        $this->delete('gov_services_params', 'id_param = 433');
        $this->delete('gov_services_params', 'id_param = 492');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $queryList = [
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 5, false, true, null, null, now(), now(), 1, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 386, false, true, null, null, now(), now(), 1, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 387, false, true, null, null, now(), now(), 1, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 388, false, true, null, null, now(), now(), 1, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 389, false, true, null, null, now(), now(), 1, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 390, false, true, null, null, now(), now(), 1, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (27, 391, false, true, null, null, now(), now(), 1, false, true);",

            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 5, false, true, null, null, now(), now(), 2, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 386, false, true, null, null, now(), now(), 2, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 387, false, true, null, null, now(), now(), 2, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 388, false, true, null, null, now(), now(), 2, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 389, false, true, null, null, now(), now(), 2, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 390, false, true, null, null, now(), now(), 2, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (433, 391, false, true, null, null, now(), now(), 2, false, true);",

            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 5, false, true, null, null, now(), now(), 3, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 386, false, true, null, null, now(), now(), 3, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 387, false, true, null, null, now(), now(), 3, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 388, false, true, null, null, now(), now(), 3, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 389, false, true, null, null, now(), now(), 3, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 390, false, true, null, null, now(), now(), 3, false, true);",
            "INSERT INTO public.gov_services_params (id_param, id_service, req_in, req_out, created_by, updated_by, created_at, updated_at, sort_by, flag_in, flag_out) VALUES (492, 391, false, true, null, null, now(), now(), 3, false, true);",
        ];

        foreach ($queryList as $query) {
            Yii::$app->db->createCommand($query)->execute();
        }
    }
}
