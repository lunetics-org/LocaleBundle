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

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\LocaleBundleEvents;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for the Switch Locale
 *
 * @author Matthias Breddin <mb@lunetics.com/>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleController
{
    protected $router;

    public function __construct(RouterInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Action for locale switch
     */
    public function switchAction(Request $request)
    {
        $_locale = $request->attributes->get('_locale', $request->getLocale());

        $validator = new LocaleValidator();
        $validator->validate($_locale);

        $event = new FilterLocaleSwitchEvent($_locale);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch(LocaleBundleEvents::onLocaleSwitch, $event);

        $statusCode = $request->attributes->get('statusCode');

        // Redirect the User
        if ($request->attributes->get('useReferrer') && $request->headers->has('referer')) {
            return new RedirectResponse($request->headers->get('referer'), $statusCode);
        }

        if ($this->router) {
            $route = $request->attributes->get('route');
            if (null !== $route) {
                return new RedirectResponse($this->router->generate($route, array('_locale' => $_locale)), $statusCode);
            }
        }

        // TODO: this seems broken, as it will not handle if the site runs in a subdir
        // TODO: also it doesn't handle the locale at all and can therefore lead to an infinite redirect
        return new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . '/', $statusCode);
    }
}
