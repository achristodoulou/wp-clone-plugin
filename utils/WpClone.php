<?php

namespace Utils;

class WpClone
{
    public $wp_directory;
    public $wp_clone_directory;
    public $messages = array();

    public function __construct($wp_directory, $wp_clone_directory)
    {
        $this->wp_directory = $wp_directory;
        $this->wp_clone_directory = $wp_clone_directory;
    }

    public function run()
    {
        #Load wp-config
        $wp_config = $this->wp_directory . 'wp-config.php';

        #DB Credentials
        $db_host = DB_HOST;
        $db_user = DB_USER;
        $db_name = DB_NAME;
        $dp_pass = DB_PASSWORD;
        $db_clone_name = DB_NAME . '_staging';

        $archive = new Archive;
        $fileSystemOperations = new FileSystemOperations($wp_config);
        $dbOperations = new DatabaseOperations($db_host, $db_user, $dp_pass);
        $timer = new Timer();

        #Temporary archive filename
        $archive_name = 'clone.zip';

        $wp_directory_name = $fileSystemOperations->getLastDirectoryName($this->wp_directory);
        $wp_clone_directory_name = $fileSystemOperations->getLastDirectoryName($this->wp_clone_directory);

        try
        {
            $timer->start();

            #TODO Check if clone folder already exists

            #Archive the current directory
            $archive->zip($archive_name, $this->wp_directory);
            $this->messages[] = "Archive created ($archive_name, $this->wp_directory)";

            #Exctract the archive directory
            $archive->unzip($archive_name, $this->wp_directory);
            $this->messages[] = "Archive extracted ($archive_name, $this->wp_directory)";

            #Rename the clone directory
            $fileSystemOperations->renameFile($this->wp_directory . $wp_directory_name, $this->wp_clone_directory);
            $this->messages[] ="Rename clone directory ($this->wp_directory$wp_directory_name, $this->wp_clone_directory)";

            #Clone wp db
            $dbOperations->cloneDb($db_name, $db_clone_name);
            $this->messages[] = "DB cloning completed ($db_name, $db_clone_name)";

            #Update db urls
            $dbOperations->updateRecords($db_clone_name, $wp_clone_directory_name);
            $this->messages[] = "DB updated ($db_clone_name, $wp_clone_directory_name)";

            #Update wp-config.php to point to new db
            $fileSystemOperations->updateWpConfig($db_name, $db_clone_name, $this->wp_clone_directory);
            $this->messages[] = "WP config updated ($db_name, $db_clone_name, $this->wp_clone_directory)";

            #Set wp directories proper permissions

            $fileOutput = $fileSystemOperations->fixWpFilePermissions($this->wp_clone_directory);
            $this->messages[] = "Fixed Files and Folders permissions ($this->wp_clone_directory) Output: " . json_encode($fileOutput);

            #Disable crawlers to index the clone site

            $timer->stop();

            #Cloning process completed
            $protocol = $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
            $this->messages[] = "Cloning process has been completed!!! <a target='_blank' href='" . $protocol . $_SERVER['HTTP_HOST'] . '/' . $wp_clone_directory_name . "'>Go to clone site and enjoy :)</a>";
            $this->messages[] = "<i>Script execution took ". $timer->getTime() . " seconds</i>";
        }
        catch (\Exception $e)
        {
            $fileSystemOperations->removeDirectories(array(
                #Remove clone directory if failed to be renamed
                $this->wp_directory . $wp_directory_name,
                #Remove clone dir
                $this->wp_clone_directory
            ));

            $dbOperations->dropCloneDatabase($db_clone_name);

            throw new \Exception("An error occurred, rolled back all changes! " . $e->getMessage());
        }

        #Remove archive file
        $fileSystemOperations->removeFiles(array($archive_name));

        #Close db connection
        $dbOperations->closeConnection();
    }
}