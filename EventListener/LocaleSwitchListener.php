<?php

namespace Lunetics\LocaleBundle\EventListener;

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Symfony\Component\Routing\Router;

class LocaleSwitchListener
{
	public function __construct(FilterLocaleSwitchEvent $event, Router $router)
	{
		$request = $event->getRequest();

		$session = $event->getSession();

		$switchLocale = $event->getLocale();

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
	}
}