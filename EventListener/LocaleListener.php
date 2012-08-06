<?php

namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;

class LocaleListener
{
    private $defaultLocale;
    
    private $guesserManager;
    
    public function __construct($defaultLocale = 'en', LocaleGuesserManager $guesserManager)
    {
        $this->defaultLocale = $defaultLocale;
        $this->guesserManager = $guesserManager;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {        
        if($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        
        $request = $event->getRequest();
        $manager = $this->guesserManager;
        if($locale = $manager->runLocaleGuessing($request)){
            $request->setDefaultLocale($locale);
            return;
        }
        $request->setDefaultLocale($this->defaultLocale);
    }
}