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
use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\LocaleBundleEvents;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for the Switch Locale
 * 
 * @author Matthias Breddin <mb@lunetics.com/>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleController
{
    protected $useReferrer = true;
    
    protected $redirectToRoute = null;
    /**
     * Action for locale switch
     */
    public function switchAction(Request $request, $_locale)
    {
        $validator = new LocaleValidator();
        $validator->validate($_locale);
        
        // Redirect the User
        if ($request->headers->has('referer') && true === $this->useReferrer) {
            return new RedirectResponse($request->headers->get('referer'));
        }

        if (null !== $this->redirectToRoute) {
            return new RedirectResponse($this->container->get('router')->generate($this->redirectToRoute));
        }
        return new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . $this->redirectToUrl);
        
        $event = new FilterLocaleSwitchEvent($_locale);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch(LocaleBundleEvents::onLocaleSwitch, $event);
    }
}
