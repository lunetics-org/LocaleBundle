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
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Request;

class LocaleGuesserManagerTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var LocaleGuesserInterface|MockObject */
    private $localeGuesser;

    protected function setUp() : void
    {
        $this->validator     = $this->createMock(MetaValidator::class);
        $this->logger        = $this->createMock(LoggerInterface::class);
        $this->localeGuesser = $this->createMock(LocaleGuesserInterface::class);
    }

    public function testLocaleGuessingInvalidGuesser() : void
    {
        $guesserManager = new LocaleGuesserManager([0 => 'foo']);
        $guesserManager->addGuesser($this->getGuesserMock(), 'bar');

        $this->expectException(InvalidConfigurationException::class);

        $guesserManager->runLocaleGuessing($this->getRequestWithoutLocaleQuery());
    }

    public function testLocaleIsIdentifiedByTheQueryGuessingService() : void
    {
        $request = $this->getRequestWithLocaleQuery('fr');

        $this->validator
            ->method('isAllowed')
            ->with('fr')
            ->willReturn(true);

        $this->logger
            ->expects($this->at(0))
            ->method('debug', [])
            ->with('Locale Query Guessing Service Loaded');

        $this->logger
            ->expects($this->at(1))
            ->method('debug', [])
            ->with('Locale has been identified by guessing service: ( Query )');

        $order   = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order, $this->logger);
        $manager->addGuesser(new RouterLocaleGuesser($this->validator), 'router');
        $manager->addGuesser(new QueryLocaleGuesser($this->validator), 'query');


        $this->localeGuesser
            ->method('guessLocale')
            ->willReturn(false);
        $manager->addGuesser($this->localeGuesser, 'browser');
        $locale = $manager->runLocaleGuessing($request);
        $this->assertEquals('fr', $locale);
    }

    public function testLocaleIsNotIdentifiedIfNoQueryParamsExist() : void
    {
        $request = $this->getRequestWithoutLocaleQuery();

        $this->validator
            ->expects($this->never())
            ->method('isAllowed');

        $order   = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser($this->validator), 'router');
        $manager->addGuesser(new QueryLocaleGuesser($this->validator), 'query');

        $this->localeGuesser
            ->method('guessLocale')
            ->willReturn(false);

        $manager->addGuesser($this->localeGuesser, 'browser');
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertFalse($guessing);
    }

    public function testGetPreferredLocales() : void
    {
        $manager = new LocaleGuesserManager([]);
        $value   = uniqid('preferredLocales:', true);

        // todo: this is bad! supply proper interface to GuesserManager to get the list of allowed locales
        $reflectionsClass = new ReflectionClass(get_class($manager));
        $property         = $reflectionsClass->getProperty('preferredLocales');
        $property->setAccessible(true);
        $property->setValue($manager, $value);

        $this->assertEquals($value, $manager->getPreferredLocales());
    }

    public function testGetGuessingOrder() : void
    {
        $order = [0 => 'query', 1 => 'router'];

        $manager = new LocaleGuesserManager($order);

        $this->assertEquals($order, $manager->getGuessingOrder());
    }

    public function testRemoveGuesser() : void
    {
        $order   = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order);

        $manager->addGuesser($this->getGuesserMock(), 'mock');

        $manager->removeGuesser('mock');
        $this->assertNull($manager->getGuesser('mock'));
    }

    private function getRequestWithLocaleQuery($locale = 'en') : Request
    {
        $request = Request::create(' / hello - world', 'GET');
        $request->query->set('_locale', $locale);

        return $request;
    }

    private function getRequestWithoutLocaleQuery() : Request
    {
        return Request::create(' / hello - world', 'GET');
    }

    /**
     * @return LocaleGuesserInterface|MockObject
     */
    private function getGuesserMock()
    {
        return $this->createMock(LocaleGuesserInterface::class);
    }

}
