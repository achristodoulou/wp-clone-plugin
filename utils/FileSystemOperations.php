<?php

namespace Utils;

class FileSystemOperations {

    public $wp_config_file;

    public function __construct($wp_config)
    {
        $this->wp_config_file = $wp_config;
    }

    /**
     * Configures wp file permissions based on recommendations
     * @see http://codex.wordpress.org/Hardening_WordPress#File_permissions
     *
     * @param $wp_clone_directory_path
     * @return array
     */
    public function fixWpFilePermissions($wp_clone_directory_path)
    {
        #File Permissions
        $wp_owner = posix_getpwuid(fileowner($this->wp_config_file));
        $wp_group = posix_getgrgid(filegroup($this->wp_config_file));

        $wp_owner = $wp_owner['name'];
        $wp_group = $wp_group['name'];

        # reset to safe defaults
        $commands = "find ${wp_clone_directory_path} -exec chown ${wp_owner}:${wp_group} {} \\;
                 find ${wp_clone_directory_path} -type d -exec chmod 755  {} \\;
                 find ${wp_clone_directory_path} -type f -exec chmod 644  {} \\;
        ";

        # allow WP to manage wp-config.php (but prevent world access)
        $commands .= "chgrp ${wp_group} ${wp_clone_directory_path}wp-config.php \\;
                  chmod 660 ${wp_clone_directory_path}wp-config.php \\;
        ";

        # allow WP to manage wp-content
        $commands .= "find ${wp_clone_directory_path}wp-content -exec chgrp ${wp_group}  {} \\;
                  find ${wp_clone_directory_path}wp-content -type d -exec chmod 775 {} \\;
                  find ${wp_clone_directory_path}wp-content -type f -exec chmod 664  {} \\;
        ";

        exec($commands, $output);

        return $output;
    }

    public function removeDirectories(array $directories)
    {
        foreach ($directories as $directory) {
            if(file_exists($directory)) {
                rmdir($directory);
            }
        }
    }

    public function removeFiles(array $files)
    {
        foreach ($files as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function renameFile($source, $destination)
    {
        $result = rename($source, $destination);
        if($result === false)
            throw new \Exception("Failed to rename $source to $destination!");
    }

    /**
     * Update wp clone config
     *
     * @param $db_name
     * @param $db_clone_name
     * @param $wp_clone_directory_path
     * @throws \Exception
     */
    public function updateWpConfig($db_name, $db_clone_name, $wp_clone_directory_path)
    {
        $wp_config = $wp_clone_directory_path . 'wp-config.php';
        $file_contents = file_get_contents($wp_config);
        $file_contents = str_replace("define('DB_NAME', '$db_name');", "define('DB_NAME', '$db_clone_name');",$file_contents);
        $result = file_put_contents($wp_config, $file_contents);
        if($result === false)
            throw new \Exception("Failed to update wp-config.php!");
    }

    public function getLastDirectoryName($path)
    {
        if(substr($path, -1) == '/')
        {
            $pos = strrpos(substr($path, 0, strlen($path) - 1), '/');
        }
        else{
            $pos = strrpos($path, '/');
        }

        return substr($path, $pos + 1);
    }

}