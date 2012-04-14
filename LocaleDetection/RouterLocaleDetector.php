<?php

namespace Lunetics\LocaleBundle\LocaleDetection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class RouterLocaleDetector implements LocaleDetectorInterface
{

	private $allowedLanguages;
	private $defaultLocale;
	private $request;
	private $response;
	private $router;
	private $logger;

	private $detectedLocale;

	/**
	* {@inheritDoc}
	*/
	public function __construct($defaultLocale = 'en', 
								array $allowedLanguages = null,
								Request $request,
								Response $response = null,
								RouterInterface $router = null,
								LoggerInterface $logger = null)
	{
		$this->allowedLanguages = $allowedLanguages;
		$this->defaultLocale = $defaultLocale;
		$this->request = $request;
		$this->response = $response;
		$this->router = $router;
		$this->logger = $logger;
	}

	/**
	* This method contains the logic for the locale detection through the route parameters.
	* A logic is already done in the Symfony\Component\HttpKernel\EventListener\LocaleListener class
	*
	* {@inheritDoc}
	*/
	public function processLocaleDetection()
	{
		if($locale = $this->request->attributes->get('_locale'))
		{
			// I would like to have here another method to verify that the locale is valid
			if(!preg_match('/^[a-z]{2}$/',$locale))
			{
				throw new \InvalidArgumentException('The _locale parameter "'.$locale.'" in the route is not valid');
			}
			$this->detectedLocale = $locale;
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function isLocaleDetected()
	{
		return(!empty($this->detectedLocale));
	}

	/**
	* {@inheritDoc}
	*/
	public function getDetectedLocale()
	{
		return $this->detectedLocale;
	}

	/**
	* {@inheritDoc}
	*/
	public function setDefaultLocale($locale)
	{

	}


}