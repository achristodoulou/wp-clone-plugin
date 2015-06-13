<?php

#error_reporting(E_ALL);
#ini_set('display_errors', '1');

require_once __DIR__ . '/../utils/Archive.php';
require_once __DIR__ . '/../utils/DatabaseOperations.php';
require_once __DIR__ . '/../utils/FileSystemOperations.php';
require_once __DIR__ . '/../utils/Timer.php';
require_once __DIR__ . '/../utils/WpClone.php';
?>

<div class="wrap">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"><br></div>
    <h2>WP Clone</h2>
</div>
<form method="post" id="myForm">
    <input type="hidden" name="isSubmitted" value="1" />
    <div  style="width:  302px; padding-top: 10px; ">
        <div style="clear: left">
            <div style="float:left; width: 100px;"><label>Clone folder:</label></div>
            <div style="float:left"><input type="text" name="CloneFolder" value="" style="width: 200px" /></div>
        </div>
        <div style="clear: left; text-align: right; padding-top: 10px">
            <input type="submit" name="Submit" value="Save" class="button button-primary button-large" />
        </div>
    </div>
</form>

<?php
if(isset($_POST['isSubmitted']) && !empty($_POST['CloneFolder']))
{
    $tmp_wp_clone_directory = substr($_POST['CloneFolder'], -1) == '/' ? $_POST['CloneFolder'] : $_POST['CloneFolder'] . '/';
    $wp_directory = get_home_path();
    $wp_clone_directory = get_home_path() . $tmp_wp_clone_directory;

    try {
        $app = new Utils\WpClone($wp_directory, $wp_clone_directory);
        $app->run();

        echo "<h2>Log</h2>";
        echo "<ul>";
            foreach($app->messages as $message){
                echo "<li>$message</li>";
            }
        echo "</ul>";
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
}



