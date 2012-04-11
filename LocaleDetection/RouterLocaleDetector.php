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
	* {@inheritDoc}
	*/
	public function processLocaleDetection()
	{
		if($locale = $this->request->attributes->get('_locale'))
		{
			if(!preg_match('/^[a-z]{2}$/',$locale))
			{
				throw new \InvalidArgumentException('The _locale parameter "'.$locale.'" is not valid');
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