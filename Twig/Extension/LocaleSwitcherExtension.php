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

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig_SimpleFunction;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleSwitcherExtension extends \Twig_Extension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AllowedLocalesProvider
     */
    private $allowedLocalesProvider;

    /**
     * @var LocaleSwitchHelper
     */
    private $switcherHelper;

    /**
     * @var bool
     */
    private $useControllerParam;

    /**
     * @var bool
     */
    private $showCurrentLocaleParam;

    /**
     * @param RequestStack           $requestStack
     * @param RouterInterface        $router
     * @param AllowedLocalesProvider $allowedLocalesProvider
     * @param LocaleSwitchHelper     $switcherHelper
     * @param bool                   $useControllerParam
     * @param bool                   $showCurrentLocaleParam
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        AllowedLocalesProvider $allowedLocalesProvider,
        LocaleSwitchHelper $switcherHelper,
        $useControllerParam,
        $showCurrentLocaleParam
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->allowedLocalesProvider = $allowedLocalesProvider;
        $this->useControllerParam = $useControllerParam;
        $this->showCurrentLocaleParam = $showCurrentLocaleParam;
        $this->switcherHelper = $switcherHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'locale_switcher',
                array($this, 'renderSwitcher'),
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * @return string The name of the extension
     */
    public function getName()
    {
        return 'locale_switcher';
    }

    /**
     * @param string $route      A route name for which the switch has to be made
     * @param array  $parameters
     * @param string $template
     *
     * @return string
     */
    public function renderSwitcher($route = null, $parameters = array(), $template = null)
    {
        $showCurrentLocale = $this->showCurrentLocaleParam;
        $useController = $this->useControllerParam;
        $allowedLocales = $this->allowedLocalesProvider->getAllowedLocales();

        $informationBuilder = new TargetInformationBuilder(
            $this->requestStack->getMasterRequest(),
            $this->router,
            $allowedLocales,
            $showCurrentLocale,
            $useController
        );

        $viewParams = $informationBuilder->getTargetInformations($route, $parameters);

        return $this->switcherHelper->renderSwitch($viewParams, $template);
    }
}
