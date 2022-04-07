<?php
class Database
{
    private static $connection;

    public static function connect()
    {
        $connection = pg_connect("hostaddr=127.0.0.1 port=5432 dbname=web user=dev_client password=1234");

        if (!$connection) {
            echo "Database erorr: " . pg_errormessage($connection);
            exit;
        }

        Database::$connection = $connection;
    }

    public static function getItem(string $table, int $id)
    {
        $query = "SELECT * FROM $table WHERE id = $id";
        $result = pg_query(Database::$connection, $query);
        return pg_fetch_assoc($result);
    }

    public static function getItems(string $table, int $take = 100)
    {
        $query = "SELECT * FROM $table LIMIT $take";
        $result = pg_query(Database::$connection, $query);
        return pg_fetch_all($result);
    }

    public static function getItemsWhere(string $table, array $conditions,  $what = '*', int $take = 100)
    {
        $condQuery = '';
        $i = 0;
        foreach ($conditions as $key => $val) {
            if ($i == 0) {
                $condQuery .= "$key = '$val'";
            } else {
                $condQuery .= " AND $key = '$val'";
            }
            $i++;
        }

        if (is_array($what)) {
            $what = join(',', $what);
        }

        $query = "SELECT $what FROM $table WHERE $condQuery LIMIT $take";
        $result = pg_query(Database::$connection, $query);
        return pg_fetch_all($result);
    }

    public static function saveItem($table, $item)
    {
        $itemPropNames = implode(',', array_keys(get_class_vars(get_class($item))));
        $itemProps = implode(",", array_map(fn ($el) => "'" . $el . "'", get_object_vars($item)));
        $query = "INSERT INTO $table({$itemPropNames}) VALUES({$itemProps})";
        return pg_query(Database::$connection, $query);
    }

    public static function updateItem($table, $id, $fields)
    {
        $fieldsQuery = '';
        $i = 0;
        foreach ($fields as $key => $val) {
            if ($i == 0) {
                $fieldsQuery .= "$key = '$val'";
            } else {
                $fieldsQuery .= " , $key = '$val'";
            }
            $i++;
        }

        $query = "UPDATE $table SET $fieldsQuery  WHERE id = $id";
        return pg_query(Database::$connection, $query);
    }

    public static function deleteItem($table, $id)
    {
        $query = "DELETE FROM $table WHERE id = $id";
        $result = pg_query(Database::$connection, $query);
        return pg_fetch_assoc($result);
    }
}
