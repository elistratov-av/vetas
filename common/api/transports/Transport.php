<?php

declare(strict_types = 1);

namespace app\common\api\transports;

use app\common\api\messages\Request;
use app\common\api\messages\Response;

interface Transport
{
    /**
     * Performs given request.
     *
     * @param Request $request request to be sent.
     * @param string $response Response class name.
     * @return Response response instance.
     */
    public function send(Request $request, string $response): Response;
}
