<?php

namespace Lunetics\LocaleBundle\LocaleDetection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

interface LocaleDetectorInterface
{

	/**
	* Constructs the object iot have the available tools to detect the locale
	*
	* @param string $locale The default locale
	* @param array $allowedLanguages The allowed languages defined in the configuration file
	* @param Request $request The current request
	* @param Response $response The response for the current request
	* @param Router $router The router object
	* @param Logger $logger The logger if available
	*/
	function __construct($defaultLocale = 'en', 
								array $allowedLanguages = null,
								Request $request,
								Response $response = null,
								RouterInterface $router = null,
								LoggerInterface $logger = null);

	/**
	* Executes the logic to detect the locale
	* Sets the locale identifier in the detectedLocale property
	*/
	function processLocaleDetection();
	
	/**
	* Returns true if the Locale is detected by the Detection logic
	*
	* @return Boolean
	*/
	function isLocaleDetected();

	/**
	* Return the detected Locale
	*
	* @return string
	*/
	function getDetectedLocale();

	/**
	* Sets the default Locale to be used after the Detection logic
	*
	* @param string $locale The default Locale
	*/
	function setDefaultLocale($locale);
	
}