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
use Symfony\Component\HttpFoundation\Session\Session;
use Lunetics\LocaleBundle\Validator\MetaValidator;

/**
 * Locale Guesser for retrieving a previously deteced locale from the session
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class SessionLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var string
     */
    private $sessionVariable;

    /**
     * @var MetaValidator
     */
    private $metaValidator;
    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Session       $session         Session
     * @param MetaValidator $metaValidator   MetaValidator
     * @param string        $sessionVariable Key value for the Session
     */
    public function __construct(Session $session, MetaValidator $metaValidator, $sessionVariable = 'lunetics_locale')
    {
        $this->metaValidator = $metaValidator;
        $this->session = $session;
        $this->sessionVariable = $sessionVariable;
    }

    /**
     * Guess the locale based on the session variable
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function guessLocale(Request $request)
    {
        if ($this->session->has($this->sessionVariable)) {
            $locale = $this->session->get($this->sessionVariable);
            if (!$this->metaValidator->isAllowed($locale)) {
                return false;
            }
            $this->identifiedLocale = $this->session->get($this->sessionVariable);

            return true;
        }

        return false;
    }

    /**
     * Sets the locale in the session
     *
     * @param string $locale Locale
     * @param bool   $force  Force write session
     */
    public function setSessionLocale($locale, $force = false)
    {
        if (!$this->session->has($this->sessionVariable) || $force) {
            $this->session->set($this->sessionVariable, $locale);
        }
    }
}
