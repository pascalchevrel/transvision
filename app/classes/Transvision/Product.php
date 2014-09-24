<?php
namespace Transvision;

/**
 * Product class
 *
 * @package Transvision
 */
class Product
{
    /* All the products we support in Transvision  */
    protected $product_list = [
        'Firefox',
        'FirefoxAndroid',
        'Lightning',
        'Thunderbird',
        'Seamonkey',
        'FirefoxOS',
        'Mozilla.org',
    ];

    /* Product currently defined, ex: Firefox */
    protected $product = '';

    /* Locale for the product, ex: fr. Defaults to en-US */
    protected $locale = '';

    /* Repository for the product, ex: central */
    protected $repository = '';

    /* All strings stored for the product */
    protected $strings = [];

    /**
     * The constuctor sets Firefox/release/en-US and includes access keys by default
     *
     * @param  string  $product The product we want to analyse (part of $product_list), defaults to Firefox
     * @param  string  $locale  The locale code for the product, defaults to en-US
     * @param  string  $repository The repository for the product,  defaults to release
     * @param  boolean $keys Do we include access keys? Defaults to yes.
     * @return void
     */
    public function __construct($product = 'Firefox', $locale = 'en-US', $repository = 'release', $keys = true)
    {
        $this->setProduct($product);
        $this->setLocale($locale);
        $this->setRepository($repository);
        if (! $keys) {
            $this->excludeAccesskeys();
        }
    }

    /**
     * Setters
     */

    /**
     * Set the locale code we want strings for
     *
     * @param string $locale Locale code
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set the product we want to work with
     *
     * @param string $product_name Name of the product, one of $product_list
     * @return $this
     */
    public function setProduct($product_name)
    {
        $this->product = in_array($product_name, $this->product_list)
            ? $product_name
            : $this->product_list[0];

        return $this;
    }

    /**
     * Set the repository we want to extract strings from and extracts the strings.
     *
     * @param type $repository_name Repository name as defined in Project class
     * @return $this
     */
    public function setRepository($repository_name)
    {
        $this->repository = in_array($repository_name, array_keys(Project::getSupportedRepositories()))
                 ? $repository_name
                 : 'central';
        $this->extractStrings();

        return $this;
    }

    /**
     * Getters
     */

    /**
     * Get the name of the Product we are working on
     *
     * @return string Name of the product, from product_list
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get the locale code we are working on
     *
     * @return string Mozilla locale code
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get the repository we are working with
     *
     * @return string reprository such as central, gaia, mozilla.org
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get the list of products this class manages
     *
     * @return array List of products defined in $product_list
     */
    public function getProductList()
    {
        return $this->product_list;
    }

    /**
     * Get all the strings for the products with their associated entity
     *
     * @return array The product's entities and strings
     */
    public function getStrings()
    {
        return $this->strings;
    }

