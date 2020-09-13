<?php

/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

/**
 * Base API for general utilities.
 */
class Base
{

    /**
     * Calls remote APIs with URL and parameters.
     * @param string $url URL
     * @param string $action Action
     * @param array $parameters Parameters
     * @return mixed Result
     */
    public static function call($url, $action, $parameters)
    {
        // Create query
        $query = http_build_query($parameters);

        // Create the URL
        $url = "$url/?$action";

        // Execute the request
        $contents = file_get_contents($url, false, stream_context_create([
            "http" => [
                "method" => "POST",
                "content" => $query
            ]
        ]));

        // Parse the contents as JSON
        $contents = json_decode($contents);

        // Check contents structure
        if (!property_exists($contents, "status") || !property_exists($contents, "result"))
            throw new Error("API response malformed");

        // Throw error
        if (!$contents->status)
            throw new Error($contents->result);

        // Return result
        return $contents->result;
    }

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
     * Creates random strings.
     * @param int $length Length
     * @return string String
     */
    public static function random($length = 0)
    {
        // Check if generation is finished
        if ($length === 0)
            return "";

        // Shuffle string
        return str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz")[0] . self::random($length - 1);
    }
}