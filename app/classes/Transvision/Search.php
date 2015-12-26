<?php
namespace Transvision;

/**
 * Search class
 *
 * Allows searching for data in our repositories using a fluent interface.
 * ex:
 * $search = (new Search)
 *     ->setSearchTerms('Bookmark this page')
 *     ->setRegexWholeWords(true)
 *     ->setRegexCase('sensitive')
 *     ->setRegexPerfectMatch(false)
 *     ->setLocales(['en-US', 'fr', 'de'])
 *     ->setResultsLimit(400);
 */
class Search
{
    /**
     * List of locales we search strings for, can be up to 3 locales
     * @var array
     */
    public $locales;

    /**
     * The trimmed string searched, we keep that one as the canonical reference
     * @var string
     */
    public $search_terms;

    /**
     * The generated regex string updated dynamically via updateRegex()
     * @var string
     */
    public $regex;

    /**
     * Case sensibility of the regex
     * @var string
     */
    public $regex_case;

    /**
     * Consider the space separated string as a single word for search
     * @var string
     */
    public $regex_whole_words;

    /**
     * Only return strings that match the search perfectly (case excluded)
     * @var boolean
     */
    public $regex_perfect_match;

    /**
     * The search terms for the regex, those differ from $search_terms as
     * they can be changed dynamically via setRegexSearchTerms()
     * @var string
     */
    public $regex_search_terms;

    /**
     * The repository we search in. Default is Aurora
     * @var string
     */
    public $repository;

    /**
     * Maximum number of search results we return per locale
     * @var int
     */
    public $limit;

    /**
     * We set the default values for a search
     */
    public function __construct()
    {
        $this->locales = [];
        $this->search_terms = '';
        $this->regex = '';
        $this->regex_case = 'i';
        $this->regex_whole_words = '';
        $this->regex_perfect_match = false;
        $this->regex_search_terms = '';
        $this->repository = 'aurora';
        $this->limit = 200;
    }

    /**
     * Store the searched string in $search_terms and in $regex_search_terms
     *
     * @param [type] $string [description]
     * @return $this
     */
    public function setSearchTerms($string)
    {
        $this->search_terms = trim($string);
        $this->regex_search_terms = $this->search_terms;
        $this->updateRegex();

        return $this;
    }

    /**
     * Set the locales we want results for.
     * Normal searches work with 2 locales, but we also have a 3 locales view.
     *
     * @param array $locales Locale codes
     * @return $this
     */
    public function setLocales(array $locales)
    {
        $this->locales = array_unique($locales);

        return $this;
    }

    /**
     * Set the repository in which we want to search for data.
     * Remember that 'global' will search through all supported repositories.
     *
     * @param string $repository Valid repository from Project::getRepositories()
     * @return $this
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set the maximum number of results we want to return per locale
     *
     * @param int $number Maximum number of results
     * @return $this
     */
    public function setResultsLimit($number)
    {
        $this->limit = (int) $number;

        return $this;
    }

    /**
     * Allows setting a new searched term for the regex.
     * This is mostly useful when you have a multi-words search and need to
     * loop through all the words to return results.
     *
     * @param string $string The string we want to update the regex for
     * @return $this
     */
    public function setRegexSearchTerms($string)
    {
        $this->regex_search_terms = $string;
        $this->updateRegex();

        return $this;
    }

    /**
     * Set the regex case sensibility.
     *
     * @param string $flag 'sensitive' == '' in a regex
     *  @return $this
     */
    public function setRegexCase($flag)
    {
        $this->regex_case = ($flag == 'sensitive') ? '' : 'i';
        $this->updateRegex();

        return $this;
    }

    /**
     * Set the regex to only return perfect matches for the searched string
     *
     * @param  boolean $flag Set to True for a perfect match
     * @return $this
     */
    public function setRegexPerfectMatch($flag)
    {
        $this->regex_perfect_match = (boolean) $flag;
        $this->updateRegex();

        return $this;
    }

    /**
     * Set the regex so as that a multi-word search is taken as a single word.
     *
     * @param  string $flag A string evaluated to True will add \b to the regex
     * @return $this
    */
    public function setRegexWholeWords($flag)
    {
        $this->regex_whole_words = $flag ? '\b' : '';
        $this->updateRegex();

        return $this;
    }

    /**
     * Update the $regex_search_terms value every time
     * a setter to the regex is triggered.
     *
     * @return $this
     */
    private function updateRegex()
    {
        // Search for perfectMatch
        if ($this->regex_perfect_match) {
            $search =  '^' . $this->regex_search_terms . '$';
        } else {
            $search = preg_quote($this->regex_search_terms, '~');
        }

        $this->regex =
            '~'
            . $this->regex_whole_words
            . $search
            . $this->regex_whole_words
            . '~'
            . $this->regex_case
            . 'u';

        return $this;
    }

    /**
     * Return search results under this form:
     * $data = [
     * 	  'en-US' => [
     * 	      'entity1' => 'string 1',
     * 	      'entity2' => 'string 2',
     * 	  ],
     * 	  'fr' => [
     * 	      'entity1' => 'string 1',
     * 	      'entity2' => 'string 2',
     * 	   ],
     * ];
     * We can have a third locale in the results for the 3 locales view.
     *
     * @return array Search results per locale
     */
    public function getResults()
    {
        // We use the search string as an array we loop into.
        // Perfect matches have only one element in the array.
        $words = [$this->regex_search_terms];
        if (! $this->regex_perfect_match) {
            $words = Utils::uniqueWords($this->regex_search_terms);
        }

        // We use a closure here so as to not store all big arrays in
        // temporary variables and consume memory.
        $extract_strings = function($locale) use ($words) {
            // Don't load data if we don't have search terms, return empty array
            if (empty($words)) {
                return [];
            }

            $strings = Utils::getRepoStrings($locale, $this->repository);
            foreach ($words as $word) {
                $this->setRegexSearchTerms($word);
                $strings = preg_grep($this->regex, $strings);
            }

            // We reset the regex search terms before exiting the function
            // because we don't want to keep the regex on a single word when we
            // do a global search.
            $this->setRegexSearchTerms($this->search_terms);

            return $strings;
        };

        $data = [];
        foreach($this->locales as $locale) {
            $data[$locale] = $extract_strings($locale);
        }

        return $data;
    }
}