    /**
     * Get the list of devtools strings for Firefox for the locale/repository
     *
     * @return array All the strings related to devtools that are in the repository
     */
    public function getDevToolsStrings()
    {
        /* Devtools are Firefox only */
        if ($this->product != 'Firefox') {
            return [];
        }

        // Caching because filtering is an heavy operation
        $cache_id = $this->product . $this->locale . $this->repository . 'getDevToolsStrings()';
        if ($caching = Cache::getKey($cache_id)) {
            return Cache::getKey($cache_id);
        }

        $entities = array_keys($this->strings);

        $devtools = [
            'toolkit/chrome/global/devtools/',
            'browser/chrome/browser/devtools/',
            // entities below are a whitelist in browser.dtd
            'browser/chrome/browser/browser.dtd:webDeveloperMenu.label',
            'browser/chrome/browser/browser.dtd:webDeveloperMenu.accesskey',
            'browser/chrome/browser/browser.dtd:devToolsCmd.keycode',
            'browser/chrome/browser/browser.dtd:devToolsCmd.keytext',
            'browser/chrome/browser/browser.dtd:devtoolsConnect.accesskey',
            'browser/chrome/browser/browser.dtd:devToolbarMenu.accesskey',
            'browser/chrome/browser/browser.dtd:errorConsoleCmd.accesskey',
            'browser/chrome/browser/browser.dtd:browserConsoleCmd.commandkey',
            'browser/chrome/browser/browser.dtd:browserConsoleCmd.accesskey',
            'browser/chrome/browser/browser.dtd:inspectContextMenu.accesskey',
            'browser/chrome/browser/browser.dtd:responsiveDesignTool.accesskey',
            'browser/chrome/browser/browser.dtd:responsiveDesignTool.commandkey',
            'browser/chrome/browser/browser.dtd:scratchpad.accesskey',
            'browser/chrome/browser/browser.dtd:eyedropper.accesskey',
            'browser/chrome/browser/browser.dtd:browserToolboxMenu.accesskey',
            'browser/chrome/browser/browser.dtd:devAppMgrMenu.accesskey',
            'browser/chrome/browser/browser.dtd:webide.accesskey',
            'browser/chrome/browser/browser.dtd:devToolboxMenuItem.keytext',
            'browser/chrome/browser/browser.dtd:devToolboxMenuItem.accesskey',
            'browser/chrome/browser/browser.dtd:getMoreDevtoolsCmd.accesskey',
            'browser/chrome/browser/browser.dtd:scratchpad.keytext',
            'browser/chrome/browser/browser.dtd:webide.keytext',
            'browser/chrome/browser/browser.dtd:devToolbar.keytext',
            'browser/chrome/browser/browser.dtd:scratchpad.keycode',
            'browser/chrome/browser/browser.dtd:webide.keycode',
            'browser/chrome/browser/browser.dtd:devToolbar.keycode',
            'browser/chrome/browser/browser.dtd:webide.label',
            'browser/chrome/browser/browser.dtd:devtoolsConnect.label',
            'browser/chrome/browser/browser.dtd:eyedropper.label',
            'browser/chrome/browser/browser.dtd:scratchpad.label',
            'browser/chrome/browser/browser.dtd:devToolbarOtherToolsButton.label',
            'browser/chrome/browser/browser.dtd:devAppMgrMenu.label',
            'browser/chrome/browser/browser.dtd:devToolboxMenuItem.label',
            'browser/chrome/browser/browser.dtd:errorConsoleCmd.label',
            'browser/chrome/browser/browser.dtd:browserConsoleCmd.label',
            'browser/chrome/browser/browser.dtd:inspectContextMenu.label',
            'browser/chrome/browser/browser.dtd:browserToolboxMenu.label',
            'browser/chrome/browser/browser.dtd:getMoreDevtoolsCmd.label',
            'browser/chrome/browser/browser.dtd:devToolbarMenu.label',
            'browser/chrome/browser/browser.dtd:remoteWebConsoleCmd.label',
            'browser/chrome/browser/browser.dtd:responsiveDesignTool.label',
            'browser/chrome/browser/browser.dtd:devToolbarCloseButton.tooltiptext',
            'browser/chrome/browser/browser.dtd:devToolbarToolsButton.tooltip',
        ];

        // Strings to include
        $entities = $this->filterEntity($entities, $devtools, 'start');

        // Clean up our selection of entities
        $entities = array_flip(array_unique($entities));

        // We now store only the strings relevant for the product
        $devtools = array_intersect_key($this->strings, $entities);

        // Remove empty strings, in Transvision an empty string is always missing
        $devtools = array_filter($devtools, 'strlen');

        Cache::setKey($cache_id, $devtools);

        return $devtools;
    }

    /**
     * Other public methods
     */

    /**
     * Exclude access keys from strings results.
     * If you need to reset the strings to what they were
     * before filtering you can fire setRepository() again.
     *
     * @return $this
     */
    public function excludeAccesskeys()
    {
        // Caching because filtering is an heavy operation
        $cache_id = $this->product . $this->locale . $this->repository . 'excludeAccesskeys()';
        if ($caching = Cache::getKey($cache_id)) {
            $this->strings = Cache::getKey($cache_id);

            return $this;
        }

        // Exclude access keys with a black list of entity endings
        $entities = $this->filterEntity(
            array_keys($this->strings),
            ['accesskey', 'key', 'accesskey2', 'accessKey', 'commandKey'],
            'end',
            false
        );

        // Clean up our selection of entities
        $entities = array_flip(array_unique($entities));

        /*
         The code below mimicks the filtering on accesskeys done by compare-locales.
         I am keeping it as reference although compare-locales.py is buggy in
         its filtering since it ignores any entity with 'key' in the name,
         which means that entities about keyboard, synckey, securekey... are
         not counted in l10n.mozilla.org in the total of strings (that's > 300 strings)

        $entities = array_filter(
            array_keys($this->strings),
            function($entity) {
                $entity = strtolower(explode(':', $entity)[1]);
                return ! strpos($entity, 'key');
            }
        );
        */

        // We now store only the strings relevant for the product
        $this->strings = array_intersect_key($this->strings, $entities);
        Cache::setKey($cache_id, $this->strings);

        return $this;
    }

    /**
     * Private methods
     */

