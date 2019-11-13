<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Lunetics\LocaleBundle\LocaleGuesser\SessionLocaleGuesser;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SessionLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var Session */
    private $session;

    /** @var SessionLocaleGuesser */
    private $guesser;

    protected function setUp() : void
    {
        $this->validator = $this->createMock(MetaValidator::class);
        $this->session   = new Session(new MockArraySessionStorage());
        $this->guesser   = new SessionLocaleGuesser($this->session, $this->validator);
    }

    public function testGuesserExtendsInterface() : void
    {
        $this->assertInstanceOf(LocaleGuesserInterface::class, $this->guesser);
    }

    public function testGuessLocaleWithoutSessionVariable() : void
    {
        $request = $this->getRequestWithSessionLocale();

        $this->assertFalse($this->guesser->guessLocale($request));
    }

    public function testLocaleIsRetrievedFromSessionIfSet() : void
    {
        $request = $this->getRequestWithSessionLocale();
        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('ru')
            ->willReturn(true);

        $this->guesser->guessLocale($request);
        $this->assertEquals('ru', $this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedFromSessionIfInvalid() : void
    {
        $request = $this->getRequestWithSessionLocale();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('ru')
            ->willReturn(false);

        $this->guesser->guessLocale($request);
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function testSetSessionLocale() : void
    {
        $locale = uniqid('locale:', true);

        $this->guesser->setSessionLocale($locale, true);

        $this->assertAttributeContains($locale, 'session', $this->guesser);
    }


    public function testLocaleIsNotRetrievedFromSessionIfNotStarted() : void
    {
        $request = $this->getRequestNoSessionLocale();
        $this->validator
            ->expects($this->never())
            ->method('isAllowed');

        $this->guesser->guessLocale($request);
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function testSessionIsNotAutomaticalyStarted() : void
    {
        $request = $this->getRequestNoSessionLocale();

        $this->guesser->guessLocale($request);
        $this->assertFalse($this->session->isStarted());
    }

    private function getRequestNoSessionLocale() : Request
    {
        $request = Request::create('/');
        $request->setSession($this->session);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRequestWithSessionLocale($locale = 'ru') : Request
    {
        $this->session->set('lunetics_locale', $locale);
        $request = Request::create('/');
        $request->setSession($this->session);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }
}
