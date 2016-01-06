<?php
namespace Transvision;

// Default search value
$_GET['recherche'] = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$my_search = Utils::cleanString($_GET['recherche']);

// Cloned value for reference
$initial_search = $my_search;
$initial_search_decoded = htmlentities($initial_search);

// Checkboxes states
$check = [];

foreach ($form_checkboxes as $val) {
    $check[$val] = isset($_GET[$val]);
}

// Check for default_repository cookie, if not set default repo to 'aurora'
$check['repo'] = isset($_COOKIE['default_repository'])
    ? $_COOKIE['default_repository']
    : 'aurora';

if (isset($_GET['repo']) && in_array($_GET['repo'], $repos)) {
    $check['repo'] = $_GET['repo'];
}

// Default search type: strings
$check['search_type'] = 'strings';
if (isset($_GET['search_type'])
    && in_array($_GET['search_type'], ['strings', 'entities', 'strings_entities']
    )) {
    $check['search_type'] = $_GET['search_type'];
} elseif (isset($_COOKIE['default_search_type'])) {
    $check['search_type'] = $_COOKIE['default_search_type'];
}

// Locales list for the select boxes
$loc_list = Project::getRepositoryLocales($check['repo']);

// Define our regex
$search = (new Search)
    ->setSearchTerms(Utils::cleanString($_GET['recherche']))
    ->setRegexWholeWords($check['whole_word'])
    ->setRegexCaseInsensitive($check['case_sensitive'])
    ->setRegexPerfectMatch($check['perfect_match']);

// build the repository switcher
$repo_list = Utils::getHtmlSelectOptions($repos_nice_names, $check['repo'], true);

// Get the locale list for every repo and build his target/source locale switcher values.
$loc_list = [];
$source_locales_list = [];
$target_locales_list = [];
foreach (Project::getRepositories(true) as $repository) {
    $loc_list[$repository] = Project::getRepositoryLocales($repository);

    // build the source locale switcher
    $source_locales_list[$repository] = Utils::getHtmlSelectOptions(
        $loc_list[$repository],
        Project::getLocaleInContext($source_locale, $repository)
    );

    // build the target locale switcher
    $target_locales_list[$repository] = Utils::getHtmlSelectOptions(
        $loc_list[$repository],
        Project::getLocaleInContext($locale, $repository)
    );

    // 3locales view: build the target locale switcher for a second locale
    $target_locales_list2[$repository] = Utils::getHtmlSelectOptions(
        $loc_list[$repository],
        Project::getLocaleInContext($locale2, $repository)
    );
}

// Build the search type switcher
$search_type_descriptions = [
    'strings'          => 'Strings',
    'entities'         => 'Entities',
    'strings_entities' => 'Strings & Entities',
];

$search_type_list = Utils::getHtmlSelectOptions(
    $search_type_descriptions,
    $check['search_type'],
    true
);

// Get COOKIES
$get_cookie = function ($var) {
    return isset($_COOKIE[$var]) ? $_COOKIE[$var] : '';
};

$cookie_repository     = $get_cookie('default_repository');
$cookie_source_locale  = $get_cookie('default_source_locale');
$cookie_target_locale  = $get_cookie('default_target_locale');
$cookie_target_locale2 = $get_cookie('default_target_locale2');
$cookie_search_type    = $get_cookie('default_search_type');
