<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\LocaleGuesser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
abstract class AbstractLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var string
     */
    protected $identifiedLocale;

    /**
     * Get the identified locale
     *
     * @return mixed
     */
    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }
}
