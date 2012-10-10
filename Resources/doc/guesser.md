Custom Guesser
==============
If you want to implement your own locale guesser, you need the following steps:

1. Define the guesser class in the config
-----------------------------------------
Add the following configuration to `app/config/config.yml`:

``` yaml
lunetics_locale:    
    acme_guesser:
        class: Acme\AcmeBundle\LocaleGuesser\AcmeLocaleGuesser
```

2. Add the guesser as service
------------------
It is important that you add exactly this tag name.
``` xml
<service id="lunetics_locale.acme_guesser" class="%lunetics_locale.acme_guesser.class%">
    <tag name="lunetics_locale.guesser" alias="acme" />
</service>
```

3. Build your guesser class
--------------------------
You need to build the guesser class. The class must implement the LocaleGuesserInterface.

If a locale is identified, the `guessLocale` function must set the locale in the object and return the string value of the locale.
If no locale was found, the `guessLocale` function must return false.

``` php
// src/Acme/AcmeBundle/LocaleGuesser/AcmeLocaleGuesser.php
<?php
namespace Acme\AcmeBundle\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;

class AcmeLocaleGuesser implements LocaleGuesserInterface
{
    private $identifiedLocale;

    public function guessLocale(Request $request)
    {
        // Code to identify the locale, if found:
        if($foundLocale) {
            $this->identifiedLocale = $foundLocale;
            return $this->identifiedLocale;
        }

        return false;
    }

    public function getIdentifiedLocale()
    {
        return $this->identifiedLocale;
    }
}

4. Add the guesser to the config
--------------------------------
Add the Service alias tag name to the order in `app/config/config.yml`:
``` yaml
lunetics_locale:
    guessing_order:
        - session
        - cookie
        - acme
        - ...
```
