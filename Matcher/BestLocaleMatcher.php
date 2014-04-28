<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Matcher;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 */
interface BestLocaleMatcher
{
    /**
     *
     * @param string $locale the current locale
     *
     * @return boolean false if the passed locale is not allowed and no alternatives are available
     * @return string if the passed locale allowed ot is exists a valid alternative
     */
    public function match($locale);
}
