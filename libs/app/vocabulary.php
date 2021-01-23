<?php 
/**
 * Helper functions to access vocabularies
 * Only functions are provided instead of object-oriented classes for optimization of database connectivity
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

require_once __DIR__.'/../Inflector/Inflector.php';
require_once __DIR__.'/../PorterStemmer/PorterStemmer.php';
require_once __DIR__.'/wordnet.php';

define('STOP_WORDS_TABLE', 'stop_words');
define('STOP_WORDS_FIELD', 'word_phrase');
define('STOP_WORDS_STEM_FIELD', 'stem');

define('EN_NAMES_TABLE', 'en_names');
define('EN_NAMES_FIELD', 'name');

define('MERRIAM_TABLE', 'merriam_webster');
define('MERRIAM_FIELD', 'word_phrase');

/**
 * Parses sentence and extracts medical terms
 * Removes stop words and names for privacy
 * @param string $sentence - Sentence to parse
 * @param boolean $exact - Whether to match words exactly
 * @param object $connection - Database handle
 * @return string - Curated sentences
 * @test - testExtractMerriam1, testExtractMerriam2
 */
function extract_med_words($sentence, $exact, $connection)
{
    $words = preg_split ('/[,\s;:\(\)\[\]!?<>.]/', $sentence); // Split sentence words with comma, colon, semi-colon, space

    $med_words = array();

    foreach($words as $key => $word)
    {
        $med_word = get_med_word($word, $exact, $connection);
        if ($med_word == NULL)
        {
            continue;
        }
        $med_words[] = $med_word;
    }
    $unique_med_words = array_unique($med_words); // Remove duplicates
    sort($unique_med_words, SORT_STRING); // Sort
    
    return $unique_med_words;
}

/**
 * Checks if given word is a medical word based on the supplied dictionary
 * @param string $word - The word to search for
 * @param boolean $exact - Whether to match words exactly
 * @param object $connection - Database handler
 * @return NULL|string - If word is found, it is returned back, else NULL
 * @test - testGetMedWord1
 */
function get_med_word($word, $exact, $connection)
{
    $word = Inflector::singularize($word);
    $stem = PorterStemmer::Stem($word);
    
    if (ctype_alpha($word) == FALSE) // Ignore words with numbers
    {
        return NULL;
    }

    $stop_match = exists($word, STOP_WORDS_FIELD, STOP_WORDS_TABLE, $connection);
    $stop_stem_match = exists($stem, STOP_WORDS_STEM_FIELD, STOP_WORDS_TABLE, $connection);    
    $name_match = exists($word, EN_NAMES_FIELD, EN_NAMES_TABLE, $connection);
    $dict_match = get_dict_match($word, $exact, $connection);

    if ($stop_match == TRUE || $stop_stem_match == TRUE || $name_match == TRUE)
    {
        return NULL;
    }
    if ($dict_match == TRUE)
    {
        $weight = get_init_weight($word, $connection);
        return strtolower("$word ($weight)");
    }
    return NULL;
}

/**
 * Find lexical classes of word and return highest weight
 * @param string $word
 * @param object $connection
 * @return float
 */
function get_init_weight($word, $connection)
{
    $lexes = find_lexical_classes($word, $connection);
    $all_lexes = get_lexical_classes();
    usort($all_lexes, function($a, $b){return $a[1] - $b[1]; }); // Custom sorting based on weights
    
    foreach($lexes as $wordlex)
    {
        foreach($all_lexes as $lex)
        {
            if ($lex[0] == $wordlex)
            {
                return $lex[1]; // Gets weight of largest lexical class
            }
        }
    }
    return 1; // Default weight
}

/**
 * Looks up word in appropriate dictionary
 * @param string $word - The word to search for
 * @param boolean $exact - Whether to match words exactly
 * @param object $connection - Database handler
 * @return boolean - TRUE if word is in selected dictionary
 */
function get_dict_match($word, $exact, $connection)
{
    return exists($word, MERRIAM_FIELD, MERRIAM_TABLE, $connection, $exact);
}

/**
 * Checks if word phrase exists in vocabulary
 * @param string $word - Word to look up
 * @param string $field - Name of field
 * @param string $table - Name of table
 * @return boolean
 * @test - testMedExists1...testMedExists4, testStopExists1...testStopExists3, testNameExists1, testNameExists2, testExistsExact1, testExistsExact2
 */
function exists($word, $field, $table, $connection, $exact = FALSE)
{
    return (find($word, $field, $table, $connection, $exact) != NULL);
}

/**
 * Looks up exact and approximate matches for word phrase in vocabulary
 * @param string $word - Word to look for
 * @param string $field - Name of field
 * @param string $table - Name of table
 * @param object $connection - Database handle
 * @param boolean $exact - Find only exact matches if this is turned on
 * @return NULL|string
 * @test - testMedExists1, testMedExists2, testStopExists1, testStopExists2, testNameExists1, testNameExists2
 */
function find($word, $field, $table, $connection, $exact = FALSE)
{
    if (strlen($word) <= 2)
    {
        return NULL;
    }

    $qry = "SELECT $field FROM $table WHERE $field = \"$word\" ";
    if ($exact == FALSE)
    {
        $qry .= "OR $field Like \"$word %\" "; 
    }
    $qry .= "LIMIT 1";

    $result = mysqli_fetch_row(mysqli_query($connection, $qry));
    if (count($result) > 0)
    {
        return $result[0];
    }
    else
    {
        return NULL;
    }
}

/**
 * Outputs word and stem from a vocabulary
 * @param string $field - Field name with word
 * @param string $table - Table name with vocabulary
 * @param object $connection - Database handle
 * @return Prints TSV of words and their stems
 * @test - Visual check of output
 */
function stem_vocabulary($field, $table, $connection)
{
    $qry = "SELECT $field FROM $table";
    $result = mysqli_query($connection, $qry);
    while ($row = mysqli_fetch_array($result))
    {
        $wrd = trim($row[0]);
        $stem = PorterStemmer::Stem($wrd);
        print_r("$wrd\t$stem\n");
    }
}
