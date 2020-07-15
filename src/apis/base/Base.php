<?php

/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

include_once __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "Keystore.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "Preference.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "security" . DIRECTORY_SEPARATOR . "Authority.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "security" . DIRECTORY_SEPARATOR . "Authenticate.php";

/**
 * Base API for general utilities.
 */
class Base
{

    // Constants
    public const API = "base";

    // Directory root
    private const DIRECTORY_ROOT = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "files";

    // Directory delimiter
    private const DIRECTORY_DELIMITER = ":";

    /**
     * Handles API calls by handing them over to the callback.
     * @param callable $callback Callback to handle the request
     */
    public static function handle($callback)
    {
        // Initialize the response
        $result = new stdClass();
        // Initialize the action
        if (count($_GET) > 0) {
            // Get the action
            $requestAction = array_key_first($_GET);
            // Parse the parameters
            $requestParameters = new stdClass();
            // Loop over GET parameters
            foreach ($_GET as $name => $value) {
                if (is_string($value))
                    $requestParameters->$name = $value;
            }
            // Loop over POST parameters
            foreach ($_POST as $name => $value) {
                if (is_string($value))
                    $requestParameters->$name = $value;
            }
            // Unset the action
            unset($requestParameters->$requestAction);
            // Execute the call
            try {
                $result->result = $callback($requestAction, $requestParameters);
                $result->status = true;
            } catch (Error $error) {
                $result->result = $error->getMessage();
                $result->status = false;
            }
        }
        // Change the content type
        header("Content-Type: application/json");
        // Echo response
        echo json_encode($result);
    }

    /**
     * Creates writable paths.
     * @param string $name Path name
     * @param string $base Base directory
     * @return string Path
     */
    public static function path($name, $base = self::DIRECTORY_ROOT)
    {
        // Split name
        $split = explode(self::DIRECTORY_DELIMITER, $name, 2);
        // Check if we have to create a sub-path
        if (count($split) > 1) {
            // Append first path to the base
            $base = $base . DIRECTORY_SEPARATOR . $split[0];
            // Make sure the path exists
            if (!file_exists($base)) {
                mkdir($base);
            }
            // Return the path
            return self::path($split[1], realpath($base));
        }
        // Return the last path
        return $base . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Creates API writable file paths.
     * @param string $name File name
     * @param string $API API
     * @return string File path
     */
    public static function file($name = "", $API = self::API)
    {
        // Glue API and file name
        $name = implode(self::DIRECTORY_DELIMITER, [$API, $name]);
        // Return the path
        return self::path($name);
    }

    /**
     * Creates API writable directory paths.
     * @param string $name Directory name
     * @param string $API API
     * @return string Directory path
     */
    public static function directory($name = "", $API = self::API)
    {
        // Find parent directory
        $directory = self::file($name, $API);
        // Make sure the subdirectory exists
        if (!file_exists($directory))
            mkdir($directory);
        // Return the directory path
        return $directory;
    }

    /**
     * Creates random strings.
     * @param int $length Length
     * @return string String
     */
    public static function random($length = 0)
    {
        if ($length > 0) {
            return str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz")[0] . self::random($length - 1);
        }
        return "";
    }
}