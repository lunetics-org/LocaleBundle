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
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\LocaleBundleEvents;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;

/**
 * Controller for the Switch Locale
 * 
 * @author Matthias Breddin <mb@lunetics.com/>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleController
{
    private $request;
    private $router;
    
    /**
     * Constructor
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Routing\RequestContextAwareInterface $router
     */
    public function __construct(Request $request, RequestContextAwareInterface $router = null)
    {
        $this->router = $router;
        $this->request = $request;
    }
    
    /**
     * Action for locale switch
     */
    public function switchAction($_locale)
    {
        $event = new FilterLocaleSwitchEvent($this->request, $this->router, $_locale);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch(LocaleBundleEvents::onLocaleSwitch, $event);
    }
}
