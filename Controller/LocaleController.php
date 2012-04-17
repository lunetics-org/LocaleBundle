<?php

namespace Lunetics\LocaleBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Lunetics\LocaleBundle\LocaleEvents;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;

/**
 * Controller for the Switch Locale
 */
class LocaleController
{
    protected $request;
    protected $router;
    protected $session;
    protected $redirectToRoute;
    protected $redirectToUrl;
    protected $useReferrer;
    protected $allowedLanguages;

    /**
     * Constructor for the Locale Switch Servicecontroller
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\HttpFoundation\Session  $session
     * @param                                            $redirectToRoute
     * @param                                            $redirectToUrl
     * @param                                            $useReferrer
     * @param                                            $allowedLanguages
     */
    public function __construct(RouterInterface $router,
                                Session $session,
                                $redirectToRoute,
                                $redirectToUrl,
                                $useReferrer,
                                $allowedLanguages)
    {
        $this->router           = $router;
        $this->session          = $session;
        $this->redirectToRoute  = $redirectToRoute;
        $this->redirectToUrl    = $redirectToUrl;
        $this->useReferrer      = $useReferrer;
        $this->allowedLanguages = $allowedLanguages;
    }

    /**
     * Action for locale switch
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param                                           $_locale The locale to set
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\RedirectResponse
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function switchAction(Request $request, $_locale)
    {
        // Check if the Language is allowed
        if (!in_array(\Locale::getPrimaryLanguage($_locale), $this->allowedLanguages)) {
            throw new NotFoundHttpException('This language is not available');
        }

        /* This logic has been moved to the LocaleSwitchListener

        // tries to detect a Region from the user-provided locales
        $providedLanguages = $request->getLanguages();
        $locales           = array();
        foreach ($providedLanguages as $locale) {
            if (strpos($locale . '_', $_locale) !== false && strlen($locale) > 2) {
                $locales[] = $locale;
            }
        }

        if (count($locales) > 0) {
            $this->session->set('localeIdentified', $locales[0]);
        } else {
            $this->session->set('localeIdentified', $_locale);
        }

        // Add the listener
        $this->session->set('setLocaleCookie', true);

        

        // Redirect the User
        if ($request->headers->has('referer') && true === $this->useReferrer) {
            return new RedirectResponse($request->headers->get('referer'));
        }

        if (null !== $this->redirectToRoute) {
            return new RedirectResponse($this->router->generate($this->redirectToRoute));
        }
        return new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . $this->redirectToUrl);

        */

        $this->dispatchEvent($_locale);

    }

    public function dispatchEvent($locale)
    {
        $event = new FilterLocaleSwitchEvent($locale);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch(LocaleEvents::onLocaleSwitch, $event);
    }
}
