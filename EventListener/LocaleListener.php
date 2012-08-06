<?php

namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;

class LocaleListener
{
    private $defaultLocale;
    
    private $guesserManager;
    
    private $logger;
    
    public function __construct($defaultLocale = 'en', LocaleGuesserManager $guesserManager, LoggerInterface $logger)
    {
        $this->defaultLocale = $defaultLocale;
        $this->guesserManager = $guesserManager;
        $this->logger = $logger;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {        
        if($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            $this->logEvent('Request is not a "MASTER_REQUEST" : SKIPPING...');
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
    
    /**
     * DI Setter for the EventDispatcher
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    /**
     * Method to add the ResponseListener which sets the cookie. Should only be called once
     *
     * @see also http://slides.seld.be/?file=2011-10-20+High+Performance+Websites+with+Symfony2.html#45
     *
     * @return void
     */
    public function addCookieResponseListener()
    {
        if($this->cookieListenerisAdded !== true) {
            $this->dispatcher->addListener(
                KernelEvents::RESPONSE,
                array($this, 'onResponse')
            );
        }
        $this->cookieListenerisAdded = true;
    }
    
    public function onResponse(Event  $event)
    {
        $response = $event->getResponse();
        /* @var $response \Symfony\Component\HttpFoundation\Response */

        $session = $event->getRequest()->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        $response->headers->setCookie(new Cookie('locale', $session->get('localeIdentified')));
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Locale Cookie set to: [ %s ]', $session->get('localeIdentified')));
        }
    }
    
    /**
     * Log detection events
     * 
     * @param type $logMessage
     * @param type $parameters
     */
    private function logEvent($logMessage, $parameters = null)
    {
        if (null !== $this->logger) {
                $this->logger->info(sprintf($logMessage, $parameters));
            }
    }
}