<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:22
 */

class dbConnection
{
    private function __construct()
    {
    }

    /**
     * This function creates a database connection
     * @return object containing database connection
     */
    public static function getConnection()
    {
        /** @var string hostname */
        $host = DB_HOST;
        /** @var string database username */
        $user = DB_USER;
        /** @var string database password */
        $pass = DB_PASS;
        /** @var string database name */
        $name = DB_NAME;
        /** @var string connection string */
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8";
        try
        {
            // Set connection options
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            // Create a new PDO connection
            $connection = new PDO($dsn, $user, $pass, $options);
            // Return the connection
            return $connection;
        }
        catch (PDOException $e)
        {
            echo($e->getCode() . " " . $e->getMessage());
        }
    }
}

?>
