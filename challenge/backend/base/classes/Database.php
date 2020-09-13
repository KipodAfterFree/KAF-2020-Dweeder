<?php

/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

/**
 * Base API for storage communication.
 */
class Database
{
    /**
     * Executes a remote database operation.
     * @param string $action Action
     * @param array $parameters Parameters
     * @return mixed Result
     */
    private static function call($action, $parameters)
    {
        // Fetch environment variables
        $url = getenv("DATABASE_URL");
        $scope = getenv("DATABASE_SCOPE");
        $token = getenv("DATABASE_TOKEN");

        // Append the scope parameter
        $parameters["scope"] = $scope;

        // Append the token parameter
        $parameters["token"] = $token;

        // Call the API
        return Base::call($url, $action, $parameters);
    }

    /**
     * Check whether a database table entry exists.
     * @param string $table Table
     * @param string $entry Entry
     * @return mixed Result
     */
    public static function check($table, $entry)
    {
        // Execute call
        return self::call("check", [
            "table" => $table,
            "entry" => $entry
        ]);
    }

    /**
     * Inserts a database table entry.
     * @param string $table Table
     * @param string $entry Entry
     * @return mixed Result
     */
    public static function insert($table, $entry)
    {
        // Execute call
        return self::call("insert", [
            "table" => $table,
            "entry" => $entry
        ]);
    }

    /**
     * Removes a database table entry.
     * @param string $table Table
     * @param string $entry Entry
     * @return mixed Result
     */
    public static function remove($table, $entry)
    {
        // Execute call
        return self::call("remove", [
            "table" => $table,
            "entry" => $entry
        ]);
    }

    /**
     * Reads a database table value.
     * @param string $table Table
     * @param string $entry Entry
     * @param string $key Key
     * @param string $value Value
     * @return mixed Result
     */
    public static function read($table, $entry, $key, $value)
    {
        // Execute call
        return self::call("read", [
            "table" => $table,
            "entry" => $entry,
            "key" => $key,
            "value" => $value
        ]);
    }

    /**
     * Writes a database table value.
     * @param string $table Table
     * @param string $entry Entry
     * @param string $key Key
     * @param string $value Value
     * @return mixed Result
     */
    public static function write($table, $entry, $key, $value)
    {
        // Execute call
        return self::call("write", [
            "table" => $table,
            "entry" => $entry,
            "key" => $key,
            "value" => $value
        ]);
    }
}