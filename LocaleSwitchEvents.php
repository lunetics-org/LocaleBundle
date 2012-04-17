<?php

namespace Lunetics\LocaleBundle;

final class LocaleEvents
{
	/**
	* The lunetics_locale.switch event is thrown each time a User manually switch to another locale.
	*
	* The available locales to be chosen can be restricted through the allowed_languages configuration.
	*
	* The event listener receives an Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent instance
	*
	* @var string
	*
	*/
	const 'onLocaleSwitch' = 'lunetics_locale.switch';
}