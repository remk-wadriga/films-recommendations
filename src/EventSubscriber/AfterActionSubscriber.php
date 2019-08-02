<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 12.09.2018
 * Time: 17:38
 */

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AfterActionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            //KernelEvents::RESPONSE => 'handleResponse',
            KernelEvents::EXCEPTION => ['handleException', 100],
        ];
    }

    public function handleResponse(ResponseEvent $event)
    {
        dd($event->getResponse());
    }

    public function handleException(ExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof HttpException) && $exception instanceof SymfonyHttpException) {
            $exception = new HttpException($exception->getMessage(), 0, $exception);
        }

        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'data' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                //'trace' => $exception->getTrace(),
            ],
        ];

        $response = new JsonResponse($data, $exception->getStatusCode());
        if (!empty($exception->getHeaders())) {
            $response->headers->add($exception->getHeaders());
        }
        $response->headers->add([
            'access-control-expose-headers' => 'X-Debug-Token,X-Debug-Token-Link',
        ]);
        $event->setResponse($response);
    }
}