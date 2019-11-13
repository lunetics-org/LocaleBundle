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

use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LocaleSwitcherExtension extends AbstractExtension
{
    /**
     * @var TargetInformationBuilder
     */
    private $targetInformationBuilder;

    /**
     * @var LocaleSwitchHelper
     */
    private $localeSwitchHelper;

    /**
     * Constructor.
     *
     * @param TargetInformationBuilder $targetInformationBuilder
     * @param LocaleSwitchHelper $localeSwitchHelper
     */
    public function __construct(TargetInformationBuilder $targetInformationBuilder, LocaleSwitchHelper $localeSwitchHelper)
    {
        $this->targetInformationBuilder = $targetInformationBuilder;
        $this->localeSwitchHelper = $localeSwitchHelper;
    }

    /**
     * @return array The added functions
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('locale_switcher', [$this, 'renderSwitcher'], array('is_safe' => array('html'))),
        );
    }

    /**
     * @param string $route A route name for which the switch has to be made
     * @param array $parameters
     * @param string $template
     *
     * @return mixed
     * @throws \Exception
     */
    public function renderSwitcher($route = null, $parameters = array(), $template = null)
    {
        return $this->localeSwitchHelper->renderSwitch(
            $this->targetInformationBuilder->getTargetInformations($route, $parameters),
            $template
        );
    }
}
