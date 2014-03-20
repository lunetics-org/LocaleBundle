<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Validate the incoming locale aginst configured locales
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class IncomingLocaleListener
{
    protected $allowedLocales;

    /**
     * @param array $allowedLocales array of allowed locales.
     */
    public function __construct(array $allowedLocales)
    {
        $this->allowedLocales = $allowedLocales;
    }

    /**
     * Called at the "kernel.request" event
     *
     * If the request locale does not match any configured locales,
     * it will raise an excpetion.
     *
     * @throw NotFoundHttpException
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!in_array($request->getLocale(), $this->allowedLocales)) {
            throw new NotFoundHttpException(sprintf('No route found for "%s %s"', $request->getMethod(), $request->getRequestUri()));
        }
    }
}
