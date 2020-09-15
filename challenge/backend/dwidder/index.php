<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "APIs.php";

Base::handle(function ($action, $parameters) {

    if ($action === "newUser") {
        // Validate parameters
        if (!isset($parameters->name) || empty($parameters->name))
            throw new Error("Name parameter error");
        if (!isset($parameters->handle) || empty($parameters->handle))
            throw new Error("Handle parameter error");
        // Create the object
        $user = new stdClass();
        $user->name = $parameters->name;
        $user->handle = $parameters->handle;
        // Return a token
        return Token::issue($user);
    }

    // Validate token parameter
    if (!isset($parameters->token) || empty($parameters->token))
        throw new Error("Token parameter error");
    // Validate token
    $user = Token::validate($parameters->token);
    // Unset parameter
    unset($parameters->token);

    // Check actions
    if ($action === "listDweed") {
        return array_slice(scandir("/opt"), 2);
    }

    if ($action === "writeDweed") {
        // Validate parameters
        if (!isset($parameters->title) || empty($parameters->title))
            throw new Error("Title parameter error");
        if (!isset($parameters->contents) || empty($parameters->contents))
            throw new Error("Contents parameter error");

        // Create ID
        $id = bin2hex(time()) . bin2hex(Base::random(4));

        // Create JSON
        $file = "/opt/$id";

        // Set date & user parameter
        $parameters->date = date("H:i");
        $parameters->handle = $user->handle;

        // Write
        file_put_contents($file, json_encode($parameters));

        return $id;
    }

    if ($action === "readDweed") {
        // Validate parameters
        if (!isset($parameters->id) || empty($parameters->id))
            throw new Error("ID parameter error");

        // Filter parameters
        $dweedId = $parameters->id;
        $dweedId = basename($dweedId);

        // Make sure the dweet exists
        if (!file_exists("/opt/$dweedId"))
            throw new Error("Dweed does not exist");

        // Read dweet
        return json_decode(file_get_contents("/opt/$dweedId"));
    }
    throw new Error("Unknown action");
});