    /**
     * Extract strings from the repository.
     * This method is triggered automatically by setRepository()
     *
     * @return $this
     */
    private function extractStrings()
    {

        // Caching because filtering is an heavy operation
        $cache_id = $this->product . $this->locale . $this->repository . 'extractStrings()';
        if ($caching = Cache::getKey($cache_id)) {
            $this->strings = Cache::getKey($cache_id);

            return $this;
        }

        // Get the strings for the product in the repositories
        $this->strings = Utils::getRepoStrings($this->locale, $this->repository);

        switch($this->product) {
            case 'Firefox':
                $exclude = [
                    'browser/chrome/browser-region/region.properties',
                    'browser/branding/aurora',
                    'browser/branding/nightly',
                    'browser/branding/unofficial',
                    // Strings optional for locales, safe to ignore
                    'toolkit/chrome/global/intl.properties:intl.charset.detector',
                    'toolkit/chrome/global-platform/mac/platformKeys.properties:MODIFIER_SEPARATOR',
                    'toolkit/chrome/global/intl.properties:intl.menuitems.alwaysappendaccesskeys',
                    'browser/chrome/browser/preferences/sync.dtd:signedInUnverified.beforename.label',
                    'browser/chrome/browser/translation.dtd:translation.options.attribution.afterLogo',
                    'browser/chrome/browser/preferences/content.dtd:translation.options.attribution.afterLogo',
                    'browser/chrome/browser/syncSetup.dtd:setup.tosAgree3.label',
                    'browser/chrome/browser/preferences/sync.dtd:signedInLoginFailure.aftername.label',
                    'browser/chrome/browser/aboutDialog.dtd:community.exp.start',
                    'browser/chrome/browser/preferences/privacy.dtd:locbar.post.label',
                    'browser/chrome/browser/preferences/aboutPermissions.dtd:header.site.end',
                    'browser/chrome/browser/translation.dtd:translation.translatedToSuffix.label',
                 ];
                $include = [
                    'browser',
                    'other-licenses/branding/firefox',
                    'extensions/reporter',
                    'netwerk',
                    'dom',
                    'toolkit',
                    'security/manager',
                    'browser/branding/official',
                    'services/sync',
                    'webapprt',
                 ];
                break;
            case 'FirefoxAndroid':
                $exclude = [];
                $include = [
                    'mobile',
                    'toolkit',
                    'netwerk',
                    'dom',
                    'security/manager',
                    'services/sync',
                 ];
                break;
            case 'Lightning':
                $exclude = [];
                $include = ['calendar'];
                break;
            case 'Thunderbird':
                $exclude = [];
                $include = [
                    'mail',
                    'chat',
                    'other-licenses/branding/thunderbird',
                    'editor/ui',
                    'toolkit',
                    'netwerk',
                    'dom',
                    'security/manager',
                 ];
                break;
            case 'Seamonkey':
                $exclude = [];
                $include = [
                    'suite',
                    'editor/ui',
                    'toolkit',
                    'netwerk',
                    'dom',
                    'security/manager',
                    'services/sync',
                    'extensions/spellcheck',
                 ];
                break;
            case 'FirefoxOS':
                $exclude = [];
                $include = [];
                break;
            case 'Mozilla.org':
                $exclude = [];
                $include = [];
                break;
            default:
                $exclude = [];
                $include = [];
                break;
        }

        // Those are global exclusions, not categorized per product
        $global_exclusion = [
            'browser/metro',
            'browser/chrome/browser/devtools/styleeditor.dtd:noStyleSheet-tip',
            'extensions/irc/chrome/chatzilla.properties:pref.bugKeyword',
            'mail/branding',
            'mail/test/',
            'mobile/android/branding',
            'mobile/android/defines.inc',
            'mobile/chrome/region.properties',
            'suite/chrome/browser/region.properties',
            'suite/chrome/common/region.properties',
            'toolkit/content/tests/',
            'toolkit/chrome/mozapps/plugins/plugins.dtd:reloadPlugin.pre',
        ];

        $exclude = array_merge($exclude, $global_exclusion);

        $entities = array_keys($this->strings);

        // Strings to include
        $entities = $this->filterEntity($entities, $include, 'start');

        // Strings to exclude
        $entities = $this->filterEntity($entities, $exclude,'start', false);

        // Clean up our selection of entities
        $entities = array_flip(array_unique($entities));

        // We now store only the strings relevant for the product
        $this->strings = array_intersect_key($this->strings, $entities);

        // Remove empty strings, in Transvision an empty string is always missing
        $this->strings = array_filter($this->strings, 'strlen');

        Cache::setKey($cache_id, $this->strings);

        return $this;
    }

    /**
     * Filter entities based on what they start or end with.
     * We compare a list of entities with a list of matches and include of exclude them.
     *
     * @param  array   $entities  The list of entities we want to analyse
     * @param  array   $matches   The list of strings that match the beginning or end of entities
     * @param  string  $direction 'start' or 'end', defines if we look at the beginning or end of the entity
     * @param  boolean $inclusion Filter entities by inclusion (true) or exclusion (false), defaut to true
     * @return array   The list of filtered entities
     */
    private function filterEntity($entities, $matches, $direction, $inclusion = true)
    {
        if (empty($matches)) {
            return $entities;
        }

        return $entities = array_filter(
            $entities,
            function($entity) use($matches, $inclusion, $direction) {
                if ($direction == 'start') {
                    return $inclusion
                        ? Strings::startsWith($entity, $matches)
                        : ! Strings::startsWith($entity, $matches);
                }
                if ($direction == 'end') {
                    return $inclusion
                        ? Strings::endsWith($entity, $matches)
                        : ! Strings::endsWith($entity, $matches);
                }
            }
        );
    }
}
