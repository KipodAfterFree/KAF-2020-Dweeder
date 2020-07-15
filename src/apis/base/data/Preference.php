<?php

/**
 * Copyright (c) 2020 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

/**
 * Base API for configuring variables.
 */
class Preference
{
    // Constants
    public const API = "preference";

    // API & Preference names
    private string $API, $name;

    // Default value
    private $default;

    /**
     * Preference constructor.
     * @param string $name Name
     * @param mixed $default Default
     * @param string $API API
     */
    public function __construct($name, $default = null, $API = Base::API)
    {
        $this->name = $name;
        $this->default = $default;
        $this->API = $API;
    }

    /**
     * Sets the preference value.
     * @param mixed $value Value
     */
    public function set($value = null)
    {
        // Create the path
        $path = Base::file("$this->API:$this->name", self::API);
        // Check if the value is null
        if ($value === null) {
            // Remove the value
            unlink($path);
        } else {
            // Update the value
            file_put_contents($path, json_encode($value));
        }
    }

    /**
     * Gets the preference value.
     * @return mixed Value
     */
    public function get()
    {
        // Create the path
        $path = Base::file("$this->API:$this->name", self::API);
        // Check if the value exists
        if (file_exists($path) && is_file($path)) {
            // Read and return value
            return json_decode(file_get_contents($path));
        } else {
            return $this->default;
        }
    }
}