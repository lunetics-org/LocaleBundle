<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class FilterLocaleSwitchEvent extends Event
{
    protected $locale;
    
    protected $request;
    
    protected $router;

    public function __construct(Request $request, RequestContextAwareInterface $router = null, $locale)
    {
        $this->locale = $locale;
        $this->request = $request;
        $this->router = $router;
    }

    public function getLocale()
    {
        return $this->locale;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getRouter()
    {
        return $this->router;
    }
}
