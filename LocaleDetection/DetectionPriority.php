<?php

namespace Lunetics\LocaleBundle\LocaleDetection;

use Symfony\Component\HttpFoundation\Request;


/**
* This class determines the Detection logic priority for guessing the default Locale
*/

class DetectionPriority
{

	/**
	* @var array $priorities Default priorities for Locale Detection Mechanism
	*/
	public $defaultPriority = array(
		'cookie', 'browser', 'router', 'custom'
		);

	/**
	* @var array $detectorsClasses The array containing the class names of the different detectors
	*/
	public $detectorsClasses = array();


	public function __construct(array $userPriority,
										$browserDetector = null,
										$routerDetector = null,
										$cookieDetector = null,
										$customDetector = null)
	{
		$this->detectorsClasses['browser'] = $browserDetector;
		$this->detectorsClasses['router'] = $routerDetector;
		$this->detectorsClasses['cookie'] = $cookieDetector;
		$this->detectorsClasses['custom'] = $customDetector;

		if(!empty($userPriority))
		{
			$this->mergeUserPriority($userPriority);
		}
	}

	
	public function mergeUserPriority(array $userPriority)
	{
		foreach(array_reverse($userPriority) as $key=>$value)
		{
			array_unshift($this->defaultPriority, $value);
		}
		$this->defaultPriority = array_values(array_unique($this->defaultPriority));
	}

	public function getDetectorsByPriority()
	{
		$detectors = array();
		foreach($this->defaultPriority as $key=>$value)
		{
			$detectors[$value] = $this->detectorsClasses[$value];
		}
		return $detectors;
	}
	
}