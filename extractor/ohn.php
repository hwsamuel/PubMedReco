<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Extractor for OHN Chat Datasets</title>
</head>

<body>
<?php
require_once('libs/simple_html_dom.php');
set_time_limit(120);
ini_set('user_agent', 'Cardea/0.0');

$yearly_listings_url = 'http://www.optimalhealthnetwork.com/Alternative-Health-Live-Chat-Log-Archive-s/196.htm';
$yearly_listings_html = file_get_html($yearly_listings_url);

// Retrieved archives from 2012-2007 as of October 20, 2014
foreach($yearly_listings_html->find('table table table table table tr td a') as $yearly_listing)
{
	$chat_listings_url = $yearly_listing->href;
    $chat_listings_html = file_get_html($chat_listings_url);
	foreach($chat_listings_html->find('table table table table table tr td a') as $chat_listing)
	{
		$chat_url = $chat_listing->href;
	    $chat_html  = file_get_html($chat_url);
	    $chat_content = "";
		foreach($chat_html->find('div[class="artund"] table tr td[class="block3mid"] table tr') as $chat)
		{
			$yearly_listing_name = $yearly_listing->plaintext;
			$chat_listing_name = $chat_listing->plaintext;

			$timestamp = $chat->find('td', 0)->plaintext;
			$user = $chat->find('td', 1)->plaintext;
			$user = str_replace("&nbsp;", "", $user);
			$data = $chat->find('td', 2)->plaintext;
            
			$chat_content .= "$timestamp\n$user\n$data\n\n";
		}
		$datasets_dir = "/var/www/html/hermes/extractor/ohn-datasets/$yearly_listing_name/";
		if (file_exists($datasets_dir) == FALSE)
		{
		    mkdir($datasets_dir);
		}
		file_put_contents($datasets_dir.trim($chat_listing_name).'.txt', $chat_content);
		
		$chat_html->clear();
		unset($chat_html);
	}
	$chat_listings_html->clear();
	unset($chat_listings_html);
}
$yearly_listings_html->clear();
unset($yearly_listings_html);
?>
</body>
</html>