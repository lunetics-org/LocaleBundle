Form Types
==============
You can use the included `lunetics_locale` Form Type based on a custom `LocaleChoiceList` ChoiceList. It's useful
if you want to make the locale selectable for the User.

1. Configure
------------
There are 2 configurable options for the custom Form Type.
``` yaml
lunetics_locale:
    form:
        strict_mode: false # default
        languages_only: true # default
```

### languages__only option
If the `lunetics_locale.form.languages_only` boolean option is set to **true** (default), it will only make the language selectable (e.g. `de` `en`)


If the `lunetics_locale.form.languages_only` boolean option is set to **false**, it will list **all** supported regions on your **system** for the locales (e.g. `de`, `de_AT`, `de_CH`, `en`, `en_GB`, `en_US`) etc. Even if the main configuration `lunetics_locale.strict_mode` is set to true.

### strict_mode option
The `lunetics_locale` custom Form Type has a dedicated `strict_mode` setting. This settings only explicitly returns the locales listed in the configuration, as opposed to all available locales (with regions)

If the `lunetics_locale.form.strict_mode` is set to **false** (default), it'll return the choices dependent on the `languages_only` config parameter.


If the `lunetics_locale.form.strict_mode` is set to **true**, it'll return the choices exactly how you defined them in the `lunetics_locale.allowed_locales` configuration.

2. Add the Formtype
-------------------
Example for adding the Locale FormType to the FOS UserBundle
``` php
        $builder
            ->add('locale', 'lunetics_locale', array('label' => 'language', 'translation_domain' => 'FOSUserBundle'))
```

3. Addendum
-----------
The custom `lunetics_locale` Form Type will return **preferredViews** based on the preferred locales in the browser and **remainingViews**
for the rest of the locales (sorted Alphanumeric).

The locale / language is output in the origin language of the locale / language.
