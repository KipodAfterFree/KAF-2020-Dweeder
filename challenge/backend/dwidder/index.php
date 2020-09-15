<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "APIs.php";

Base::handle(function ($action, $parameters) {
    if ($action === "newUser") {
        if (!isset($parameters->name) || empty($parameters->name))
            throw new Error("Name parameter error");
        // Return a token
        return TOken::issue($parameters->name);
    }
    if ($action === "listDweed") {
        return array_slice(scandir("/opt"), 2);
    }
    if ($action === "writeDweed") {
        if (!isset($parameters->token) || empty($parameters->token))
            throw new Error("Token parameter error");
        $userName = Token::validate($parameters->token);
        if (!isset($parameters->title) || empty($parameters->title))
            throw new Error("Title parameter error");
        if (!isset($parameters->contents) || empty($parameters->contents))
            throw new Error("Contents parameter error");
        // Create ID
        $id = bin2hex(time()) . bin2hex(Base::random(4));
        // Create JSON
        $file = "/opt/$id";
        // Unset token parameter
        unset($parameters->token);
        // Set date & user parameter
        $parameters->name = $userName;
        $parameters->date = date("H:i");
        // Write
        file_put_contents($file, json_encode($parameters));
        return $id;
    }
    if ($action === "readDweed") {
        if (!isset($parameters->id) || empty($parameters->id))
            throw new Error("ID parameter error");
        $dweedId = $parameters->id;
        $dweedId = basename($dweedId);
        if (!file_exists("/opt/$dweedId"))
            throw new Error("Dweed does not exist");
        return json_decode(file_get_contents("/opt/$dweedId"));
    }
    throw new Error("Unknown action");
});
