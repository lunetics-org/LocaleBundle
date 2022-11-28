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

use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
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
            new TwigFunction('locale_switcher', array($this, 'renderSwitcher'), array('is_safe' => array('html'))),
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
