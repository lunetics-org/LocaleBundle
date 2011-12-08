<?php

namespace Lunetics\LocaleBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Common base class for Twig extensions dealing with the current locale.
 *
 * @author    Christian Raue <christian.raue@gmail.com>
 * @author    Matthias Breddin <mb@lunetics.com>
 * @copyright 2011 Christian Raue
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class AbstractLocaleAwareExtension extends \Twig_Extension
{

    /**
     * @var string
     */
    protected $localeStorage = null;
    /**
     * @var ContainerInterface
     */
    protected $serviceContainer = null;

    /**
     * @param mixed $serviceContainer Can be a locale string or the service container.
     */
    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
        if ($serviceContainer === null) {
            return;
        }

        if ($serviceContainer instanceof ContainerInterface) {
            if (version_compare(Kernel::VERSION, '2.1.0-DEV') >= 0) {
                if ($serviceContainer->isScopeActive('request') && $serviceContainer->has('request')) {
                    $this->localeStorage = 'request';
                    return;
                }
            } else {
                if ($serviceContainer->has('session')) {
                    $this->localeStorage = 'session';
                    return;
                }
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Expected argument of either type "string" or "%s", but "%s" given.',
                'Symfony\Component\DependencyInjection\ContainerInterface',
                is_object($serviceContainer) ? get_class($serviceContainer) : gettype($serviceContainer)
            ));
        }
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        //return $this->locale;
        return $this->getServiceContainer()->get($this->localeStorage)->getLocale();
    }

    /**
     * @return string
     */
    public function getLocaleLanguage()
    {
        return \Locale::getPrimaryLanguage($this->getServiceContainer()->get($this->localeStorage)->getLocale());
    }

}

