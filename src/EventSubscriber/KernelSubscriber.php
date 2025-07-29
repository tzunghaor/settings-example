<?php

namespace App\EventSubscriber;

use App\Middleware\CookieSeparatedConnectionDriver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event):void
    {
        $dbCookieName = CookieSeparatedConnectionDriver::COOKIE_NAME;
        $dbCookieValue = $event->getRequest()->cookies->get($dbCookieName);
        if ($dbCookieValue) {
            $event->getResponse()->headers->setCookie(Cookie::create($dbCookieName, $dbCookieValue));
        }
    }
}