<?php

namespace Lunetics\LocaleBundle\Tests\LocaleDetection;

use Lunetics\LocaleBundle\LocaleDetection\DetectionPriority;

class DetectionPriorityTest extends \PHPUnit_Framework_TestCase
{
	public function testDefaultPriority()
	{
		$defaultPrio = array('cookie', 'browser', 'router', 'custom');

		$prioClass = new DetectionPriority(array());

		$this->assertEquals($prioClass->defaultPriority, $defaultPrio);

	}

	public function testUserPriorityMerging()
	{
		$userPrio = array('custom', 'cookie');
		$prioClass = new DetectionPriority($userPrio);

		$expectedPrio = array('custom', 'cookie', 'browser', 'router');

		$this->assertEquals($prioClass->defaultPriority, $expectedPrio);
	}
}