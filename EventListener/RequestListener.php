<?php

namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Lunetics\LocaleBundle\LocaleDetection\DetectionPriority;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Cookie;

class RequestListener
{
	/**
	* @var DetectionPriority $detectionPriority The instance of the DetectionPriority class
	*											Contains the possible detection mechanism class names
	*											ordered by priority
	*/
	public $detectionPriority;
	public $defaultLocale;
	public $router;
	public $logger;

	private $dispatcher;
	private $cookieListenerisAdded;

	
	public function __construct(DetectionPriority $detectionPriority, 
								$defaultLocale = 'en',
								array $allowedLanguages = array(),
								LoggerInterface $logger = null,
								RouterInterface $router = null)
	{
		$this->detectionPriority = $detectionPriority;
		$this->defaultLocale = $defaultLocale;
		$this->router = $router;
		$this->logger = $logger;
		$this->allowedLanguages = $allowedLanguages;
	}	

	/**
	* This method is called after a kernel.request event. The method receives a GetResponseEvent object
	*
	* @param GetResponseEvent $event A GetResponseEvent object
	*/
	public function onKernelRequest(GetResponseEvent $event)
	{
		//If this is not a MASTER_REQUEST we do not need to go further
		if(1 === $event->getRequestType())
		{
		$request = $event->getRequest();
		$session = $request->getSession();
		$response = $event->getResponse();

		$detectors = $this->detectionPriority->getDetectorsByPriority();
		foreach($detectors as $key=>$detector)
		{
			if(!empty($detector) && class_exists($detector))
			{
				$engine = new $detector($this->defaultLocale,
										$this->allowedLanguages,
										$request,
										$response,
										$this->router,
										$this->logger
										);

				$engine->processLocaleDetection();

				if($locale = $engine->getDetectedLocale())
				{
					$this->logger->info(sprintf('The locale has been identified through the [ %s ] detector', $detector));
					$this->logger->info(sprintf('The locale identified is the [ %s ] locale', $engine->getDetectedLocale()));
					$request->setDefaultLocale($locale);
					$request->setLocale($locale);
					$this->addCookieResponseListener();
					return;
				}
			}
		}
		}
	}



	public function setDefaultLocale($locale)
	{
		//It would be nice to have a common code here to set the default locale in the app
		//So all detectors(browser, cookie, ...) do not need to have some logic, only the detection logic and return 
		//the detected locale ??
	}

	/**
     * DI Setter for the EventDispatcher
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

	/**
     * Method to add the ResponseListener which sets the cookie. Should only be called once
     *
     * @see also http://slides.seld.be/?file=2011-10-20+High+Performance+Websites+with+Symfony2.html#45
     *
     * @return void
     */
    public function addCookieResponseListener()
    {
        if($this->cookieListenerisAdded !== true) {
            $this->dispatcher->addListener(
                KernelEvents::RESPONSE,
                array($this, 'onKernelResponse')
            );
        }
        $this->cookieListenerisAdded = true;
    }

    public function onKernelResponse(FilterResponseEvent  $event)
    {
        $response = $event->getResponse();
        /* @var $response \Symfony\Component\HttpFoundation\Response */

        $session = $event->getRequest()->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        $response->headers->setCookie(new Cookie('locale', $session->get('localeIdentified')));
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Locale Cookie set to: [ %s ]', $session->get('localeIdentified')));
        }
    }
}