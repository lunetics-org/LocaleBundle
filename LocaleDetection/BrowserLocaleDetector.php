<?php

namespace Lunetics\LocaleBundle\LocaleDetection;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Class for the Locale Detector
 *
 * Detects and sets the Locale
 */
class BrowserLocaleDetector implements LocaleDetectorInterface
{
    /**
     * @var array
     */
    private $allowedLanguages = array();
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

    private $request;

    private $router;

    private $detectedLocale;

    /**
     * @var boolean
     */
    private $cookieListenerisAdded = false;

    /**
    * {@inheritDoc}
    */
    public function __construct($defaultLocale = 'en', 
                                array $allowedLanguages = null,
                                Request $request,
                                Response $response = null,
                                RouterInterface $router = null,
                                LoggerInterface $logger = null)
    {
        $this->allowedLanguages = $allowedLanguages;
        $this->defaultLocale = $defaultLocale;
        $this->request = $request;
        $this->response = $response;
        $this->router = $router;
        $this->logger = $logger;
    }
    

    

    /**
     *  The Request Listener which sets the locale
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     *
     * @return void
     */
    public function processLocaleDetection()
    {

        $request = $this->request;
        /* @var $request \Symfony\Component\HttpFoundation\Request */

        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

       if($session->has('setLocaleCookie') || !$request->cookies->has('locale')) {
          $session->remove('setLocaleCookie');
       }

        // Check if the locale has been identified, no repeating locale checks on subsequent requests needed
        if ($session->has('localeIdentified')) {
            $request->setLocale($session->get('localeIdentified'));
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Locale already Identified : [ %s ]', $session->get('localeIdentified')));
                $this->detectedLocale = $session->get('localeIdentified');
            }
            return;
        }

        // Get the Preferred Language from the Browser
        $preferredLanguage = $request->getPreferredLanguage();
        $providedLanguages = $request->getLanguages();

        if (!$preferredLanguage OR count($providedLanguages) === 0) {
            //$preferredLanguage = $this->defaultLocale; // This one is skipped as the next detector has to take over
        } else if (!in_array(\Locale::getPrimaryLanguage($preferredLanguage), $this->allowedLanguages) && !empty($this->allowedLanguages)) {
            $allowedLanguages = $this->allowedLanguages;
            $map = function($v) use ($allowedLanguages)
            {
                if (in_array(\Locale::getPrimaryLanguage($v), $allowedLanguages)) {
                    return true;
                }
            };
            $result = array_values(array_filter($providedLanguages, $map));
            if (!empty($result)) {
                $preferredLanguage = $result[0];
            } else {
                // We skip this one as the next detector has to take over
                // $preferredLanguage = $this->defaultLocale;
            }
        }
        
        $request->setLocale($preferredLanguage);
        $session->set('localeIdentified', $preferredLanguage);
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Locale detected: [ %s ]', $request->getLocale()));
            $this->detectedLocale = $preferredLanguage;
        }
        //$this->addCookieResponseListener();
        return;
    }

    /**
    * {@inheritDoc}
    */
    public function isLocaleDetected()
    {
        return(!empty($this->detectedLocale));
    }

    /**
    * {@inheritDoc}
    */
    public function getDetectedLocale()
    {
        return $this->detectedLocale;
    }

    /**
    * {@inheritDoc}
    */
    public function setDefaultLocale($locale)
    {

    }
}