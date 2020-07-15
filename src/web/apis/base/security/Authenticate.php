<?php

/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

/**
 * Authenticate API for user authentication.
 */
class Authenticate
{
    // API string
    public const API = "authenticate";

    // Column names
    private const COLUMN_SALT = "salt";
    private const COLUMN_HASH = "hash";
    private const COLUMN_LOCK = "lock";

    // Constant lengths
    private const LENGTH_SALT = 512;
    private const LENGTH_NAME = 2;
    private const LENGTH_PASSWORD = 8;

    // Preferences
    private static Preference $validate, $signUp, $signIn;

    // Authority & Keystore
    private static Authority $authority;
    private static Keystore $keystore;

    /**
     * API hook.
     */
    public static function handle()
    {
        // Handle the request
        Base::handle(function ($action, $parameters) {
            if ($action === "validate") {
                // Validate locks
                if (!self::$validate->get())
                    throw new Error("Validation not allowed");
                // Validate parameters
                if (!isset($parameters->token) || !is_string($parameters->token))
                    throw new Error("Parameter error");
                // Validate token
                return self::validate($parameters->token);
            } else if ($action === "signIn") {
                // Validate locks
                if (!self::$signIn->get())
                    throw new Error("Sign-In not allowed");
                // Validate parameters
                if (!isset($parameters->name) || !isset($parameters->password) || !is_string($parameters->name) || !is_string($parameters->password))
                    throw new Error("Parameter error");
                // Sign the user in
                return self::signIn($parameters->name, $parameters->password);
            } else if ($action === "signUp") {
                // Validate locks
                if (!self::$signUp->get())
                    throw new Error("Sign-Up not allowed");
                // Validate parameters
                if (!isset($parameters->name) || !isset($parameters->password) || !is_string($parameters->name) || !is_string($parameters->password))
                    throw new Error("Parameter error");
                // Sign the user up
                return self::signUp($parameters->name, $parameters->password);
            }
            return [false, "Unhandled hook"];
        });
    }

    /**
     * API initializer.
     */
    public static function initialize()
    {
        // Load preferences
        self::$validate = new Preference("validate", true, self::API);
        self::$signUp = new Preference("signUp", true, self::API);
        self::$signIn = new Preference("signIn", true, self::API);
        // Make sure the keystore is initiated.
        self::$keystore = new Keystore(self::API);
        // Make sure the authority is initiated.
        self::$authority = new Authority(self::API);
    }

    /**
     * Authenticates using a bearer token.
     * @param string $token Token
     * @return string User ID
     */
    public static function validate($token)
    {
        // Authenticate the user using tokens
        return self::$authority->validate($token);
    }

    /**
     * Signs up a new user.
     * @param string $name Name
     * @param string $password Password
     * @return string User ID
     */
    public static function signUp($name, $password)
    {
        // Validate name
        if (strlen($name) < self::LENGTH_NAME)
            throw new Error("Name too short");
        // Validate password
        if (strlen($password) < self::LENGTH_PASSWORD)
            throw new Error("Password too short");
        // Create user ID
        $userID = bin2hex($name);
        // Make sure the user does not exist
        if (self::$keystore->exists($userID))
            throw new Error("User already exists");
        // Insert a new entry
        self::$keystore->insert($userID);
        // Generate salt and hash
        $salt = Base::random(self::LENGTH_SALT);
        $hash = hash("sha256", $password . $salt);
        $time = strval(0);
        // Set user information
        self::$keystore->set($userID, self::COLUMN_SALT, $salt);
        self::$keystore->set($userID, self::COLUMN_HASH, $hash);
        self::$keystore->set($userID, self::COLUMN_LOCK, $time);
        // Return a success result
        return $userID;
    }

    /**
     * Signs in an existing user.
     * @param string $name Name
     * @param string $password Password
     * @return string Token
     */
    public static function signIn($name, $password)
    {
        // Create user ID
        $userID = bin2hex($name);
        // Make sure the user exists
        if (!self::$keystore->exists($userID))
            throw new Error("User does not exist");
        // Get keystore values
        $lock = self::$keystore->get($userID, self::COLUMN_LOCK);
        $salt = self::$keystore->get($userID, self::COLUMN_SALT);
        $hash = self::$keystore->get($userID, self::COLUMN_HASH);
        // Make sure the user is not locked
        if (intval($lock) > time())
            throw new Error("User is locked");
        // Check password match
        if (hash("sha256", $password . $salt) === $hash) {
            // Issue a new token
            return self::$authority->issue($userID);
        } else {
            // Calculate new lock time
            $time = strval(time() + 10);
            // Lock the user
            self::$keystore->set($userID, self::COLUMN_LOCK, $time);
            // Throw an error
            throw new Error("Wrong password");
        }
    }
}