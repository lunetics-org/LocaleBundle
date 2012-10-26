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
 * Filter for the LocaleSwitchEvent
 */
class FilterLocaleSwitchEvent extends Event
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * Constructor
     *
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($locale)
    {
        if (!is_string($locale) || null == $locale || '' == $locale) {
            throw new \InvalidArgumentException(sprintf('Wrong type, expected \'string\' got \'%s\'', $locale));
        }

        $this->locale = $locale;
    }

    /**
     * Returns the locale string
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}