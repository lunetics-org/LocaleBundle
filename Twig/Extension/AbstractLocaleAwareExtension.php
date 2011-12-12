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
     * @var ContainerInterface
     */
    protected $serviceContainer = null;

    /**
     * Injects the Service Container
     * @param mixed $serviceContainer Can be a locale string or the service container.
     */
    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
        if ($serviceContainer === null) {
            return;
        }

        if (!$serviceContainer instanceof ContainerInterface) {
            throw new \InvalidArgumentException(sprintf('Expected argument of either type "string" or "%s", but "%s" given.',
                'Symfony\Component\DependencyInjection\ContainerInterface',
                is_object($serviceContainer) ? get_class($serviceContainer) : gettype($serviceContainer)
            ));
        }
    }

    /**
     * Returns the Service Container
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getServiceContainer()->get('request')->getLocale();
    }

    /**
     * @return string
     */
    public function getLocaleLanguage()
    {
        return \Locale::getPrimaryLanguage($this->getServiceContainer()->get('request')->getLocale());
    }

}

