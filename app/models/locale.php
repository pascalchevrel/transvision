<?php
namespace Transvision;

$english = new Product('Firefox', 'en-US', 'aurora', false);

$results = [];

foreach (Project::getRepositoryLocales('aurora') as $locale) {
    $target = new Product('Firefox', $locale, 'aurora', false);
    $results[$locale]['total_missing']    = count($english->getStrings()) - count($target->getStrings());
    $results[$locale]['devtools_missing'] = count($english->getDevToolsStrings()) - count($target->getDevToolsStrings());
}

unset($english, $target);
