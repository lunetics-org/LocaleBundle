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
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
interface LocaleGuesserInterface
{
    public function guessLocale(Request $request);

    public function getIdentifiedLocale();
}
