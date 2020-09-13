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
    // Constant names
    public const API = "authenticate";

    // Cell names
    private const KEY_SALT = "salt";
    private const KEY_HASH = "hash";
    private const KEY_LOCK = "lock";

    // Constant lengths
    private const LENGTH_SALT = 128;
    private const LENGTH_NAME = 2;
    private const LENGTH_PASSWORD = 8;

    // Constant locks
    private const LOCK_VALIDATE = false;
    private const LOCK_SIGN_IN = false;
    private const LOCK_SIGN_UP = false;

    /**
     * Handles authentication requests with action and parameters.
     * @param string $action Action
     * @param array $parameters Parameters
     * @return mixed Result
     */
    public static function handle($action, $parameters)
    {
        // Check action
        if ($action === "validate") {
            // Validate locks
            if (self::LOCK_VALIDATE)
                throw new Error("Validation not allowed");

            // Validate parameters
            if (!isset($parameters->token) || !is_string($parameters->token))
                throw new Error("Parameter error");

            // Validate token
            return self::validate($parameters->token);
        } else if ($action === "signIn") {
            // Validate locks
            if (self::LOCK_SIGN_IN)
                throw new Error("Sign-In not allowed");

            // Validate parameters
            if (!isset($parameters->name) || !isset($parameters->password) || !is_string($parameters->name) || !is_string($parameters->password))
                throw new Error("Parameter error");

            // Sign the user in
            return self::signIn($parameters->name, $parameters->password);
        } else if ($action === "signUp") {
            // Validate locks
            if (self::LOCK_SIGN_UP)
                throw new Error("Sign-Up not allowed");

            // Validate parameters
            if (!isset($parameters->name) || !isset($parameters->password) || !is_string($parameters->name) || !is_string($parameters->password))
                throw new Error("Parameter error");

            // Sign the user up
            return self::signUp($parameters->name, $parameters->password);
        }
        // Throw error
        throw new Error("Unhandled hook");
    }

    /**
     * Authenticates using a bearer token.
     * @param string $token Token
     * @return string User ID
     */
    public static function validate($token)
    {
        // Validate using authority
        return Token::validate($token);
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

        // Make sure the user does not exist
        if (Database::check(self::API, $name))
            throw new Error("User already exists");

        // Insert a new entry
        Database::insert(self::API, $name);

        // Generate salt and hash
        $salt = Base::random(self::LENGTH_SALT);
        $hash = hash("sha256", $password . $salt);
        $time = strval(0);

        // Set user information
        Database::write(self::API, $name, self::KEY_SALT, $salt);
        Database::write(self::API, $name, self::KEY_HASH, $hash);
        Database::write(self::API, $name, self::KEY_LOCK, $time);

        // Return a success result
        return $name;
    }

    /**
     * Signs in an existing user.
     * @param string $name Name
     * @param string $password Password
     * @return string Token
     */
    public static function signIn($name, $password)
    {

        // Make sure the user exists
        if (!Database::check(self::API, $name))
            throw new Error("User does not exist");

        // Get keystore values
        $lock = Database::read(self::API, $name, self::KEY_LOCK, 0);
        $salt = Database::read(self::API, $name, self::KEY_SALT, "");
        $hash = Database::read(self::API, $name, self::KEY_HASH, "");

        // Make sure the user is not locked
        if (intval($lock) > time())
            throw new Error("User is locked");

        // Check password match
        if (hash("sha256", $password . $salt) === $hash) {
            // Issue a new token
            return Token::issue($name, 60 * 60 * 24 * 28);
        } else {
            // Calculate new lock time
            $time = strval(time() + 10);

            // Lock the user
            Database::write(self::API, $name, self::KEY_LOCK, $time);

            // Throw an error
            throw new Error("Wrong password");
        }
    }
}