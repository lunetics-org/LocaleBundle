<?php

namespace Lunetics\LocaleBundle\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;

interface LocaleGuesserInterface
{
    function guessLocale(Request $request);
    
    function getIdentifiedLocale();
}