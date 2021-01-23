<?php 
/**
 * Uses PubMed API to query database and display relevant results
 * PHP PubMed API Wrapper (modified): https://github.com/asifr/PHP-PubMed-API-Wrapper
 * Entrez search fields: http://www.ncbi.nlm.nih.gov/books/NBK3827/#pubmedhelp.Search_Field_Descrip
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

define('TITLE_SCORE', 2);
define('ABSTRACT_SCORE', 1);
define('FRESHNESS_SCORE', 2);
define('SAMPLE_THRESHOLD', 3);

require_once __DIR__.'/libs/pubmed/PubMedAPI.php';
require_once __DIR__.'/libs/app/vocabulary.php';
require_once __DIR__.'/libs/app/WordNGram.php';
require_once __DIR__.'/connect.php';

main();

/**
 * Main method
 */
function main()
{
    $entrez = new PubMedAPI();
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_NAME);
    
    $top_words = $_REQUEST['topWords'];
    $all_words = $_REQUEST['allWords'];
    $articles = $entrez->query(create_query($top_words));
    
    if (count($articles) == 0)
    {
        $articles = $entrez->query(create_base_query($top_words));
    }
    $articles = score_articles($articles, $all_words, $top_words);
    print_articles($articles);
}

/**
 * Uses ngrams to create a formatted query
 * @param array $ngrams
 * @return string
 */
function format_query($ngrams)
{
    $qry = '(';
    foreach ($ngrams as $ngram)
    {
        foreach($ngram as $word)
        {
            $qry .= $word. ' AND ';
        }
        $qry = substr($qry, 0, -5);
        $qry .= ') OR (';
    }
    $qry = substr($qry, 0, -6);
    $qry .= strlen($qry) > 0 ? ')' : NULL;
    return $qry;
}

/**
 * Create the query to send to PubMed
 * @param string $term
 * @return string
 */
function create_query($term)
{
    $unigrams = WordNGram::get_unigrams($term);
    $digrams = WordNGram::get_ngrams($unigrams, $unigrams, 2);
    $qry = format_query($digrams);
    return $qry;
}

/**
 * Create the base query to send to PubMed, using terms and OR
 * @param string $term
 * @return string
 */
function create_base_query($term)
{
    $unigrams = WordNGram::get_unigrams($term);
    $qry = null;
    foreach($unigrams as $gram)
    {
        $qry .= "$gram OR "; 
    }
    $qry = substr($qry, 0, -4);
    return $qry;
}

/**
 * Score title
 * @param string $title
 * @param string $abstract
 * @param int $year
 * @param string $index_words
 * @param string $query_words
 */
function score_article($title, $abstract, $year, $index_words, $query_words)
{
    $score = 0;
    $scored_title = array();
    $scored_abstract = array();
    $scored_aux = array();
    $this_year = date('Y');
    $freshness = $year/(FRESHNESS_SCORE * $this_year); // Consider the year of publication against current year
    $num_words = str_word_count($title, 0); // Count number of words in title
    
    foreach ($index_words as $key => $item)
    {
        $rank = count($index_words) - $key; // Take word ranks into consideration
        
        $found_title = stripos($title, $item);
        if ($found_title !== FALSE)
        {
            $score += (TITLE_SCORE * $rank * $freshness)/$num_words;
            if (in_array($item, $query_words) == TRUE)
            {
                $scored_title[] = $item;
            }
            else
            {
                $scored_aux[] = $item;
            }
            continue;
        }

        $found_abstract = stripos($abstract, $item);
        if ($found_abstract !== FALSE)
        {
            $score += (ABSTRACT_SCORE * $rank * $freshness)/$num_words;
            if (in_array($item, $query_words) == TRUE)
            {
                $scored_abstract[] = $item;
            }
            else
            {
                $scored_aux[] = $item;
            }
            continue;
        }
    }
    
    return array('score' => $score, 'scored_title' => $scored_title, 'scored_abstract' => $scored_abstract, 'scored_aux' => $scored_aux);
}

/**
 * Compute score of each article found
 * @param array $articles - Articles found
 * @param string $index_words - Words to match and score against
 * @param string $query_words - Words used in PubMed query
 */
