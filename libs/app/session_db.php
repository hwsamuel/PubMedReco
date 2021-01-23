<?php
/**
 * Real-time temporary cache database stored in session
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

define('METHOD', 'method');
define('THRESH_VAL', 'threshValue');
define('MEDWORDS', 'med_words');
define('DATASET', 'dataset');
define('SUBSET', 'subset');
define('USER_FILTER', 'userfilter');

define('DEFAULT_METHOD', 1);
define('DEFAULT_THRESH_VAL', 10);
define('DEFAULT_MEDWORDS', 1);
define('DEFAULT_MEDDICT', 1);
define('DEFAULT_DATASET', 1);
define('DEFAULT_SUBSET', 1);
define('DEFAULT_USER_FILTER', NULL);

class SessionDB
{
    private $datasets_dir;

    function __construct($datasets_dir)
    {
        $this->datasets_dir = $datasets_dir;
        if (!isset($_SESSION[METHOD]))
        {
            $this->set_method(DEFAULT_METHOD);
        }
        if (!isset($_SESSION[THRESH_VAL]))
        {
            $this->set_thresh_val(DEFAULT_THRESH_VAL);
        }
        if (!isset($_SESSION[MEDWORDS]))
        {
            $this->set_med_words(DEFAULT_MEDWORDS);
        }
        if (!isset($_SESSION[DATASET]))
        {
            $this->set_dataset(DEFAULT_DATASET);
        }
        if (!isset($_SESSION[SUBSET]))
        {
            $this->get_rand_subset();
        }
        if (!isset($_SESSION[USER_FILTER]))
        {
            $this->set_user_filter(DEFAULT_USER_FILTER);
        }
    }

    // Getters and setters
    function get_method()
    {
        return $_SESSION[METHOD];
    }

    function set_method($method)
    {
        $_SESSION[METHOD] = $method;
    }

    function get_thresh_val()
    {
        return $_SESSION[THRESH_VAL];
    }

    function set_thresh_val($thresh_val)
    {
        if (is_numeric($thresh_val) == TRUE)
        {
            $_SESSION[THRESH_VAL] = $thresh_val;
        }
    }
    
    function get_med_words()
    {
        return $_SESSION[MEDWORDS];
    }

    function set_med_words($med_words)
    {
        $_SESSION[MEDWORDS] = $med_words;
    }

    function get_dataset()
    {
        return $_SESSION[DATASET];
    }

    function set_dataset($dataset)
    {
        $_SESSION[DATASET] = $dataset;
    }

    function get_subset()
    {
        return $_SESSION[SUBSET];
    }

    function set_subset($subset)
    {
        $_SESSION[SUBSET] = $subset;
    }

    function get_user_filter()
    {
        return $_SESSION[USER_FILTER];
    }

    function set_user_filter($user_filter)
    {
        $_SESSION[USER_FILTER] = $user_filter;
    }

    /**
     * Updates session cache values
     * @param string $form - Name of form with submitted values
     * @param string $redirect - Page to redirect to after saving
     */
    function on_save_settings($form, $redirect)
    {
        if (isset($_POST[$form]))
        {
            $method = $_POST[METHOD];
            $thresh_val = $_POST[THRESH_VAL];
            $med_words = $_POST[MEDWORDS];
            $dataset = $_POST[DATASET];
            $subset  = $_POST[SUBSET];
            $user_filter = $_POST[USER_FILTER];

            $this->set_method($method);
            $this->set_thresh_val($thresh_val);
            $this->set_med_words($med_words);
            $this->set_user_filter($user_filter);

            if ($subset != DEFAULT_SUBSET)
            {
                $this->set_dataset($dataset);
                $this->get_rand_subset();
            }
            header("Location: $redirect");
        }
    }

    /**
     * Get currently saved settings in cache
     * @return array - All current settings
     */
    function get_settings()
    {
        $method = $this->get_method();
        $thresh_val = $this->get_thresh_val();
        $med_words = $this->get_med_words();
        $dataset = $this->get_dataset();
        $subset = $this->get_subset();
        $user_filter = $this->get_user_filter();

        return array(METHOD => $method, THRESH_VAL => $thresh_val, MEDWORDS => $med_words, DATASET => $dataset, SUBSET => $subset, USER_FILTER => $user_filter);
    }

    /**
     * Set a random subset from the current dataset
     */
    function get_rand_subset()
    {
        $dataset = $this->get_dataset() == DEFAULT_DATASET ? 'ohn' : 'hp';
        $dataset = $this->datasets_dir.$dataset;

        // Select random file from selected dataset
        $files = scandir($dataset);
        $num_files = count($files) - 1;
        $ran = rand(2, $num_files);

        $_SESSION[SUBSET] = "$dataset/$files[$ran]";
    }
}
