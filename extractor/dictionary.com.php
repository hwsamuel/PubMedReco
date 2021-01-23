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

for ($i = 0; $i < 26; $i++)
{
    $alpha_url = 'http://dictionary.reference.com/medical/list/'.chr($i+97);
    $alpha_html = file_get_html($alpha_url);
    foreach($alpha_html->find('a[class="result_link"]') as $listing)
    {
        $words_url = $listing->href;
        $words_html = file_get_html($words_url);
        foreach($words_html->find('a[class="result_link"]') as $word)
        {
            file_put_contents('medical.dictionary.com.txt', "$word->plaintext\n", FILE_APPEND);
        }
        unset($words_html);
    }
    unset($alpha_html);
}
?>
</body>
</html>