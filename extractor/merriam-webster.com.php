<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Extractor for Medical Words from Dictionary.com</title>
</head>

<body>
<?php
require_once(__DIR__.'/../libs/simple_html_dom/simple_html_dom.php');
set_time_limit(120);
ini_set('user_agent', 'MedWordTagger/0.1');

$base_url = 'http://www.merriam-webster.com/browse/medical/';
for ($i = 0; $i < 26; $i++)
{
    $alpha_url = $base_url.chr($i+97).'.htm';
    $alpha_html = file_get_html($alpha_url);
    $ad = $alpha_html->find('div[class="proceed_button"] a');
    if (count($ad) > 0)
    {
        $alpha_html = file_get_html($ad[0]->href);
    }
    $listings = $alpha_html->find('ol[class="toc"] li a');
    foreach($listings as $listing)
    {
        $words_url = $base_url.str_replace(" ", "+", $listing->href);
        $words_html = file_get_html($words_url);
        $ad = $words_html->find('div[class="proceed_button"] a');
        if (count($ad) > 0)
        {
            $words_html = file_get_html($ad[0]->href);
        }
        $words = $words_html->find('div[class="entries"] ol li a[id]');
        foreach($words as $word)
        {
            file_put_contents('merriam-webster.com.txt', "$word->plaintext\n", FILE_APPEND);
        }
        unset($words_html);
    }
    unset($alpha_html);
}
?>
</body>
</html>