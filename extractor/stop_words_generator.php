<?php
/**
 * Stop words source: http://stop-words.googlecode.com/svn/trunk/stop-words/
 */

require_once __DIR__.'/../libs/PorterStemmer/PorterStemmer.php';

class StopWordsGenerator
{
    /**
     * Generates curated stop words list
     * @param string $dir - Directory with files containing stop words
     * @return array - The stop words
     */
    static function generate($dir)
    {
        $stop_words = self::load($dir); // Load
        $stop_words = array_unique($stop_words); // Remove duplicates
        sort($stop_words, SORT_STRING);
        return $stop_words;
    }
    
    /**
     * Filters words from a string using a list of ignore words
     * E.g. Remove medical words from list of stop words
     * @param array $stop_words - Stop words
     * @param string $ignore_words - Words to filter
     * @return array - Stop words excluding ignored
     */

    /**
     * Scans a directory for files containing stop words and returns all words
     * @param string $dir - Directory to scan
     * @return array Amalgamated list of stop words
     */
    static function load($dir)
    {
        $files = scandir($dir);
        $words = array();
        foreach($files as $file)
        {
            if ($file == '.' || $file == '..')
            {
                continue;
            } 
            $next_words = file($dir.'/'.$file, FILE_IGNORE_NEW_LINES);
            $words = array_merge($words, $next_words);
        }
        return $words;
    }
    
    /**
     * Outputs word and stem
     * @param array $words - Words to stem
     * @return Prints TSV of words and their stems
     */
    static function stem($words)
    {
        foreach($words as $wrd)
        {
            $stem = PorterStemmer::Stem($wrd);
            print_r("$wrd\t$stem\n");
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
$words = StopWordsGenerator::generate('/var/www/html/pubmedreco/extractor/stopwords');
StopWordsGenerator::stem($words);