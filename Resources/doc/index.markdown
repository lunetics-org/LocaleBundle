# Installation

### Add the package to your dependencies

````
"require": {
    "lunetics/locale-bundle": "2.1.x-dev",
    ....
},
````

### Register the bundle in your kernel

````
public function registerBundles()
    {
        $bundles = array(
            // ...
            new Lunetics\LocaleBundle\LuneticsLocaleBundle(),
        );
````

### Update your packages

````
php composer.phar update lunetics/locale-bundle
````

# Configuration

### Allowed locales for your application

You need to define at least one valid locale that is valid for your application

````
lunetics_locale:
  allowed_locales:
    - en
    - fr
    - de
````

### Guessers

You need to activate and define the order of the locale guessers. This is done in one step in the configuration :

````
lunetics_locale:
  guessing_order:
    - cookie
    - router
    - browser
````
With the example above, the guessers will be called in the order you defined as 1. cookie 2. router 3. browser

### Locale Cookies

You can set a cookie when a locale has been identified, simply activate it in the configuration:

````
lunetics_locale:
  cookie:
    set_on_detection: true
````
This behavior is usually used when you do not use locales in the uri's and you guess it from the user browser preferences. When doing this,
 it is good to set *cookie* as the first guesser to not try to detect the locale at each request.

### Switch to another locale

You can render a default locale switcher, simply by calling the twig function in your template :

````
{{ locale_switcher() }}
````

#### Using your own template

You can define your own template in the configuration :

````
lunetics_locale:
  switcher:
    template: AcmeDemoBundle:MyBundle:mytemplate.html.twig
````

