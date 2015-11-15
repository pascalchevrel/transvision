<?php
namespace Transvision;

// Define our source and target locales
$locales = [$source_locale, $locale];
if ($page == '3locales') {
    $locales[] = $locale2;
}

// Define our regex and our locales
$search = (new Search)
    ->setSearchTerms(Utils::cleanString($_GET['recherche']))
    ->setRegexWholeWords($check['whole_word'])
    ->setRegexCaseInsensitive($check['case_sensitive'])
    ->setRegexPerfectMatch($check['perfect_match'])
    ->setLocales($locales)
    ->setResultsLimit(200)
;

$repo_loop = ($repo == 'global')
    ? Project::getRepositories()
    : [$repo];

$search_yields_results = false;

// This will hold the components names for the search filters
$components = [];

$search_results = [];

// We loop through all repositories searched and merge results
foreach ($repo_loop as $repository) {
    $search->setRepository($repository);
    // This is the reference data
    $data = [
        Utils::getRepoStrings($source_locale, $repository),
        Utils::getRepoStrings($locale, $repository)
    ];

    foreach ($search->getResults() as $key => $value) {
        $search_results = array_merge($search_results, ShowResults::getTMXResults(array_keys($value), $data));
        $components += Project::getComponents($search_results);
        if (count($value) > 0) {
            // We have results, we won't display search suggestions but search results
            $search_yields_results = true;
            $search_id = strtolower(str_replace('-', '', $key));
            $real_search_results = count($search->getResults()[$key]);

            $message_count = $real_search_results > $search->getLimit()
            ? "<span>{$search->getLimit()} results</span> out of {$real_search_results}"
            : "<span>" . Utils::pluralize(count($search_results), 'result') . '</span>';

            $output[$key] = "<h2>Displaying {$message_count} for the string "
            . "<span class=\"searchedTerm\">{$initial_search_decoded}</span> in {$key}:</h2>";
            $output[$key] .= ShowResults::resultsTable($search_id, $search_results, $initial_search, $source_locale, $locale, $check);
        } else {
            $output[$key] = "<h2>No matching results for the string "
            . "<span class=\"searchedTerm\">{$initial_search_decoded}</span>"
            . " for the locale {$key}</h2>";
        }
    }
    unset($data);
}

// Display a search hint for the closest string we have if we have no search results
if (! $search_yields_results) {
    $merged_strings = [];

    foreach ($data as $key => $values) {
        $merged_strings = array_merge($merged_strings, array_values($values));
    }

    $best_matches = Strings::getSimilar($initial_search, $merged_strings, 3);

    include VIEWS . 'results_similar.php';

    return;
}

// Build logic to filter components
$javascript_include[] = '/js/component_filter.js';
$filter_block = '';

// Remove duplicated components
$components = array_unique($components);

foreach ($components as $value) {
    $filter_block .= " <a href='#{$value}' id='{$value}' class='filter'>{$value}</a>";
}

skipped:
