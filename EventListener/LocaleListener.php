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
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\LocaleBundleEvents;
use Lunetics\LocaleBundle\Matcher\BestLocaleMatcher;

/**
 * Locale Listener
 *
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var string Default framework locale
     */
    private $defaultLocale;

    /**
     * @var LocaleGuesserManager
     */
    private $guesserManager;

    /**
     * @var BestLocaleMatcher
     */
    private $bestLocaleMatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var boolean
     */
    private $disableVaryHeader = false;

    /**
     * @var string
     */
    private $excludedPattern;

    /**
     * Construct the guessermanager
     *
     * @param string               $defaultLocale  Framework default locale
     * @param LocaleGuesserManager $guesserManager Locale Guesser Manager
     * @param LoggerInterface      $logger         Logger
     */
    public function __construct($defaultLocale = 'en', LocaleGuesserManager $guesserManager, BestLocaleMatcher $bestLocaleMatcher = null, LoggerInterface $logger = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->guesserManager = $guesserManager;
        $this->bestLocaleMatcher = $bestLocaleMatcher;
        $this->logger = $logger;
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
        $request = $event->getRequest();

        if ($this->excludedPattern && preg_match(sprintf('#%s#', $this->excludedPattern), $request->getPathInfo())) {
            return;
        }

        $request->setDefaultLocale($this->defaultLocale);

        $manager = $this->guesserManager;
        $locale = $manager->runLocaleGuessing($request);

        if ($locale && $this->bestLocaleMatcher) {
            $locale = $this->bestLocaleMatcher->match($locale);
        }
        
        if ($locale) {
            $this->logEvent('Setting [ %s ] as locale for the (Sub-)Request', $locale);
            $request->setLocale($locale);
            $request->attributes->set('_locale', $locale);

            if (($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST || $request->isXmlHttpRequest())
                && ($manager->getGuesser('session') || $manager->getGuesser('cookie'))
            ) {
                $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $locale);
                $this->dispatcher->dispatch(LocaleBundleEvents::onLocaleChange, $localeSwitchEvent);
            }
        }
    }

    /**
     * This Listener adds a vary header to all responses.
     *
     * @param FilterResponseEvent $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onLocaleDetectedSetVaryHeader(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$this->disableVaryHeader && $event->isMasterRequest()) {
            $response->setVary('Accept-Language', false);
        }
        return $response;
    }
    /**
     * DI Setter for the EventDispatcher
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInteface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param boolean $disableVaryHeader
     */
    public function setDisableVaryHeader($disableVaryHeader)
    {
        $this->disableVaryHeader = $disableVaryHeader;
    }

    /**
     * @param string $excludedPattern
     */
    public function setExcludedPattern($excludedPattern)
    {
        $this->excludedPattern = $excludedPattern;
    }

    /**
     * Log detection events
     *
     * @param string $logMessage
     * @param string $parameters
     */
    private function logEvent($logMessage, $parameters = null)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf($logMessage, $parameters));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the Router to have access to the _locale and before the Symfony LocaleListener
            KernelEvents::REQUEST => array(array('onKernelRequest', 24)),
            KernelEvents::RESPONSE => array('onLocaleDetectedSetVaryHeader')
        );
    }
}
