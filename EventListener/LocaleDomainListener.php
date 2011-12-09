<?php

namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;


class LocaleDomainListener
{
    protected $tldAllowed;
    protected $tldDefault;
    protected $redirectTo;

    public function __construct($tldAllowed, LoggerInterface $logger = null, Router $router)
    {
        $this->router = $router;
        $this->tldAllowed = $tldAllowed;
        $this->logger = $logger;

        foreach ($tldAllowed as $k) {
            if (isset($k['default'])) {
                $this->tldDefault = $k['domain'];
            }
        }

        if (!$this->tldDefault) {
            throw new RuntimeException(sprintf('Missing Default Domain Parameter in %s', __CLASS__));
        }

    }

    public function onRequest(Event $event)
    {
        $request = $event->getRequest();
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $host = $request->getHost();

        $map = function($tldMap)
        {
            return $tldMap['domain'];
        };

        $redirectTo = function($tldMap, $host)
        {
            foreach ($tldMap as $k) {
                if ($host == $k['domain']) {
                    return $k;
                }
            }
        };

        if ($host != $this->tldDefault && in_array($host, array_map($map, $this->tldAllowed))) {
            $this->redirectTo = $redirectTo($this->tldAllowed, $host);
            //$event->setResponse(new RedirectResponse('http://'. strtolower($this->redirectTo['locale']) .'.' .$this->redirectTo['domain'] . $this->router->generate('admin_homepage')));
            $event->setResponse(new RedirectResponse('http://' . strtolower($this->redirectTo['locale']) . '.' . $this->redirectTo['domain']));
        }
    }
}