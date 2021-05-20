<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Switcher;

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Scripts;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Builder to generate information about the switcher links
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class TargetInformationBuilder
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var bool
     */
    private $showCurrentLocale;
    /**
     * @var bool
     */
    private $useController;
    /**
     * @var AllowedLocalesProvider
     */
    private $allowedLocalesProvider;

    /**
     * @param RequestStack $requestStack Request
     * @param RouterInterface $router Router
     * @param AllowedLocalesProvider $allowedLocalesProvider
     * @param bool $showCurrentLocale Config Var
     * @param bool $useController Config Var
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        AllowedLocalesProvider $allowedLocalesProvider,
        $showCurrentLocale = false,
        $useController = false
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->allowedLocalesProvider = $allowedLocalesProvider;
        $this->showCurrentLocale = $showCurrentLocale;
        $this->useController = $useController;
    }

    /**
     * Builds a bunch of informations in order to build a switcher template
     * for custom needs
     *
     * Will return something like this (let's say current locale is fr :
     *
     * current_route: hello_route
     * current_locale: fr
     * locales:
     *   en:
     *     link: http://app_dev.php/en/... or http://app_dev.php?_locale=en
     *     locale: en
     *     locale_target_language: English
     *     locale_current_language: Anglais
     *
     * @param string|null $targetRoute The target route
     * @param array $parameters Parameters
     *
     * @return array           Informations for the switcher template
     */
    public function getTargetInformations($targetRoute = null, $parameters = [])
    {
        $request = $this->requestStack->getCurrentRequest();
        $router = $this->router;
        $route = $request->attributes->get('_route');

        if (method_exists($router, 'getGenerator')) {
            $generator = $router->getGenerator();
            if ($generator instanceof ConfigurableRequirementsInterface) {
                if (!$generator->isStrictRequirements()) {
                    $strict = false;
                }
            }
        }

        $infos['current_locale'] = $request->getLocale();
        $infos['current_route'] = $route;
        $infos['locales'] = [];

        $parameters = array_merge((array) $request->attributes->get('_route_params'), $request->query->all(), (array) $parameters);

        foreach ($this->allowedLocalesProvider->getAllowedLocales() as $locale) {
            $strpos = 0 === strpos($request->getLocale(), $locale);
            if ($this->showCurrentLocale && $strpos || !$strpos) {

                $targetLocaleTargetLang = Locales::getName($locale, $locale);
                $targetLocaleCurrentLang = Locales::getName($locale, $request->getLocale());
                $parameters['_locale'] = $locale;
                try {
                    if (null !== $targetRoute && "" !== $targetRoute) {
                        $switchRoute = $router->generate($targetRoute, $parameters);
                    } elseif ($this->useController) {
                        $switchRoute = $router->generate('lunetics_locale_switcher', ['_locale' => $locale]);
                    } elseif ($route) {
                        $switchRoute = $router->generate($route, $parameters);
                    } else {
                        continue;
                    }
                } catch (RouteNotFoundException $e) {
                    // skip routes for which we cannot generate a url for the given locale
                    continue;
                } catch (InvalidParameterException $e) {
                    // skip routes for which we cannot generate a url for the given locale
                    continue;
                } catch (\Exception $e) {
                    if (isset($strict)) {
                        $generator->setStrictRequirements(false);
                    }

                    throw $e;
                }

                $infos['locales'][$locale] = [
                    'locale_current_language' => $targetLocaleCurrentLang,
                    'locale_target_language' => $targetLocaleTargetLang,
                    'link' => $switchRoute,
                    'locale' => $locale,
                ];
            }
        }

        if (isset($strict)) {
            $generator->setStrictRequirements(false);
        }

        return $infos;
    }
}
