<?php

namespace Utils;

class DatabaseOperations {

    /**
     * @var \Mysqli
     */
    protected $connection;

    public function __construct($host, $user, $password)
    {
        $this->connection = $this->open_db_connection($host, $user, $password);
    }

    /**
     * Open db connection
     *
     * @param $host
     * @param $user
     * @param $password
     * @return \Mysqli
     */
    private function open_db_connection($host, $user, $password)
    {
        // Create connection
        $conn = new \Mysqli($host, $user, $password);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    /**
     * Drop clone database
     *
     * @param $db_clone_name
     */
    public function drop_clone_database($db_clone_name)
    {
        $resource = $this->getConnection();

        @mysqli_select_db( $resource, $db_clone_name );
        $resource->query("DROP DATABASE IF EXISTS $db_clone_name");
    }

    public function getConnection()
    {
        $resource = $this->connection;

        if($resource == null)
            throw new \Exception('There is no database connection!');

        return $resource;
    }

    /**
     * Clone wp db
     *
     * @param $db_name
     * @param $db_clone_name
     * @throws \Exception
     */
    public function clone_db($db_name, $db_clone_name)
    {
        $error = true;

        $resource = $this->getConnection();

        @mysqli_select_db ( $resource, $db_name );
        $getTables = $resource->query("SHOW TABLES");
        $tables = array();
        while($row = mysqli_fetch_row($getTables)){
            $tables[] = $row[0];
        }

        #Create clone db
        mysqli_query($resource, "CREATE DATABASE `$db_clone_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;")
        or function(){ throw new \Exception(mysql_error()); };

        foreach($tables as $cTable){
            @mysqli_select_db ( $resource, $db_clone_name );
            $create     =   $resource->query("CREATE TABLE $cTable LIKE ".$db_name.".".$cTable);
            if(!$create) {
                $error = false;
            }
            $resource->query("INSERT INTO $cTable SELECT * FROM ".$db_name.".".$cTable);
        }

        if($error === false)
            throw new \Exception('Database cloning failed!');
    }

    /**
     * Update clone db
     *
     * @param $db_clone_name
     * @param $wp_clone_directory
     */
    public function update_db($db_clone_name, $wp_clone_directory)
    {
        $resource = $this->getConnection();

        @mysqli_select_db ( $resource, $db_clone_name );

        $sql_query1 = "UPDATE wp_options SET option_value = CONCAT(option_value, '/$wp_clone_directory') WHERE option_name = 'siteurl'";

        $sql_query2 = "UPDATE wp_options SET option_value = CONCAT(option_value, '/$wp_clone_directory') WHERE option_name = 'home'";

        $resource->query($sql_query1);
        $resource->query($sql_query2);
    }

    public function close_db_connection()
    {
        $resource = $this->getConnection();
        $resource->close();
    }
}