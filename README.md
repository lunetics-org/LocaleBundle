## Refactoring

This bundle is in refactoring state. You can see the vision the bundle will take by looking inside the comments of the PR #5 or looking into the Wiki.

You can follow the refactoring by looking into the `rework` branch. The `master` branch remains the last stable version for the Symfony 2.0.* framework.

# Information

This Symfony2 Bundle consists currently of 3 Parts

1. A Request Eventlistener which sets the Language
2. A Controller / Route for the actual "Switch Language" action
3. A Twig Plugin to show the available Languages (forked from Craue/TwigExtensionsBundle)

## 1. The Request Eventlistener (Listener/LocaleDetectorListener.php)

The Listener uses the ``$request->getPreferredLanguage()`` and ``$request->getLanguages()`` methods which gather infomation about the browser language (see http://en.wikipedia.org/wiki/Content_negotiation).

The Listener also checks against the ``allowed_language`` list to ensure that the application locale will only be set to an allowed locale / language.

After the locale is identified, the  is saved in the Session, because we only want to do the locale lookup only once per user and not on every page request.

If the user changes the language manually (with the "Switch Language" action from this bundle), the app will locked to that language.

Additionally a cookie with the user-selected language is set. (For returning visitors).

**If you have a project with registered users, you should always set the language and region provided by the user, e.g. set the application locale after login.**

see: http://symfony.com/doc/2.0/book/translation.html

### Scenario: No Locale provided from Browser
If no languages are supplied from the Browser, the locale will be set to the default ``%locale%`` defined in ``app/config.yml``.

### Scenario: Preferred Language matched
If the preferred language from ``$request->getPreferredLanguage()`` matches a language from our ``allowed_languages`` list defined in ``app/config.yml``, the application locale will be set to the preferred locale. 

**Example:**
The preferred language is set to **'de_AT'**, the Listener will check for the primary language (which is **'de'**) and set it to **'de_AT'**, since we also want to use/set the Region of the user.

### Scenario: No matching Preferred Language, but still a match from our allowed_languages list

If the **preferred language** doesn't match any of our ``allowed_languages``, the **provided languages** from ``$request->getLanguages()`` will be checked if theres a match to any of our ``allowed languages`` and sets it to the first matching ``allowed_language`` the user has set (highest priority).

**Example:**

We allow the following Languages:

- de
- en
- fr

The preferred Language is set to **'ru_RU'**, but the Browser provides additional locales/languages: 

- ru
- da_DK
- da
- en_US
- en
- de_DE
- de

Then the Locale will be set to **'en_US'**, cause it is the first matched language. It also provides a Region setting (US) which can be used for formatting a date or use the currency for that region.

## 2. The Controller / Route for "Switch Language"

This Controller lets the User choose the language manually. It'll also check for the predefined available languages and redirects to the target specified in in the ``app/config.yml`` ``switch_router:`` section.

## 3. The Twig Plugin to show the available Languages

This Twig extension is forked from craue/TwigExtensionsBundle. The plugin displays the allowed languages in ``<li>`` tags. 

The options are described below in the ``app/config.yml`` section.

There are already a few translations for the ``show_languagetitle`` option, feel free to correct / add translations :)


Installation
============

## 1. deps

	[LuneticsLocaleBundle]
	    git=http://github.com/lunetics/LocaleBundle.git
	    target=bundles/Lunetics/LocaleBundle

(add to deps file and run `./bin/vendors install`)


## 2. AppKernel

Add the Bundle to your application's kernel:

    // app/AppKernel.php
	
	public function registerBundles()
    	{
        	$bundles = array(
			//...
        	$bundles[] = new Lunetics\LocaleBundle\LuneticsLocaleBundle();
    		//...
		}


## 3. Autoloading

Add the namespace **Lunetics** to the Autoloader:

	
	$loader->registerNamespaces(array(
    	//...
    	'Lunetics\LocaleBundle'             => __DIR__.'/../vendor/bundles'
		//...
	));


## 4. Setup the config.

Edit the **app/config.yml** file and add the following:

	lunetics_locale:	
		# Sets the Languages which are allowed
	   	allowed_languages:
	    # Add Language 'de' (German)
	    	- de
	       	# Add Language 'en' (English)
	       	- en
	       	# Add Language 'fr' (French)
	       	- fr
	
		# Options for the Change Language Twig Extension
		change_language:
			# Show The Language in their native Language (e.g. Deutsch / English / Français )			
			# Default setting is set to false 
			show_foreign_languagenames: true
			
			# Force Uppercase of the Languagenames
			# Default setting is set to false
			show_first_uppercase: true 
			
			# Adds an additional block / title ( Sprache ändern / Change Language / Changer la langue )
			# Default setting is set to false
			show_languagetitle: true
			
	   switch_router:
	
			# Redirect to referrer after changing the language
			# Default setting is set to true
			use_referrer: true
			
			# Redirect to a custom Route after changing the language.
			# use_referrer must be set to false
			redirect_to_route: "MyBundle_homepage"

			# Redirect to a custom url after changing the language.
			# use_refferer must be set to false and redirect_to_route not be set
	       	redirect_to_url: "/"


## 5. Add the Route for the Language-switcher:

If you want to use the included Languageswitcher, you have to add this to your ``app/config/routing.yml`` file:

	LuneticsLocaleBundle:
	    resource: "@LuneticsLocaleBundle/Resources/config/routing.yml"
	    prefix:   /
