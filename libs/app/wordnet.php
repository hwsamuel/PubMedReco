<?php
/**
 * Wordnet MySQL database query library
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

/**
 * Gets medical lexicons for given string
 * @param array $med_words - Medical words to search
 * @return array - Unique lexical classes for all extracted keywords
 */
function get_med_lexicons($med_words, $exact, $connection)
{
    $hns = array();
    foreach($med_words as $med_word)
    {
        $hns = array_merge((array) $hns, (array) find_med_lexicon($med_word, $connection));
    }
    return array_unique($hns);
}

/**
 * Gets hypernyms for given string
 * @param array $string - Title to search
 * @return array - Unique hypernyms for all extracted keywords
 */
function get_med_hypernyms($string, $exact, $connection)
{
    $hns = array();
    $med_words = extract_med_words($string, $exact, $connection);
    foreach($med_words as $med_word)
    {
        $hns = array_merge((array) $hns, (array) find_hypernyms($med_word, $connection, 1));
    }
    return array_unique($hns);
}

/**
 * Uses WordNet to find hypernyms for medical words
 * @param string $word - Word to search
 * @param object $connection - Database connection
 * @param int $depth - Depth to look up
 * @return array - Single-word hypernyms that match the curated medical lexes
 */
function find_hypernyms($word, $connection, $depth = 3)
{
    $qry = "SELECT linkedlemma FROM word_links WHERE lemma = '$word' AND link = 'hypernym'";
    $ret = mysqli_fetch_multi_row($qry, $connection);
    $result = array();
    $recurse = array();
    foreach($ret as $row)
    {
        $hypernym = $row['linkedlemma'];
        $result[] = $hypernym;
    }
    return $result;
    if ($depth <= 1)
    {
        return $result;
    }
    else
    {
        foreach($result as $word)
        {
            return array_merge((array) $result, (array) find_hypernyms($word, $connection, $depth - 1));
        }
    }
}

/**
 * Checks the lexicon for the given word and determines if it is an agreed medical lexicon
 * Full listing and description of lexical files: http://wordnet.princeton.edu/man/lexnames.5WN.html
 * @param string $word - Word to search for
 * @param object $connection - Database connection
 * @return boolean
 */
function is_med_lexicon($word, $connection)
{
    $qry = "SELECT lex FROM word_lex WHERE lemma ='$word'";

    $med_lex = array(
        'noun.animal',
        'noun.body',
        'noun.food',
        'noun.feeling',
        'noun.plant',
        'noun.substance');

    $ret = mysqli_fetch_multi_row($qry, $connection);
    foreach($ret as $row)
    {
        $lexicon = $row['lex'];
        if (in_array($lexicon, $med_lex) == TRUE)
        {
            return TRUE;
        }
    }
    return FALSE;
}

/**
 * Finds the lexicons for the given word in an agreed medical lexicon
 * Full listing and description of lexical files: http://wordnet.princeton.edu/man/lexnames.5WN.html
 * @param string $word - Word to search for
 * @param object $connection - Database connection
 * @return array
 */
function find_lexical_classes($word, $connection)
{
    $qry = "SELECT lex FROM word_lex WHERE lemma ='$word'";

    $ret = mysqli_fetch_multi_row($qry, $connection);
    $lexicons = array();
    foreach($ret as $row)
    {
        $lexword = $row['lex'];
        if (!in_array($lexword, $lexicons))
        {
            $lexicons[] = $lexword;
        }
    }
    array_unique($lexicons);
    return count($lexicons) == 0 ? array('none') : $lexicons;
}

/**
 * Returns pre-configured listing of lexical classes
 * @return array
 */
function get_lexical_classes()
{
    $lex = array
    (
        array('none', 1),
        array('adj.all', 1),
        array('adj.pert', 1.5),
        array('adv.all', 1),
        array('noun.Tops', 1),
        array('noun.act', 1),
        array('noun.animal', 1.5),
        array('noun.artifact', 1),
        array('noun.attribute', 1),
        array('noun.body', 1.9),
        array('noun.cognition', 1),
        array('noun.communication', 1),
        array('noun.event', 1),
        array('noun.feeling', 1.5),
        array('noun.food', 1.2),
        array('noun.group', 1),
        array('noun.location', 1),
        array('noun.motive', 1),
        array('noun.object', 1),
        array('noun.person', 1),
        array('noun.phenomenon', 1),
        array('noun.plant ', 1.5),
        array('noun.possession', 1),
        array('noun.process', 1.5),
        array('noun.quantity', 1),
        array('noun.relation', 1),
        array('noun.shape', 1),
        array('noun.state', 1.9), // Diseases
        array('noun.substance', 1.5),
        array('noun.time', 1),
        array('verb.body', 1.9),
        array('verb.change', 1.5),
        array('verb.cognition', 1),
        array('verb.communication', 1),
        array('verb.competition', 1),
        array('verb.consumption', 1),
        array('verb.contact', 1),
        array('verb.creation', 1),
        array('verb.emotion', 1.5),
        array('verb.motion', 1),
        array('verb.perception', 1),
        array('verb.possession', 1),
        array('verb.social', 1),
        array('verb.stative', 1),
        array('verb.weather', 1),
        array('adj.ppl', 1)
    );
    return $lex;
}

/**
 * Helper function to fetch multiple rows
 * @param string $qry - Query to execute
 * @param object $connection - Database connection
 * @return array - Associative array
 */
function mysqli_fetch_multi_row($qry, $connection)
{
    $result = array();
    $exec = mysqli_query($connection, $qry);
    if ($exec == FALSE)
    {
        return $result;
    }
    while($row = $exec->fetch_assoc())
    {
        $result[] = $row;
    }
    return $result;
}