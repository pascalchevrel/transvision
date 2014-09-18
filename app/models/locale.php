<?php
namespace Transvision;

$english = new Product('Firefox', 'en-US', 'aurora');
$total_english  = count($english->excludeAccesskeys()->getStrings());
$total_devtools = count($english->excludeAccesskeys()->getDevToolsStrings());
$results = [];

foreach (Project::getRepositoryLocales('aurora') as $locale) {
    $target = new Product('Firefox', $locale, 'aurora');
    $results[$locale]['total_missing'] = $total_english - count($target->excludeAccesskeys()->getStrings());
    $results[$locale]['devtools_missing'] = $total_devtools - count($target->excludeAccesskeys()->getDevToolsStrings());
}

unset($english, $target);

