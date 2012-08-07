<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleSwitchHelper extends Helper
{
    protected $templating;
    
    protected $templates = array(
        'links' => 'LuneticsLocaleBundle:Switcher:switch_links.html.twig',
        'form' => 'LuneticsLocaleBundle:Switcher:switch_form.html.twig'
    );
    
    protected $view;
    
    /**
     * Constructor
     * 
     * @param EngineInterface $templating 
     * @param string $template The Twig Template that renders the switch
     */
    public function __construct(EngineInterface $templating, $template)
    {
        $this->templating = $templating;
        if(array_key_exists($template, $this->templates)){
            $this->view = $templates[$template];
        }
        $this->view = $template;
        
        
    }
    
    /**
     *
     * @param array $viewParams
     */
    public function renderSwitch(array $viewParams = array())
    {
        return $this->templating->render($this->view, $viewParams);
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
