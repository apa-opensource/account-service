<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 30.10.14
 * Time: 21:21
 */

namespace FNC\Bundle\AccountServiceBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EventListener implements EventSubscriberInterface
{
    protected $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     * @return bool
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (stripos($event->getRequest()->getPathInfo(), '/service') === 0) {
            $this->logger->error($exception->getCode() . '-' . $exception->getMessage());

            $event->setResponse(
                new JsonResponse(array(
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ))
            );
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (stripos($event->getRequest()->getPathInfo(), '/service') === 0) {
            $event->setResponse(
                new JsonResponse($event->getControllerResult()));
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', 100),
            KernelEvents::VIEW => array('onKernelView', 100)
        );
    }
}
