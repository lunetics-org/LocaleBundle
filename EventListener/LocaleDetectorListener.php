<?php

namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
     *  OnRequest Listener
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

        // Which handler should be used for setlocale. session for pre 2.1.0-DEV and request for post 2.1.0-DEV
        if (version_compare(Kernel::VERSION, '2.1.0-DEV') >= 0) {
            $handler = $request;
        } else {
            $handler = $session;
        }

        // Checks Cookie if a locale has been selected manually, then just set the Locale from the Cookie and return
        if ($request->cookies->has('localeManually')) {
            $handler->setLocale($request->cookies->get('localeManually'));
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Language Locale manually get from cookie, value: [ %s ]', $session->getLocale()));
            }
            return;
        }

        // Check if the locale has been identified, no repeating locale checks on subsequent requests needed
        if ($session->has('localeIdentified')) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Language Locale already Identified : [ %s ]', $session->get('localeIdentified')));
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


        // Checks the Session var 'localeManually' if  no locale has been selected manually
        if ($session->get('localeManually') !== true) {
            $handler->setLocale($preferredLanguage);
            $session->set('localeIdentified', $preferredLanguage);
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Language Locale Autoset to: [ %s ]', $session->getLocale()));
            }
            return;
        }
    }

    public function onResponse(Event  $event)
    {
        $request = $event->getRequest();
        /* @var $request \Symfony\Component\HttpFoundation\Request */

        $response = $event->getResponse();
        /* @var $request \Symfony\Component\HttpFoundation\Response */

        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        if (!$request->cookies->has('locale')) {
            $response->headers->setCookie(new Cookie('locale', $session->getLocale(), '2037-01-01'));
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Language Locale Cookie set to: [ %s ]', $session->getLocale()));
            }
        }

        if ($session->has('localeManually')) {
            $response->headers->setCookie(new Cookie('localeManually', $session->getLocale(), '2037-01-01'));
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Language Locale Cookie manually set to: [ %s ]', $session->getLocale()));
            }
        }
    }
}