function score_articles($articles, $index_words, $query_words)
{
    $articles_sort = array();
    $index_words = explode(PubMedAPI::$term_separator, $index_words);
    $query_words = explode(PubMedAPI::$term_separator, $query_words);
    
    $min_title_len = 99999;
    foreach($articles as $article)
    {
        $title_len = str_word_count($article['title']);
        if ($title_len < $min_title_len)
        {
            $min_title_len = $title_len;
        }
    }
    
    $sum = 0;
    foreach($articles as $article)
    {
        $ret = score_article($article['title'], $article['abstract'], $article['year'], $index_words, $query_words);
        $normalize = (TITLE_SCORE * seq_sum(count($index_words))) / (FRESHNESS_SCORE * $min_title_len); // Max possible value of score
        $score = $ret['score']/$normalize;
        $sum += $score;

        $articles_sort[] = array
        (
            'id' => $article['pmid'], 
            'title' => $article['title'], 
            'year' => $article['year'], 
            'score' => $score, 
            'scored_title' => $ret['scored_title'], 
            'scored_abstract' => $ret['scored_abstract'],
            'scored_aux' => $ret['scored_aux']
        );
    }
    
    uasort($articles_sort, 'sort_score');
    return $articles_sort;
}

/**
 * Sum of sequence
 * @param int $n - Number to find sequence for
 * @return int - Sequence sum of $n
 */
function seq_sum($n)
{
    if ($n <= 1)
    {
        return 1;
    }
    else
    {
        return $n + seq_sum($n - 1);
    }
}

/**
 * Format output to show results
 * @param array $articles - Articles found
 */
function print_articles($articles)
{
    echo "<div>";
    $i = 0;
    foreach($articles as $key => $article)
    {
        $BASE_URL = "https://www.ncbi.nlm.nih.gov/pubmed";
        $year = $article['year'] == '' ? '' : "(".$article['year'].")";
        $title = ucwords(strtolower($article['title']));
        echo "<p class='articles'>";
        echo "<span class='article-id hidden'>".$article['id']."</span>";
        $titleWords = json_encode($article['scored_title']);
        $abstractWords = json_encode($article['scored_abstract']);
        
        $cls = ($i++ >= SAMPLE_THRESHOLD) ? 'text-muted' : 'text-primary';
        echo "<a href='#' onclick='upVote($titleWords, $abstractWords);'><span class='glyphicon glyphicon-thumbs-up'></span></a> ";
        echo "<a href='#' onclick='downVote($titleWords, $abstractWords);'><span class='glyphicon glyphicon-thumbs-down'></span></a> ";
        echo "<a class='$cls' target='_blank' href='$BASE_URL/".$article['id']."'>".$title."</a> $year";
        foreach($article['scored_title'] as $word)
        {
            echo " <span class='label label-info'>$word</span>";
        }
        foreach($article['scored_abstract'] as $word)
        {
            echo " <span class='label label-primary'>$word</span>";
        }
        foreach($article['scored_aux'] as $word)
        {
            echo " <span class='label label-default label-light-gray'>$word</span>";
        }
        $art_score = number_format($article['score'], 2);
        echo " <span class='badge'>$art_score</span>";
        echo "</p>";
    }
    
    $stats = get_summary_stats($articles);
    $avg = $stats['avg_score'];
    $max = $stats['max_score'];
    $min = $stats['min_score'];
    
    echo "<span id='avg_score'>$avg</span>";
    echo "<span id='max_score'>$min</span>";
    echo "<span id='min_score'>$max</span>";
    
    echo "</div>";
}

/**
 * Gets summary stats for current set of recommendations
 * @param array $articles - List of articles
 */
function get_summary_stats($articles)
{
    $stats = array();
    if (count($articles) == 0)
    {
        $stats['avg_score'] = 0;
        $stats['max_score'] = 0;
        $stats['min_score'] = 0;
        return $stats;
    }
    
    $score = 0;
    $max_score = -1;
    $min_score = 99999;
    $count = 0;
    foreach($articles as $article)
    {
        if ($count >= SAMPLE_THRESHOLD)
        {
            break;
        }
        $art_score = $article['score'];
        $score += $art_score;
        if ($art_score > $max_score)
        {
            $max_score = $art_score;
        }
        if ($art_score < $min_score)
        {
            $min_score = $art_score;
        }
        $count++;
    }
    
    $num = count($articles);
    if (count($articles) > SAMPLE_THRESHOLD)
    {
        $num = SAMPLE_THRESHOLD;
    }
    $stats['avg_score'] = $score / $num;
    $stats['max_score'] = $max_score;
    $stats['min_score'] = $min_score;
    return $stats;
}

/**
 * Function to sort array
 * @param array $a - First array
 * @param array $b - Second array
 */
function sort_score($a, $b)
{
    return sort_assoc_array($a, $b, 'score');
}

/**
 * 
 * @param array $a - First array 
 * @param array $b - Second array
 * @param string $field - Array field to compare
 * @return int - If less than, then return 1 else return -1
 */
function sort_assoc_array($a, $b, $field)
{
    if ($a[$field] == $b[$field])
    {
        return 0;
    }
    return ($a[$field] < $b[$field]) ? 1 : -1;
}
