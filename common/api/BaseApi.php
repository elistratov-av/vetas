<?php

declare(strict_types = 1);

namespace app\common\api;


use app\common\api\events\RequestEvent;
use app\common\api\events\ResponseEvent;
use app\common\api\messages\Request;
use app\common\api\messages\Response;
use app\common\api\transports\CurlTransport;
use app\common\api\transports\Transport;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

abstract class BaseApi extends Component
{
    /**
     * @event Event an event raised at the beginning of sending request to service.
     */
    public const EVENT_BEFORE_SEND = 'beforeSend';

    /**
     * @event Event an event raised, when service retrieves response.
     */
    public const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @var string The service location
     */
    public $location;

    /**
     * @var int This option defines a timeout in seconds for the connection to the service service.
     */
    public $timeout = 0;

    /**
     * @var Transport|array|string
     */
    public $transport = [
        'class' => CurlTransport::class,
    ];

    /**
     * A list mappings between Requests and Responses
     *
     * @var array
     */
    public $mappings = [];

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!$this->location) {
            throw new InvalidConfigException('Please provide service entrypoint.');
        }
        if (!is_numeric($this->timeout)) {
            throw new InvalidConfigException('Connection timeout must be integer value.');
        }
        $this->transport = Instance::ensure($this->transport, Transport::class);
    }

    /**
     * This method is invoked before sending request from queue to service.
     * The default implementation raises a [[EVENT_BEFORE_SEND]] event.
     * Make sure the parent implementation is invoked so that the event can be raised.
     *
     * @param string $id Request id(can be not unique)
     * @param Request $rq Request object
     */
    public function beforeSend(string $id, Request $rq): void
    {
        $this->trigger(self::EVENT_BEFORE_SEND, new RequestEvent([
            'id' => $id,
            'rq' => $rq,
        ]));
    }

    /**
     * This method is invoked after request to service end.
     * The default implementation raises an [[EVENT_AFTER_SEND]] event.
     * Make sure the parent implementation is invoked so that the event can be raised.
     *
     * @param string $id Request id(can be not unique)
     * @param Response $rs Response object
     */
    public function afterSend(string $id, Response $rs): void
    {
        $this->trigger(self::EVENT_AFTER_SEND, new ResponseEvent([
            'id' => $id,
            'rs' => $rs,
        ]));
    }

    /**
     * @param Request $rq
     * @return Response
     * @throws InvalidConfigException
     */
    public function sent(Request $rq): Response
    {
        $options = ['url' => rtrim($this->getLocation(), '/') . '/' . $rq->getUrl()];
        if ($this->getTimeout()) {
            $options['timeout'] = $this->getTimeout();
        }
        $rq->addOptions($options);

        $id = str_replace('.', '', uniqid('', true));
        $rsClass = $this->compose($rq);
        $this->beforeSend($id, $rq);
        $rs = $this->transport->send($rq, $rsClass);
        $this->afterSend($id, $rs);

        return $rs;
    }

    /**
     * Composes response class name by given request
     *
     * @param Request $rq
     * @return string
     * @throws InvalidConfigException
     */
    private function compose(Request $rq): string
    {
        $rqClass = get_class($rq);
        if (!array_key_exists($rqClass, $this->mappings)) {
            throw new InvalidConfigException(sprintf('Undefined request class "%s". Please register request in mappings.', $rqClass));
        }
        $rsClass = $this->mappings[$rqClass];
        if (!is_subclass_of($rsClass, Response::class)) {
            throw new InvalidConfigException('Response class must implement ' . Response::class);
        }

        return $rsClass;
    }
}
