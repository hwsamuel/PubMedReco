<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Extractor for Health Canada Forum Datasets</title>
</head>

<body>
<?php
require_once('libs/simple_html_dom.php');
set_time_limit(120);
ini_set('user_agent', 'Cardea/0.0');

$forum_listings_url = 'http://www.healthpost.ca/forums/'; // Retrieved recent threads as of October 20, 2014
$forum_listings_html = file_get_html($forum_listings_url);
foreach($forum_listings_html->find('td[class="alt2"] div[class="smallfont"] div span a') as $thread)
{
    $thread_name = $thread->plaintext;
    $thread_url = $thread->href;
    $thread_html = file_get_html($thread_url);
    $sub_forum_name = $thread->parent()->parent()->parent()->parent()->parent()->find('td[class="alt1Active"] a', 0)->plaintext;
    
    $data = '';
    foreach($thread_html->find('td[class="alt1"] div[id]') as $content)
    {
        $time_stamp = $content->parent()->parent()->parent()->find('div[class="normal"]', 1)->plaintext;
        $user = $content->parent()->parent()->parent()->find('td[class="alt2"] div[id] a', 0)->plaintext;
        $post_text = $content->plaintext;
    
        $time_stamp = trim($time_stamp);
        $user = trim($user);
        $post_text = preg_replace('/\s+/', ' ', $post_text);
        $post_text = substr($post_text, 1);
    
        $data .= "$time_stamp\n$user\n$post_text\n\n";
    }
    
    $datasets_dir = "/var/www/html/hermes/extractor/hp-datasets/";
    $post_name = $sub_forum_name;
    if (file_exists($datasets_dir) == FALSE)
    {
        mkdir($datasets_dir);
    }
    file_put_contents($datasets_dir.trim($post_name).'.txt', $data);
}
$forum_listings_html->clear();
unset($forum_listings_html);
?>
</body>
</html>