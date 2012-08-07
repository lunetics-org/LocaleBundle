<?php

namespace Lunetics\LocaleBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;

class LocaleSwitchHelper extends Helper
{
    protected $templating;
    
    /**
     * Constructor
     * 
     * @param EngineInterface $templating 
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }
    
    /**
     *
     * @param array $localesInfos
     * @param string $template
     */
    public function renderSwitch($localesInfos = array(), $template)
    {
        $templatesNamespace = 'LuneticsLocaleBundle:Switcher:';
        return $this->templating->render($templatesNamespace.$template, $localesInfos);
    }
    
    /**
     *
     * @return string The name of the helper 
     */
    public function getName()
    {
        return 'locale_switch_helper';
    }
}
