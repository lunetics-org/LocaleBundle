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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\Session\LocaleSession;

/**
 * Locale Update Listener
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleUpdateListener
{
    /**
     * @var string
     */
    private $locale;
    /**
     * @var LocaleSession
     */
    private $session;
    /**
     * @var LocaleCookie
     */
    private $localeCookie;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $registeredGuessers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Construct the Locale Update Listener
     *
     * @param LocaleCookie    $localeCookie       Locale Cookie
     * @param LocaleSession   $session            Locale Session
     * @param Request         $request            Request
     * @param EventDispatcher $dispatcher         Event Dispatcher
     * @param array           $registeredGuessers List of registered guessers
     * @param LoggerInterface $logger             Logger
     */
    public function __construct(LocaleCookie $localeCookie,
                                LocaleSession $session,
                                Request $request,
                                EventDispatcher $dispatcher,
                                $registeredGuessers = array(),
                                LoggerInterface $logger = null)
    {
        $this->localeCookie = $localeCookie;
        $this->session = $session;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->registeredGuessers = $registeredGuessers;
    }

    /**
     * Processes the locale updates. Adds listener for the cookie and updates the session.
     *
     * @param FilterLocaleSwitchEvent $event
     */
    public function onLocaleChange(FilterLocaleSwitchEvent $event)
    {
        $this->locale = $event->getLocale();
        $this->updateCookie($this->localeCookie->setCookieOnChange());
        $this->updateSession();
    }

    /**
     * Update Cookie Section
     *
     * @param bool $update If cookie should be updated
     */
    public function updateCookie($update)
    {
        if ($this->checkGuesser('cookie')
                && $update === true
                && $this->request->cookies->get($this->localeCookie->getName()) !== $this->locale
        ) {
            $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'updateCookieOnResponse'));

            return true;
        }

        return false;
    }

    /**
     * Event for updating the cookie on response
     *
     * @param FilterResponseEvent $event
     *
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    public function updateCookieOnResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $cookie = $this->localeCookie->getLocaleCookie($this->locale);
        $response->headers->setCookie($cookie);
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Locale Cookie set to [ %s ]', $this->locale));
        }

        return $response;
    }

    /**
     * Update Session section
     */
    public function updateSession()
    {
        if ($this->checkGuesser('session') && $this->session->hasLocaleChanged($this->locale)) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Session var \'%s\' set to [ %s ]', $this->session->getSessionVar(), $this->locale));
            }
            $this->session->setLocale($this->locale);

            return true;
        }

        return false;
    }

    /**
     * Returns if a guesser is
     *
     * @param string $guesser Name of the guesser to check
     *
     * @return bool
     */
    private function checkGuesser($guesser)
    {
        return in_array($guesser, $this->registeredGuessers);
    }
}
