<?php

namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class for the Locale Detector
 *
 * Detects and sets the Locale
 */
class LocaleDetectorListener
{
    /**
     * @var array
     */
    private $availableLanguages = array();
    /**
     * @var null|\Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $dispatcher;

    /**
     * @var boolean
     */
    private $cookieListenerisAdded = false;

    /**
     * Setup the Locale Listener
     *
     * @param                                                        $defaultLocale      The default Locale
     * @param                                                        $availableLanguages List of available / allowed locales
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger             Logger Interface
     */
    public function __construct($defaultLocale, $availableLanguages, LoggerInterface $logger = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->logger = $logger;
        $this->availableLanguages = $availableLanguages;
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

    /**
     *  The Request Listener which sets the locale
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     *
     * @return void
     */
    public function onRequest(Event $event)
    {

        $request = $event->getRequest();
        /* @var $request \Symfony\Component\HttpFoundation\Request */

        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

       if($session->get('setLocaleCookie') === true || !$request->cookies->has('locale')) {
          $session->remove('setLocaleCookie');
          $this->addCookieResponseListener();
       }

        // Check if the locale has been identified, no repeating locale checks on subsequent requests needed
        if ($session->has('localeIdentified')) {
            $request->setLocale($session->get('localeIdentified'));
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Locale already Identified : [ %s ]', $session->get('localeIdentified')));
            }
            return;
        }

        // Get the Preferred Language from the Browser
        $preferredLanguage = $request->getPreferredLanguage();
        $providedLanguages = $request->getLanguages();

        if (!$preferredLanguage OR count($providedLanguages) === 0) {
            $preferredLanguage = $this->defaultLocale;
        } else if (!in_array(\Locale::getPrimaryLanguage($preferredLanguage), $this->availableLanguages)) {

            $availableLanguages = $this->availableLanguages;
            $map = function($v) use ($availableLanguages)
            {
                if (in_array(\Locale::getPrimaryLanguage($v), $availableLanguages)) {
                    return true;
                }
            };
            $result = array_values(array_filter($providedLanguages, $map));
            if (is_array($result)) {
                $preferredLanguage = $result[0];
            } else {
                $preferredLanguage = $this->defaultLocale;
            }
        }

        $request->setLocale($preferredLanguage);
        $session->set('localeIdentified', $preferredLanguage);
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Locale detected: [ %s ]', $request->getLocale()));
        }
        $this->addCookieResponseListener();
        return;
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
}