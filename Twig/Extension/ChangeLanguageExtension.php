<?php

namespace Lunetics\LocaleBundle\Twig\Extension;

/**
 * Twig extension providing helpers for implementing a language change mechanism and handling localized routes.
 *
 * @author    Christian Raue <christian.raue@gmail.com>
 * @copyright 2011 Christian Raue
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ChangeLanguageExtension extends AbstractLocaleAwareExtension
{

    /**
     * @var array
     */
    protected $availableLocales = array();

    /**
     * @var boolean
     */
    protected $showForeignLanguageNames = false;

    /**
     * @var boolean
     */
    protected $showFirstUppercase = false;

    /**
     * @var boolean
     */
    protected $showLanguagetitle = false;

    /**
     * Sets the available locales.
     *
     * @param array $availableLocales
     */
    public function setAvailableLocales(array $availableLocales = array())
    {
        $this->availableLocales = $availableLocales;
    }

    /**
     * Sets whether each language's name will be shown in its foreign language.
     *
     * @param boolean $showForeignLanguageNames
     */
    public function setShowForeignLanguageNames($showForeignLanguageNames)
    {
        $this->showForeignLanguageNames = (boolean)$showForeignLanguageNames;
    }

    /**
     * Sets whether all language names will be shown with a leading uppercase character.
     * This requires the mbstring extension {@link http://php.net/manual/book.mbstring.php} to be loaded.
     *
     * @param boolean $showFirstUppercase
     */
    public function setShowFirstUppercase($showFirstUppercase)
    {
        $this->showFirstUppercase = (boolean)$showFirstUppercase;
    }

    /**
     * Sets whether show a Title to the languageselection
     *
     * @param boolean $showLanguagetitle
     */
    public function setShowLanguagetitle($showLanguagetitle)
    {
        $this->showLanguagetitle = (boolean)$showLanguagetitle;
    }


    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        $functions = array();

        $getLanguageNameMethod = new \Twig_Function_Method($this, 'getLanguageName');
        $functions['lunetics_languageName'] = $getLanguageNameMethod;

        $getLocaleLanguageMethod = new \Twig_Function_Method($this, 'getLocaleLanguage');
        $functions['lunetics_getLocaleLanguage'] = $getLocaleLanguageMethod;

        return $functions;
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobals()
    {
        $globals = array();

        $globals['lunetics_availableLocales'] = $this->availableLocales;
        $globals['lunetics_showLanguagetitle'] = $this->showLanguagetitle;

        return $globals;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'lunetics_changeLanguage';
    }

    /**
     * Get the corresponding language name for a locale.
     * If the given locale contains a region code the name of that region will be appended in parentheses.
     *
     * @param string $locale Locale to be used with {@link http://php.net/manual/locale.getdisplayname.php}.
     *
     * @return string
     */
    public function getLanguageName($locale)
    {
        if (empty($locale)) {
            return null;
        }

        $localeToUse = $this->showForeignLanguageNames ? $locale : $this->getLocale();

        $languageName = \Locale::getDisplayName($locale, $localeToUse);

        if ($this->showFirstUppercase) {
            if (!extension_loaded('mbstring')) {
                throw new \RuntimeException('PHP extension "mbstring" is not loaded. Either load it or disable the "showFirstUppercase" option.');
            }
            $encoding = mb_detect_encoding($languageName);
            $languageName = mb_strtoupper(mb_substr($languageName, 0, 1, $encoding), $encoding)
                    . mb_substr($languageName, 1, mb_strlen($languageName, $encoding), $encoding);
        }

        return $languageName;
    }

}
