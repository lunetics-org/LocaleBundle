<?php

namespace Lunetics\LocaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for the Switch Locale
 */
class LocaleController extends Controller
{
    /**
     * Action for locale switch

     * @param $_locale The locale to set

     * @return \Symfony\Bundle\FrameworkBundle\Controller\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function switchAction($_locale)
    {
        $request = $this->getRequest();
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $this->get('session');
        /* @var $session \Symfony\Component\HttpFoundation\Session */
        $router = $this->get('router');
        /* @var $router \Symfony\Component\Routing\Router */

        $container = $this->container;

        $redirectToRoute = $container->getParameter('lunetics_locale.switch.redirect_route');
        $redirectToUrl = $container->getParameter('lunetics_locale.switch.redirect_url');
        $useReferrer = $container->getParameter('lunetics_locale.switch.use_referrer');
        $allowedLanguages = $container->getParameter('lunetics_locale.allowed_languages');

        // Check if the Language is allowed
        if (!in_array(\Locale::getPrimaryLanguage($_locale), $allowedLanguages)) {
            throw new NotFoundHttpException('This language is not available');
        }
        $session->setLocale($_locale);

        // Set the Locale Manually selected, will also be set into cookie at the response listener
        $session->set('localeManually', true);

        $logger = $this->get('logger');
        if (null !== $logger) {
            $logger->info(sprintf('Language Locale manually set to cookie, value: [ %s ]', $session->getLocale()));
        }

        // Redirect the User
        if ($request->headers->has('referer') && true === $useReferrer) {
            return $this->redirect($request->headers->get('referer'));
        }

        if (null !== $redirectToRoute) {
            return $this->redirect($this->generateUrl($redirectToRoute));
        }

        return $this->redirect($request->getScheme() . '://' . $request->getHttpHost() . $redirectToUrl);

    }
}
