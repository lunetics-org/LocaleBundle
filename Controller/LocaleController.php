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

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\LocaleBundleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Lunetics\LocaleBundle\Validator\MetaValidator;

/**
 * Controller for the Switch Locale
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class LocaleController
{
    private $router;
    private $metaValidator;
    private $useReferrer;
    private $redirectToRoute;
    private $dispatcher;

    /**
     * @param RouterInterface $router          Router Service
     * @param MetaValidator   $metaValidator   MetaValidator for locales
     * @param bool            $useReferrer     From Config
     * @param null            $redirectToRoute From Config
     * @param string          $statusCode      From Config
     */
    public function __construct(RouterInterface $router = null, MetaValidator $metaValidator, $useReferrer = true, $redirectToRoute = null, $statusCode = '302', EventDispatcherInterface $dispatcher = null)
    {
        $this->router = $router;
        $this->metaValidator = $metaValidator;
        $this->useReferrer = $useReferrer;
        $this->redirectToRoute = $redirectToRoute;
        $this->statusCode = $statusCode;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Action for locale switch
     *
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @return RedirectResponse
     */
    public function switchAction(Request $request)
    {
        $_locale = $request->attributes->get('_locale', $request->getLocale());
        $statusCode = $request->attributes->get('statusCode', $this->statusCode);
        $useReferrer = $request->attributes->get('useReferrer', $this->useReferrer);
        $redirectToRoute = $request->attributes->get('route', $this->redirectToRoute);

        $metaValidator = $this->metaValidator;
        if (!$metaValidator->isAllowed($_locale)) {
            throw new \InvalidArgumentException(sprintf('Not allowed to switch to locale %s', $_locale));
        }

        if ($this->dispatcher !== null) {
            $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $_locale);
            $this->dispatcher->dispatch(LocaleBundleEvents::onLocaleChange, $localeSwitchEvent);
        }

        // Redirect the User
        if ($useReferrer && $request->headers->has('referer')) {
            $response = new RedirectResponse($request->headers->get('referer'), $statusCode);
        } elseif ($this->router && $redirectToRoute) {
            $response = new RedirectResponse($this->router->generate($redirectToRoute, array('_locale' => $_locale)), $statusCode);
        } else {
            // TODO: this seems broken, as it will not handle if the site runs in a subdir
            // TODO: also it doesn't handle the locale at all and can therefore lead to an infinite redirect
            $response = new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . '/', $statusCode);
        }

        return $response;

    }
}
