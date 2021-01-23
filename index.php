<?php
/**
 * Main file
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__.'/libs/app/session_db.php';
require_once __DIR__.'/libs/smarty/template.php';
require_once __DIR__.'/libs/app/vocabulary.php';
require_once __DIR__.'/connect.php';

session_start();
main();

/**
 * Main entry point for app
 * Smarty variables:
 *     _smartySettings - JS variable value for loading
 *     _smartyThreads - JS variable value for loading chat threads
 * Form variables:
 *     save_settings - Settings form name
 */
function main()
{
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_NAME);
    $config = new SessionDB(__DIR__.'\\assets\\datasets\\');
    $config->on_save_settings('save_settings', 'index.php'); // Handler if form submitted
    //$config->set_subset('/var/www/html/pubmedreco/assets/datasets/ohn/Live Chat Transcript 2009-3-25.txt'); // For testing
    //$config->set_subset('/var/www/html/pubmedreco/assets/datasets/ohn/Live Chat Transcript 2009-4-08.txt');
    
    $chat = file($config->get_subset(), FILE_IGNORE_NEW_LINES);
    $exact = $config->get_med_words() == 1 ? TRUE : FALSE;
    $chat = process_chat($chat, $exact, $connection);
    $profile = profile_chat($chat);
    
    $data['_smartyUsers'] = get_users($chat);
    $data['_smartySettings'] = $config->get_settings();
    $data['_smartyThreads'] = json_encode($chat);
    $data['_smartyProfile'] = $profile;
    $data['_smartyDataFile'] = get_data_file_log($config);
    
    $template = new Template();
    $template->display('index.htm', $data);
}

/**
 * Create a profile of the whole chat conversation
 * @param array $chat - All threads
 */
function profile_chat($chat)
{
    $i = 0;
    $freq = 0;
    $weight = 0;
    $num_threads = 0;
    $repeats = 0;
    $unique = 0;
    $indexer = array();
    foreach($chat as $thread)
    {
        $keywords = $thread[3];
        $kwcount = count($keywords);
        if ($kwcount == 0)
        {
            continue;
        }
        $num_threads++;
        $freq += $kwcount;
        $all_weights = array();
        foreach($keywords as $keyword)
        {
            $all_weights[] = get_weight_part($keyword);
            $exists = in_array($keyword, $indexer);
            if ($exists === FALSE)
            {
                $indexer[] = $keyword;
                $unique++;
            }
            else
            {
                $repeats++;
            }
        }
        $weight += array_sum($all_weights);
    }
    $avg_weight = $freq == 0 ? 0 : $weight/$freq;
    return array(count($chat), $num_threads, $unique, $repeats, $avg_weight);
}

/**
 * Finds stdev of values in list
 * @param array $arr
 * @return float
 */
function sample_stdev($arr)
{
    $num = count($arr);
    if ($num == 1)
    {
        return 0;
    }
    $sum = array_sum($arr);
    $average = $sum/$num;
    
    $sum2 = 0;
    foreach($arr as $val) 
    {
        $sum2 += pow(($val - $average), 2);
    }
    $stdev = sqrt($sum2 / ($num - 1));
    return $stdev;
}

/**
 * Gets the weight part of the keyword-weight pair, e.g. clinic (1) = 1
 * @param string $keyword_weight_pair
 */
function get_weight_part($keyword_weight_pair)
{
    $start = stripos($keyword_weight_pair, '(');
    $weight = substr($keyword_weight_pair, $start + 1, - 1);
    return $weight;
}

/**
 * Get unique users in conversation
 * @param object $chat
 * @return array;
 */
function get_users($chat)
{
    $usrs = array();
    foreach($chat as $thread)
    {
        $usr = ucfirst($thread[1]);
        if (in_array($usr, $usrs) == TRUE)
        {
            continue;
        }
        else
        {
            $usrs[] = $usr;
        }
    }
    sort($usrs);
    return $usrs;
}

/**
 * Returns a string containing information about which chat data file is loaded
 * @param array $config - The currently loaded configuration
 * @return string - Friendly string with data file info
 */
function get_data_file_log($config)
{
    $data_file = $config->get_subset();
    $data_file = substr($data_file, strripos($data_file, '/') + 1);
    $data_file = $config->get_dataset() == 1 ? "Optimal Health Network: $data_file" : "Health Post: $data_file";
    $data_file = "Loaded dataset from $data_file";
     
    return $data_file;
}

/**
 * Load chat threads and apriori extract med words from each thread
 * @param array $chat - List of all threads in current chat, e.g. {timestamp, user, thread}
 * @param boolean $exact - Whether to match words exactly
 * @param object $connection - Database handle
 */
function process_chat($chat, $exact, $connection)
{
    $new_chat = array();
    $i = 0;
    for($thread_count = 0; $thread_count < count($chat); $thread_count += 4)
    {
        $timestamp = str_replace('&nbsp;', '', $chat[0 + $thread_count]);
        $user = strtoupper($chat[1 + $thread_count]);
        $thread = str_replace('&quot;', "'", $chat[2 + $thread_count]);
        
        $new_chat[$i][0] = $timestamp;
        $new_chat[$i][1] = $user;
        $new_chat[$i][2] = $thread;
        $new_chat[$i][3] = extract_med_words($thread, $exact, $connection);
        $i++;
    }
    return $new_chat;
}