<?php 
/**
 * PHP word n-grams generator (order of n-grams doesn't matter)
 * 
 * @author Hamman Samuel <hwsamuel@ualberta.ca>
 */

class WordNGram
{
    /**
     * Get unigrams
     * @param string $sentence - Sentence to use
     * @return array
     */
    static function get_unigrams($sentence)
    {
        $unigrams = self::_tokenize($sentence);
        sort($unigrams);
        return $unigrams;
    }

    /**
     * Get n-grams
     * @param array $unigrams - Unigrams
     * @param array $n_1grams - Previous n-grams, i.e. to get digrams, send unigrams in this parameter
     * @param int $grams - Value of n in n-grams, e.g. 2 means digram, etc.
     * @return array 
     */
    static function get_ngrams($unigrams, $n_1grams, $grams)
    {
        $ngrams = array();
        for($i = 0; $i < count($n_1grams); $i++)
        {
            $base = $n_1grams[$i];
            $base_last = is_array($base) ? $base[count($base) - 1] : $base;
            $last_pos = array_search($base_last, $unigrams); // Find index of last item of base in unigrams
            for($j = $last_pos + 1; $j < count($unigrams); $j++)
            {
                $ngram = self::_array_flatten(array($base, $unigrams[$j]));
                $ngrams[] = $ngram;
            }
        }
        sort($ngrams);
        return $ngrams;
    }
    
    /**
     * Split string using spaces as delimiter
     * @param string $sentence - Sentence to split
     * @return array
     */
    static function _tokenize($sentence)
    {
        return preg_split("/[\W]+/", $sentence);
    }

    /**
     * Flatten array
     * Source: brownelearning.org/blog/2012/04/quick-way-to-flatten-multidimensional-arrays-in-php/
     * @param array $input - Array to flatten
     * @return array
     */
    static function _array_flatten($input)
    {
        $output = array();
        if (is_array($input)) 
        {
            foreach ($input as $element)
            {
                $output = array_merge($output, self::_array_flatten($element));
            }
        }
        else
        {
            $output[] = $input;
        }
        return $output;
    }
}

class WordNGramTests
{   
    static function assert($result, $expected, $tag)
    {
        if ($result == $expected)
        {
            echo "<p>Passed $tag</p>";
        }
        else
        {
            echo "<p><b>Failed $tag</b></p>";
        }
    }
    
    static function run()
    {
        $unigrams = WordNGram::get_unigrams('a b c d');
        $digrams = WordNGram::get_ngrams($unigrams, $unigrams, 2);
        self::assert($digrams[0], array('a', 'b'), 'digrams1');
        self::assert($digrams[1], array('a', 'c'), 'digrams2');
        self::assert($digrams[2], array('a', 'd'), 'digrams3');
        self::assert($digrams[3], array('b', 'c'), 'digrams4');
        self::assert($digrams[4], array('b', 'd'), 'digrams5');
        self::assert($digrams[5], array('c', 'd'), 'digrams6');
        
        $trigrams = WordNGram::get_ngrams($unigrams, $digrams, 3);
        self::assert($trigrams[0], array('a', 'b', 'c'), 'trigrams1');
        self::assert($trigrams[1], array('a', 'b', 'd'), 'trigrams2');
        self::assert($trigrams[2], array('a', 'c', 'd'), 'trigrams3');
        self::assert($trigrams[3], array('b', 'c', 'd'), 'trigrams4');
        
        $tetragrams = WordNGram::get_ngrams($unigrams, $trigrams, 4);
        self::assert($tetragrams[0], array('a', 'b', 'c', 'd'), 'tetragrams1');
        
        $unigrams = WordNGram::get_unigrams('hello, slim shady');
        self::assert($unigrams[0], 'hello', 'unigramsSentences1');
        self::assert($unigrams[1], 'slim', 'unigramsSentences2');
        self::assert($unigrams[2], 'shady', 'unigramsSentences3');
        
        $digrams = WordNGram::get_ngrams($unigrams, $unigrams, 2);
        self::assert($digrams[0], array('hello', 'slim'), 'digramsSentences1');
        self::assert($digrams[1], array('hello', 'shady'), 'digramsSentences1');
        self::assert($digrams[2], array('slim', 'shady'), 'digramsSentences1');

        $unigrams = WordNGram::get_unigrams('my name is, slim shady');
        self::assert($unigrams[0], 'my', 'unigramsSentences21');
        self::assert($unigrams[1], 'name', 'unigramsSentences22');
        self::assert($unigrams[2], 'is', 'unigramsSentences23');
        self::assert($unigrams[3], 'slim', 'unigramsSentences24');
        self::assert($unigrams[4], 'shady', 'unigramsSentences25');

        $digrams = WordNGram::get_ngrams($unigrams, $unigrams, 2);
        self::assert($digrams[0], array('my', 'name'), 'digramsSentences21');
        self::assert($digrams[1], array('my', 'is'), 'digramsSentences22');
        self::assert($digrams[2], array('my', 'slim'), 'digramsSentences23');
        self::assert($digrams[3], array('my', 'shady'), 'digramsSentences24');
        self::assert($digrams[4], array('name', 'is'), 'digramsSentences25');
        self::assert($digrams[5], array('name', 'slim'), 'digramsSentences26');
        self::assert($digrams[6], array('name', 'shady'), 'digramsSentences27');
        self::assert($digrams[7], array('is', 'slim'), 'digramsSentences28');
        self::assert($digrams[8], array('is', 'shady'), 'digramsSentences29');
        self::assert($digrams[9], array('slim', 'shady'), 'digramsSentences30');

        $trigrams = WordNGram::get_ngrams($unigrams, $digrams, 3);
        self::assert($trigrams[0], array('my', 'name', 'is'), 'trigramsSentences1');
        self::assert($trigrams[1], array('my', 'name', 'slim'), 'trigramsSentences2');
        self::assert($trigrams[2], array('my', 'name', 'shady'), 'trigramsSentences3');
        self::assert($trigrams[3], array('my', 'is', 'slim'), 'trigramsSentences4');
        self::assert($trigrams[4], array('my', 'is', 'shady'), 'trigramsSentences5');
        self::assert($trigrams[5], array('my', 'slim', 'shady'), 'trigramsSentences6');

        $tetragrams = WordNGram::get_ngrams($unigrams, $trigrams, 4);
        self::assert($tetragrams[0], array('my', 'name', 'is', 'slim'), 'tetragramsSentences1');
        self::assert($tetragrams[1], array('my', 'name', 'is', 'shady'), 'tetragramsSentences2');
        self::assert($tetragrams[2], array('my', 'name', 'slim', 'shady'), 'tetragramsSentences3');

        $pentagrams = WordNGram::get_ngrams($unigrams, $tetragrams, 4);
        self::assert($pentagrams[0], array('my', 'name', 'is', 'slim', 'shady'), 'pentagramsSentences1');
    }
}
?>