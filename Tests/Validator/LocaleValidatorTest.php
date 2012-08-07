<?php

namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\Validator\LocaleValidator;

class LocaleValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleLocaleIsValid()
    {
        $locale = 'fr';
        $validator = new LocaleValidator();
        $this->assertTrue($validator->validate($locale));
    }
    
    public function testLocaleWithVariantIsValid()
    {
        $locale = 'fr_BE';
        $validator = new LocaleValidator();
        $this->assertTrue($validator->validate($locale));
    }
    
    public function testLocaleWithSameVariantIsValid()
    {
        $locale = 'fr_FR';
        $validator = new LocaleValidator();
        $this->assertTrue($validator->validate($locale));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSimpleInvalidLocale()
    {
        $locale = 'ju';
        $validator = new LocaleValidator();
        $validator->validate($locale);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testComposedInvalidLocale()
    {
        $locale = 'fr_GT';
        $validator = new LocaleValidator();
        $validator->validate($locale);
    }
}