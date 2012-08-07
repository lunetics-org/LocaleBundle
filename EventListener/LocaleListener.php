<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use Lunetics\LocaleBundle\Validator\LocaleValidator;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleListener
{
    private $defaultLocale;
    
    private $guesserManager;
    
    private $logger;
    
    private $dispatcher;
    
    private $localeCookie;
    
    private $identifiedLocale;
    
    public function __construct($defaultLocale = 'en', LocaleGuesserManager $guesserManager, LocaleCookie $localeCookie, LoggerInterface $logger = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->guesserManager = $guesserManager;
        $this->logger = $logger;
        $this->localeCookie = $localeCookie;
    }
    
    /**
     * Called at the "kernel.request" event
     * 
     * Call the LocaleGuesserManager to guess the locale
     * by the activated guessers
     * 
     * Sets the identified locale as default locale to the request
     * 
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {        
        if($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            $this->logEvent('Request is not a "MASTER_REQUEST" : SKIPPING...');
            return;
        }
        
        $request = $event->getRequest();
        $manager = $this->guesserManager;
        if($locale = $manager->runLocaleGuessing($request)){
            $validator = new LocaleValidator();
            $validator->validate($locale);
            $this->logEvent('Setting [ %s ] as defaultLocale for the Request', $locale);
            $request->setDefaultLocale($locale);
            $this->identifiedLocale = $locale;
            if($this->localeCookie->setCookieOnDetection()){
                $this->addCookieResponseListener();
            }
            return;
        }
        $request->setDefaultLocale($this->defaultLocale);
    }
    
    /**
     * DI Setter for the EventDispatcher
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    /**
     * Method to add the ResponseListener which sets the cookie. Should only be called once
     */
    public function addCookieResponseListener()
    {
            $this->dispatcher->addListener(
                KernelEvents::RESPONSE,
                array($this, 'onResponse')
            );
    }
    
    /**
     * Called at the kernel.response event to attach the cookie to the request
     * 
     * @param Event $event
     */
    public function onResponse(Event $event)
    {
        $response = $event->getResponse();
        $cookie = $this->localeCookie->getLocaleCookie($this->identifiedLocale);
        $response->headers->setCookie($cookie);
        $this->logEvent('Locale Cookie set to [ %s ]', $this->identifiedLocale);
        return $response;
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