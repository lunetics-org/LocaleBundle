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


use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use Lunetics\LocaleBundle\Validator\LocaleValidator;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleSwitchListener
{
    private $cookieManager;
    
    private $useReferrer = true;
    
    private $redirectToRoute = null;
    
    public function __construct(LocaleCookie $cookieManager)
    {
        $this->cookieManager = $cookieManager;
    }
    
    public function onLocaleSwitch(FilterLocaleSwitchEvent $event)
    {
        $locale = $event->getLocale();
        $request = $event->getRequest();
        $router = $event->getRouter();
        $validator = new LocaleValidator();
        $validator->validate($locale);
        
        if ($request->headers->has('referer') && true === $this->useReferrer) {
            return new RedirectResponse($request->headers->get('referer'));
        }

        if (null !== $this->redirectToRoute) {
            return new RedirectResponse($this->router->generate($this->redirectToRoute));
        }
        return new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . $this->redirectToUrl);
    }
}