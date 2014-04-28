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
class DefaultBestLocaleMatcher implements BestLocaleMatcher
{
    /**
     * @var array
     */
    private $allowedLocales;

    /**
     * Constructor
     *
     * @param array  $allowedLocales array of valid locales
     */
    public function __construct(array $allowedLocales)
    {
        $this->allowedLocales = $allowedLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function match($locale)
    {
        uasort($this->allowedLocales, function ($a, $b) {
            return strlen($b)-strlen($a);
        });
    	foreach ($this->allowedLocales as $allowedLocale) {
    		if (strpos($locale, $allowedLocale)===0) {
                return $allowedLocale;
    		}
    	}
    	return false;
    }
}
