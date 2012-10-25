Locale Switcher
==============
The locale switcher gives you different options for your twig template to change the locale.

1. Use the switcher in a Twig Template
-----------------------------------------
Add the following twig method to your template:
``` html
{{ locale_switcher() }}
```
This will give you a basic switcher.

2. Configuration Options
------------------------
It's possible to change the behaviour and the template of the switcher.

### 2.1 Use another template

There are two ways to override the default template:

#### By bundle inheritance
To override the default template, create a template in your own src directory at the following location: `src/Lunetics/LocaleBundle/Resources/views/Switcher/switcher_links.html.twig`

#### By configuration
Define your own template in the configuration :
``` yaml
lunetics_locale:
  switcher:
    template: AcmeDemoBundle:MyBundle:mytemplate.html.twig
```

### 2.1 Show current locale
You can set if the current locale should be shown in the template. Defaults to false.
``` yaml
lunetics_locale:
  switcher:
    show_current_locale: false
```

3. Define the actor
-------------------
There are 3 actors to change the locale. **newLocale** stands for the generated link locale, e.g. **de** or **en**, based on your configuration.

* **default actor:** Change locale via _locale Request attribute (?_locale=newLocale OR /newLocale/about)
* **controller actor:** Change locale via controller and redirect (/changeLocale/newLocale)
* **route actor:** Define route in the method locale_switcher (generates custom route)

### 3.1 Default actor

By default, the `TargetInformationBuilder` generates the switcher links to the same route by using the _locale attribute.

If your route has the following config:

``` yaml
my_route:
    pattern: /{_locale}/about
    defaults:
        _controller: my_controller:myAction
        _locale: %locale%
```

Then the switcher will generate links to all your allowed locales. If you have enabled **de** and **en**, it would generate links to:
* /de/about
* /en/about

If your route doesn't use a _locale parameter, it will use a query parameter (default symfony behaviour):
* /about?_locale=de
* /about?_locale=en

You should use the query guesser and/or route guesser to set the locale.
If you also use the session guesser or cookie guesser, the locale will be saved in the session / cookie.

### 3.2 Controller actor

You should use the controller actor if you don't use the _locale attribute and want to rely on sessions / cookies only.

The controller actor redirects to the `/changeLocale/**newLocale**` url, sets the new locale into the session / cookie and :
* redirects back to the origin if the `use_referrer` config var is set to **true**
* redirect to the route given in the `redirect_to_route` config var

**You MUST set the `redirect_to_route` config var, which will used if you don't want to use the referrer. It's also used as fallback if no referrer could be identified**

These are the current options for the **Controller** actor:
``` yaml
lunetics_locale:
    switcher:
        use_controller: true                # Must be set to true if you want to use the controller. Defaults to false
        use_referrer: true                  # Redirect to the origin url from where the switcher was used. Defaults to true
        redirect_to_route: fallback_route   # This parameter MUST be set. Fallback route if no referrer could be found.
        redirect_statuscode: 302            # Redirect HTTP status code. Options:  300, 301, 302, 303, 307. Defaults to 302
```

### 3.3 Route actor
You can override the default / controller actor, if you give a route to the twig switcher method, for example:

``` html
{{ locale_switcher('my_route') }}
```

The route actor will then generate the routes to your custom route with the _locale parameter, this can be either the locale in the url or as query parameter **(see default actor)**

It's also possible to add additional parameters/attributes that can be used for route generation:
 ``` html
{{ locale_switcher('my_route', {'parameter1':'value1'}) }}
```