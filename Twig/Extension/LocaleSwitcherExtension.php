<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleSwitcherExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
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
            'locale_switcher' => new \Twig_Function_Method($this, 'renderSwitcher', array('is_safe' => array('html')))
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
     * @param string $route     A route name for which the switch has to be made
     * @param array $parameters
     * @param string $template
     *
     * @return mixed
     */
    public function renderSwitcher($route = null, $parameters = array(), $template = null)
    {
        $showCurrentLocale = $this->container->getParameter('lunetics_locale.switcher.show_current_locale');
        $useController = $this->container->getParameter('lunetics_locale.switcher.use_controller');
        $allowedLocales = $this->container->getParameter('lunetics_locale.allowed_locales');
        $request = $this->container->get('request');
        $router = $this->container->get('router');

        $infosBuilder = new TargetInformationBuilder($request, $router, $allowedLocales, $showCurrentLocale, $useController);

        $infos = $infosBuilder->getTargetInformations($route, $parameters);

        return $this->container->get('lunetics_locale.switcher_helper')->renderSwitch($infos, $template);
    }
}
