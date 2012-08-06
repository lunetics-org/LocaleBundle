<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleGuesserManager
{
    private $guessingOrder;
    
    private $routerLocaleGuesser;
    
    private $browserLocaleGuesser;
    
    private $cookieLocaleGuesser;
    
    private $customGuessingService;
    
    private $guessersMap;
    
    private $logger;
    
    /**
     * Constructor
     * 
     * @param array $guessingOrder
     * @param LocaleGuesserInterface $routerGuesser
     * @param LocaleGuesserInterface $browserGuesser
     * @param LocaleGuesserInterface $cookieGuesser
     * @param LocaleGuesserInterface $customGuessingService
     * @param LoggerInterface $logger
     */
    public function __construct(array $guessingOrder, LocaleGuesserInterface $routerGuesser, LocaleGuesserInterface $browserGuesser = null,
                                LocaleGuesserInterface $cookieGuesser = null, LocaleGuesserInterface $customGuessingService = null, 
                                LoggerInterface $logger = null)
    {
        $this->guessingOrder = $guessingOrder;
        $this->routerLocaleGuesser = $routerGuesser;
        $this->browserLocaleGuesser = $browserGuesser;
        $this->cookieLocaleGuesser = $cookieGuesser;
        $this->customGuessingService = $customGuessingService;
        $this->logger = $logger;
        $this->mapGuessersToServices();
    }
    
    /**
     * Loops through all the activated Locale Guessers and
     * calls the guessLocale methode and passing the current request
     * 
     * @param Request $request
     * @return boolean false if no locale is identified
     * @return string the locale identified by the guessers
     */
    public function runLocaleGuessing(Request $request)
    {
        foreach($this->guessingOrder as $key => $guesser) {
            $guessingService = $this->guessersMap[$guesser];
            if(null !== $guessingService){
                $this->logEvent('%s Guessing Service Loaded', ucfirst($guesser));
                if($guessingService->guessLocale($request)){
                    $locale = $guessingService->getIdentifiedLocale();
                    $this->logEvent('Locale has been identified : ( %s )', $locale);
                    return $locale;
                }
                $this->logEvent('Locale has not been identified by the %s Guessing Service', ucfirst($guesser));
            }
        }
        return false;
    }
    
    /**
     * Returns the Locale Guessing Services mapped by guesser names
     * 
     * @return array the guessing services
     */
    public function getGuessingServices()
    {
        return $this->guessersMap;
    }
    
    /**
     * Links the guesser names to the correspondant services
     */
    private function mapGuessersToServices()
    {
        $this->guessersMap = array(
            'router' => $this->routerLocaleGuesser,
            'browser' => $this->browserLocaleGuesser,
            'cookie' => $this->cookieLocaleGuesser,
            'custom' => $this->customGuessingService
        );
    }
    
    /**
     * Log detection events
     * 
     * @param type $logMessage
     * @param type $parameters
     */
    private function logEvent($logMessage, $parameters = null)
    {
        if (null !== $this->logger) {
                $this->logger->info(sprintf($logMessage, $parameters));
            }
    }
}