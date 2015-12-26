<?php
namespace tests\units\Transvision;

use atoum;
use Transvision\Search as _Search;

require_once __DIR__ . '/../bootstrap.php';

class Search extends atoum\test
{
    public function testConstructor()
    {
        $obj = new _Search();
        $this
            ->array($obj->locales)
                ->isEqualTo([]);
        $this
            ->string($obj->search_terms)
                ->isEqualTo('');
        $this
            ->string($obj->regex)
                ->isEqualTo('');
        $this
            ->string($obj->regex_case)
                ->isEqualTo('i');
        $this
            ->string($obj->regex_whole_words)
                ->isEqualTo('');
        $this
            ->boolean($obj->regex_perfect_match)
                ->isEqualTo(false);
        $this
            ->string($obj->regex_search_terms)
                ->isEqualTo('');
        $this
            ->string($obj->repository)
                ->isEqualTo('aurora');
        $this
            ->integer($obj->limit)
                ->isEqualTo(200);
    }

    public function testSetSearchTerms()
    {
        $obj = new _Search();
        $obj->setSearchTerms(' foobar ');
        $this
            ->string($obj->search_terms)
                ->isEqualTo('foobar');
        $this
            ->string($obj->regex_search_terms)
                ->isEqualTo('foobar');
    }

    public function testSetLocales()
    {
        $obj = new _Search();
        $obj->setLocales(['en-US', 'fr', 'fr']);
        $this
            ->array($obj->locales)
                ->isEqualTo(['en-US', 'fr']);
    }

    public function testSetRepository()
    {
        $obj = new _Search();
        $obj->setRepository('central');
        $this
            ->string($obj->repository)
                ->isEqualTo('central');
    }

    public function testSetResultsLimit()
    {
        $obj = new _Search();
        $obj->setResultsLimit(50);
        $this
            ->integer($obj->limit)
                ->isEqualTo(50);

        $obj->setResultsLimit('100');
        $this
            ->integer($obj->limit)
                ->isEqualTo(100);
    }

    public function testSetRegexSearchTerms()
    {
        $obj = new _Search();
        $obj->setRegexSearchTerms('A new hope');
        $this
            ->string($obj->regex_search_terms)
                ->isEqualTo('A new hope')
            ->string($obj->regex)
                ->isEqualTo('~A new hope~iu');
    }

    public function testSetRegexCase()
    {
        $obj = new _Search();
        $obj->setRegexCase('sensitive');
        $this
            ->string($obj->regex)
                ->isEqualTo('~~u');

        $obj->setRegexCase('foobar');
        $this
            ->string($obj->regex)
                ->isEqualTo('~~iu');
    }

    public function testSetRegexPerfectMatch()
    {
        $obj = new _Search();
        $obj->setRegexPerfectMatch('perfect_match');
        $this
            ->boolean($obj->regex_perfect_match)
                ->isEqualTo(true)
            ->string($obj->regex)
                ->isEqualTo('~^$~iu');

        $obj->setRegexPerfectMatch(false);
        $this
            ->boolean($obj->regex_perfect_match)
                ->isEqualTo(false)
            ->string($obj->regex)
                ->isEqualTo('~~iu');
    }

    public function testSetRegexWholeWords()
    {
        $obj = new _Search();
        $obj->setRegexWholeWords('whole_word');
        $this
            ->string($obj->regex_whole_words)
                ->isEqualTo(true)
            ->string($obj->regex)
                ->isEqualTo('~\b\b~iu');

        $obj->setRegexWholeWords(false);
        $this
            ->string($obj->regex_whole_words)
                ->isEqualTo(false)
            ->string($obj->regex)
                ->isEqualTo('~~iu');
    }

    public function testMultipleRegexChanges()
    {
        $obj = new _Search();
        $obj
            ->setSearchTerms('A new hope')
            ->setRegexWholeWords('whole_word')
            ->setRegexPerfectMatch(false)
            ->setRegexCase('sensitive');

        $this->string($obj->regex)
                ->isEqualTo('~\bA new hope\b~u');

        $obj->setSearchTerms('Return of the jedi')
            ->setRegexWholeWords('')
            ->setRegexPerfectMatch(true)
            ->setRegexCase('');
        $this
            ->string($obj->regex)
                ->isEqualTo('~^Return of the jedi$~iu');
    }

    public function testGetResults()
    {
        $obj = new _Search();
        $obj->setLocales(['en-US', 'fr']);
        $obj->setRepository('central');
        // No search terms
        $this
            ->array($obj->getResults())
                ->isEqualTo(['en-US' => [], 'fr' => []]);

        $obj->setSearchTerms('A new hope');
        // Terms we don't have
        $this
            ->array($obj->getResults())
                ->isEqualTo(['en-US' => [], 'fr' => []]);

        $obj->setSearchTerms('Ouvrir dans le Finder');
        // Terms we have
        $this
            ->array($obj->getResults())
                ->isEqualTo([
                    'en-US' => [],
                    'fr' => [
                        'browser/chrome/browser/downloads/downloads.dtd:cmd.showMac.label' => 'Ouvrir dans le Finder'
                    ]
                ]);
    }
}
