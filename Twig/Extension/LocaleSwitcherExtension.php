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
        $infos = new TargetInformationBuilder();
        $inf = $infos->getTargetInformations($this->container->get('request'), $this->container->get('router'), $this->container->getParameter('lunetics_locale.allowed_locales'));
        return $this->container->get('lunetics_locale.switcher_helper')->renderSwitch($inf, 'switcher_links.html.twig');
    }
}
