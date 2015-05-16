# Installation

## Add the package to your dependencies

``` yaml
"require": {
    "lunetics/locale-bundle": "2.4.*",
    ....
},
```

## Register the bundle in your kernel

``` php
public function registerBundles()
    {
        $bundles = array(
            // ...
            new Lunetics\LocaleBundle\LuneticsLocaleBundle(),
        );
```

## Update your packages

```
php composer.phar update lunetics/locale-bundle
```

# Configuration

## Allowed locales for your application

You need to define at least one valid locale that is valid for your application

``` yaml
lunetics_locale:
  allowed_locales:
    - en
    - fr
    - de
```

### Strict Mode

``` yaml
lunetics_locale:
  strict_mode: true # defaults to false
```
You can enable `strict_mode`, where only the **exact** allowed locales will be matched. For example:

* If your user has a browser locale with `de_DE` and `de`, and you only explicitly allow `de`, the locale `de` will be chosen.
* If your user has a browser locale with `de` and you only explicitly allow `de_DE`, no locale will be detected.

We encourage you to use the non-strict mode, that'll also choose the best region locale for your user.

Pay attention when using the non-strict mode. `allowed_locales` will be ignored in the detection phase;
it will be only used to help you to create a choice/list with the preferred locales.

### Strict Match and Strict Mode

When the browser has `en_US` as configured locale and config.yml is:

```yaml
lunetics_locale:
  strict_mode: true
  allowed_locales:
    - en
    - en_GB
```
With this configuration, no locale will be matched (symfony fallback locale will be used).

To avoid this, you can use the `strict_match` option with the following configuration, which will allow a partial matching of locales.


```yaml
lunetics_locale:
  strict_mode: false
  strict_match: true
  allowed_locales:
    - en
    - en_GB
```

- If the browser has `en_US` ( or just `en`), the matched locale will be `en`;
- If the browser has `en_GB`, the matched locale will be `en_GB`.

When applicable, this configuration should be preferred to pure non-strict mode.

## Guessers

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
With the example above, the guessers will be called in the order you defined as 1. session 2. cookie 3. browser 4. query 5. router.

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

Note that you can disable the guessing for a given query pattern :

``` yaml
lunetics_locale:
  guessing_excluded_pattern: ^/api
```

You can also create your own guesser:
[Read the full documentation for creating a custom Locale Guesser](guesser.md)

### Locale Cookies / Session

The session and cookie guessers are typically used when you do not rely on URI-based locales and instead guess the locale from user browser preferences. When doing this,
it is recommended to set *session* and/or *cookie* as the first guesser to avoid having the bundle attempt to detect the locale for each request.

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

### Subdomain

The subdomain guesser will try to determine the locale based on the subdomain hostname. `[locale].domain.com`.

### Topleveldomain

The topleveldomain guesser will map the tld to a locale. So `domain.de` maps to `de` and `domain.fr` maps to `fr`.  
Note that the guesser will first try to set the tld as a locale.  
Where this does not make sense or needs further detection the locale map comes into play.  
This applies for tlds as `com` which is no locale or with multilingual countries as `be` that would need a default locale.  

You can add custom mappings via config, like:

``` yaml
topleveldomain:
  locale_map:
    - com: en
    - org: en_US
    - net: en_US
    - uk: en_GB
    - nz: en_NZ
    - ch: de_CH
    - at: de_AT
    - be: fr_BE
```

### Domain

The domain guesser will map a domain to a locale.

``` yaml
domain:
  locale_map:
    - dutchversion.be: nl_BE
    - frenchversion.be: fr_BE
    - dutchversion.nl: nl_NL
```

### FilterLocaleSwitchEvent / LocaleUpdateListener
The `LocaleGuesserManager` dispatches a `LocaleBundleEvents::onLocaleChange` if you use either the `session` or `cookie` guesser. The LocaleUpdateListeners checks if the locale has changed and updates the session or cookie.

For example, if you don't use route / query parameters for locales, you could build an own listener for your user login, which dispatches a `LocaleBundleEvents::onLocaleChange` event to set the locale for your user. You just have to use the `FilterLocaleSwitchEvent` and set the locale.


``` php
$locale = $user->getLocale();
$request = $this->getRequest();
$localeSwitchEvent = new FilterLocaleSwitchEvent($request, $locale);
$this->dispatcher->dispatch(LocaleBundleEvents::onLocaleChange, $localeSwitchEvent);
```

## Vary on Accept-Language

By default, this bundle adds `Accept-Language` to the list of `Vary` headers.
You can can disable this behaviour with `disable_vary_header`:

``` yaml
lunetics_locale:
  disable_vary_header: true
```

# Usage

## Display Links to Switch to another locale

You can render a default locale switcher, simply by calling the twig function in your template :

``` html
{{ locale_switcher() }}
```

[Read the full documentation for the switcher](switcher.md)

## Custom Form Types

Read more about using the custom choice Form Type here:

[Read the full documentation on usage of the custom choice Form Type](forms.md)

