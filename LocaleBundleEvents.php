<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle;

final class LocaleBundleEvents
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
    const onLocaleSwitch = 'lunetics_locale.switch';
}
