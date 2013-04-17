# Installation

### Add the package to your dependencies

``` yaml
"require": {
    "lunetics/locale-bundle": "2.2.*",
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
You can enable `strict_mode`, where only the **exact** allowed locales will be matched. For example:

* If your user has a browser locale with `de_DE` and `de`, and you only explicitly allow `de`, the locale `de` will be chosen.
* If your user has a browser locale with `de` and you only explicitly allow `de_DE`, no locale will be detected.

We encourage you to use the non-strict mode, that'll also choose the best region locale for your user.

### Guessers

You need to activate and define the order of the locale guessers. This is done in one step in the configuration :

``` yaml
lunetics_locale:
  guessing_order:
    - session
    - cookie
    - browser
    - query
    - router
```
With the example above, the guessers will be called in the order you defined as 1. session 2. cookie 2. router 3. browser.

Note that the session and cookie guessers only retrieve previously identified and saved locales by the router or browser guesser.

If you use the _locale parameter as attribute/parameter in your routes, you should use the query and router guesser first.

``` yaml
lunetics_locale:
  guessing_order:
    - query
    - router
    - session
    - cookie
    - browser
```

### Locale Cookies / Session 

The session and cookie guesser is usually used when you do not use locales in the uri's and you guess it from the user browser preferences. When doing this,

 it is good to set *session* and/or *cookie* as the first guesser to not try to detect the locale at each request.

#### Cookie
If you use the cookie guesser, it will be automatically read from the cookie and write changes into the cookie anytime the locale has changed (Even from another guesser)

``` yaml
lunetics_locale:
  cookie:
    set_on_change: true
```
This is most useful for unregistered and returning visitors.

#### Session

The session guesser will automatically save a previously identified locale into the session and retrieve it from the session. This guesser should always be first in your `guessing_order` configuration if you don't use the router guesser.

### FilterLocaleSwitchEvent / LocaleUpdateListener
The `LocaleGuesserManager` dispatches a `LocaleBundleEvents::onLocalChange` if you use either the `session` or `cookie` guesser. The LocaleUpdateListeners checks if the locale has changed and updates the session or cookie.


For example, if you don't use route / query parameters for locales, you could build an own listener for your user login, which dispatches a `LocaleBundleEvents::onLocalChange` event to set the locale for your user. You just have to use the `FilterLocaleSwitchEvent` and set the locale.

``` php
$locale = $user->getLocale();
$localeSwitchEvent = new FilterLocaleSwitchEvent($locale);
$this->dispatcher->dispatch(LocaleBundleEvents::onLocaleChange, $localeSwitchEvent);
```
### Custom Form Types

Read more about using the custom choice Form Type here:

[Read the full documentaion on usage of the custom choice Form Type](forms.md)

### Custom Guessers

Read more about creating your own guesser here:

[Read the full documentation for creating a custom Locale Guesser](guesser.md)

### Switch to another locale

You can render a default locale switcher, simply by calling the twig function in your template :

``` html
{{ locale_switcher() }}
```

[Read the full documentation for the switcher](switcher.md)