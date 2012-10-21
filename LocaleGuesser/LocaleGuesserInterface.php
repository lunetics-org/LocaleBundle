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

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a guesser
 *
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
interface LocaleGuesserInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request);

    /**
     * @return bool|mixed|string
     */
    public function getIdentifiedLocale();
}
