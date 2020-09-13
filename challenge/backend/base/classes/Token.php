<?php

/**
 * Copyright (c) 2020 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

class Token
{
    // Token properties
    private const DELIMITER = ":";
    private const VARIABLE = "TOKEN_SECRET";

    // Token parameters
    private const PARAMETER_EXPIRY = "expiry";
    private const PARAMETER_CONTENT = "content";

    // Token slices
    private const SLICE_STRING = 0;
    private const SLICE_SIGNATURE = 1;

    /**
     * Issues a token.
     * @param mixed $content Content
     * @param int | null $validity Validity
     * @return string Token
     */
    public static function issue($content, $validity = null)
    {
        // Create token parameters
        $tokenExpiry = $validity ? time() + intval($validity) : null;
        $tokenContent = $content;

        // Create token array
        $tokenObject = new stdClass();

        // Insert token parameters
        $tokenObject->{Token::PARAMETER_EXPIRY} = $tokenExpiry;
        $tokenObject->{Token::PARAMETER_CONTENT} = $tokenContent;

        // Create token string
        $tokenString = base64_encode(json_encode($tokenObject));

        // Generate signature
        $tokenSignature = base64_encode(hash_hmac("sha256", $tokenString, getenv(Token::VARIABLE), true));

        // Create token slices
        $tokenSlices = array();

        // Insert token slices
        $tokenSlices[Token::SLICE_STRING] = $tokenString;
        $tokenSlices[Token::SLICE_SIGNATURE] = $tokenSignature;

        // Return compiled token
        return implode(Token::DELIMITER, $tokenSlices);
    }

    /**
     * Validates a token.
     * @param string $token Token
     * @return mixed Content
     */
    public static function validate($token)
    {
        // Decompile token
        $tokenSlices = explode(Token::DELIMITER, $token);

        // Make sure the slices exist
        if (!isset($tokenSlices[Token::SLICE_STRING]) || !isset($tokenSlices[Token::SLICE_SIGNATURE]))
            throw new Error("Invalid token structure");

        // Extract token slices
        $tokenString = $tokenSlices[Token::SLICE_STRING];
        $tokenSignature = $tokenSlices[Token::SLICE_SIGNATURE];

        // Make sure the signature is correct
        if (base64_encode(hash_hmac("sha256", $tokenString, getenv(Token::VARIABLE), true)) !== $tokenSignature)
            throw new Error("Invalid token signature");

        // Extract token array
        $tokenObject = json_decode(base64_decode($tokenString));

        // Extract token parameters
        $tokenExpiry = $tokenObject->{Token::PARAMETER_EXPIRY};
        $tokenContent = $tokenObject->{Token::PARAMETER_CONTENT};

        // Make sure the token is not expired
        if ($tokenExpiry !== null && time() > intval($tokenExpiry))
            throw new Error("Invalid token expiry");

        // Return content
        return $tokenContent;
    }
}