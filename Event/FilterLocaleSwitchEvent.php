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

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class FilterLocaleSwitchEvent extends Event
{
    protected $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
