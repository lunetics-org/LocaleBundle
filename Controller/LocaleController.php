<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Controller;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for the Switch Locale
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class LocaleController
{
    protected $router;
    protected $useReferrer;
    protected $redirectToRoute;

    /**
     * @param RouterInterface $router          Router Service
     * @param bool            $useReferrer     From Config
     * @param null            $redirectToRoute From Config
     * @param string          $statusCode      From Config
     */
    public function __construct(RouterInterface $router = null, $useReferrer = true, $redirectToRoute = null, $statusCode = '302')
    {
        $this->router = $router;
        $this->useReferrer = $useReferrer;
        $this->statusCode = $statusCode;
        $this->redirectToRoute = $redirectToRoute;
    }

    /**
     * Action for locale switch
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function switchAction(Request $request)
    {
        $_locale = $request->attributes->get('_locale', $request->getLocale());
        // Save into session
        // TODO: Build Locale Persister and decouple from the guessers
        $session = $request->getSession();
        $session->set('lunetics_locale', $_locale);

        // Redirect the User
        if ($this->useReferrer && $request->headers->has('referer')) {
            $response = new RedirectResponse($request->headers->get('referer'), $this->statusCode);
        } elseif ($this->router && $this->redirectToRoute) {
            $response = new RedirectResponse($this->router->generate($this->redirectToRoute, array('_locale' => $_locale)), $this->statusCode);
        } else {
            // TODO: this seems broken, as it will not handle if the site runs in a subdir
            // TODO: also it doesn't handle the locale at all and can therefore lead to an infinite redirect
            $response = new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . '/', $this->statusCode);
        }
        $response->setVary('accept-language');

        return $response;

    }
}
