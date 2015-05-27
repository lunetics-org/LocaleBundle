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
 * Event for guessed locales
 */
class LocaleGuessedEvent extends Event
{
    /**
     * @var string
     */
    protected $guesser;

    /**
     * @var string
     */
    protected $locale;

    /**
     * Constructor
     *
     * @param string $guesser
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($guesser, $locale)
    {
        if (!is_string($guesser) || null == $guesser || '' == $guesser) {
            throw new \InvalidArgumentException(sprintf('Wrong type, expected \'string\' got \'%s\'', $guesser));
        }
        if (!is_string($locale) || null == $locale || '' == $locale) {
            throw new \InvalidArgumentException(sprintf('Wrong type, expected \'string\' got \'%s\'', $locale));
        }

        $this->guesser = $guesser;
        $this->locale = $locale;
    }

    /**
     * Returns the request
     *
     * @return Request
     */
    public function getGuesser()
    {
        return $this->guesser;
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
