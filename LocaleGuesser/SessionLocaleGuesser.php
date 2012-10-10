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

/**
 * This guesser class checks the session for a var
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class SessionLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var string
     */
    private $sessionVariable;

    /**
     * @var string
     */
    private $identifiedLocale;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Session $session         Session
     * @param string  $sessionVariable Key value for the Session
     */
    public function __construct(Session $session, $sessionVariable = 'lunetics_locale')
    {
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
            $this->identifiedLocale = $this->session->get($this->sessionVariable);

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }

    /**
     * Sets the locale in the session
     *
     * @param string $locale
     */
    public function setSessionLocale($locale)
    {
        if (!$this->session->has($this->sessionVariable)) {
            $this->session->set($this->sessionVariable, $locale);
        }
    }
}
