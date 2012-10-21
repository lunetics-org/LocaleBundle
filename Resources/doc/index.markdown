# Installation

### Add the package to your dependencies

``` yaml
"require": {
    "lunetics/locale-bundle": "2.1.x-dev",
    ....
},
```

### Register the bundle in your kernel

``` php
public function registerBundles()
    {
        $bundles = array(
            // ...
            new Lunetics\LocaleBundle\LuneticsLocaleBundle(),
        );
```

### Update your packages

```
php composer.phar update lunetics/locale-bundle
```

# Configuration

### Allowed locales for your application

You need to define at least one valid locale that is valid for your application

``` yaml
lunetics_locale:
  allowed_locales:
    - en
    - fr
    - de
```

#### Strict Mode

``` yaml
lunetics_locale:
  strict_mode: true # defaults to false
```
You can set a **'strict_mode'**, where only EXACTLY! the allowed locales will be tested. For example:
* If your user has a browser locale with `de_DE` and `de`, the locale `de` will be chosen!
* If your user has a browser locale with `de` and you only explicit allow `de_DE`, no locale will be detected!

We encourage you to use the non-strict mode, that'll also choose the best region locale for your user.

### Guessers

You need to activate and define the order of the locale guessers. This is done in one step in the configuration :

``` yaml
lunetics_locale:
  guessing_order:
    - session
    - cookie
    - router
    - browser
```
With the example above, the guessers will be called in the order you defined as 1. session 2. cookie 2. router 3. browser.

Note that the session and cookie guessers only retrieve previously identified and saved locales by the router or browser guesser

### Locale Cookies / Session 

The session and cookie guesser is usually used when you do not use locales in the uri's and you guess it from the user browser preferences. When doing this,

 it is good to set *session* and/or *cookie* as the first guesser to not try to detect the locale at each request.

#### Cookie
You can set a cookie when a locale has been identified, simply activate it in the configuration:

``` yaml
lunetics_locale:
  cookie:
    set_on_detection: true
```
This is most useful for unregistered and returning visitors.

#### Session

The session guesser will automatically save a previously identified locale into the session and retrieve it from the session. It should be best on the first place on the guessing_order.

### Custom Guessers

Read more about creating your own guesser here:

[Custom Locale Guesser](guesser.md)

### Switch to another locale

You can render a default locale switcher, simply by calling the twig function in your template :

``` html
{{ locale_switcher() }}
```

#### Using your own template

You can define your own template in the configuration :

``` yaml
lunetics_locale:
  switcher:
    template: AcmeDemoBundle:MyBundle:mytemplate.html.twig
```