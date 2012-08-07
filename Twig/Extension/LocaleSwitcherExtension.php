<?php

namespace Lunetics\LocaleBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;

class LocaleSwitcherExtension extends \Twig_Extension
{
    protected $container;
    
    /**
     * Constructor
     * 
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     *
     * @return array The added functions
     */
    public function getFunctions()
    {
        return array(
            'locale_switcher_show' => new \Twig_Function_Method($this, 'renderSwitcher', array('is_safe' => array('html')))
        );
    }
    
    /**
     *
     * @return string The name of the extension
     */
    public function getName()
    {
        return 'locale_switcher';
    }
    
    /**
     *
     * @param array $parameters
     */
    public function renderSwitcher($parameters = array())
    {
        $infosBuilder = new TargetInformationBuilder();
        $request = $this->container->get('request');
        $router = $this->container->get('router');
        $allowedLocales = $this->container->getParameter('lunetics_locale.allowed_locales');
        
        $infos = $infosBuilder->getTargetInformations($request, $router, $allowedLocales);
        return $this->container->get('lunetics_locale.switcher_helper')->renderSwitch($infos, 'switcher_links.html.twig');
    }
}
