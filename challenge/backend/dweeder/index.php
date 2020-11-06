<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "APIs.php";

const DWEEDER_DIRECTORY = "/opt";
const DWEEDER_DWEEDS_DIRECTORY = DWEEDER_DIRECTORY . DIRECTORY_SEPARATOR . "dweeds";
const DWEEDER_MENTIONS_DIRECTORY = DWEEDER_DIRECTORY . DIRECTORY_SEPARATOR . "mentions";

const DWEEDER_WELCOME_ID = "3136303032343435303037643539";

Base::handle(function ($action, $parameters) {

    if ($action === "newUser") {
        // Validate parameters
        if (!isset($parameters->name) || empty($parameters->name))
            throw new Error("Name parameter error");
        if (!isset($parameters->handle) || empty($parameters->handle))
            throw new Error("Handle parameter error");

        // Make sure the handle is only one word
        if (preg_match("/^([A-Z]|[a-z]|[0-9])+$/", $parameters->handle) !== 1)
            throw new Error("Handle is invalid");

        // Make sure the user does not exist already
        $mentions = DWEEDER_MENTIONS_DIRECTORY . DIRECTORY_SEPARATOR . bin2hex($parameters->handle);
        if (file_exists($mentions))
            throw new Error("User already exists");

        // Create a file for the mentions
        file_put_contents($mentions, json_encode(array(DWEEDER_WELCOME_ID)));

        // Create the object for the token
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
    $mentions = DWEEDER_MENTIONS_DIRECTORY . DIRECTORY_SEPARATOR . bin2hex($user->handle);

    // Unset parameter
    unset($parameters->token);

    // Check actions
    if ($action === "listDweed") {
        return array_slice(scandir(DWEEDER_DWEEDS_DIRECTORY), 2);
    }

    if ($action === "writeDweed") {
        // Validate parameters
        if (!isset($parameters->title) || empty($parameters->title))
            throw new Error("Title parameter error");
        if (!isset($parameters->contents) || empty($parameters->contents))
            throw new Error("Contents parameter error");

        // Create ID
        $id = bin2hex(time()) . bin2hex(Base::random(4));

        // Create file name
        $file = DWEEDER_DWEEDS_DIRECTORY . DIRECTORY_SEPARATOR . $id;

        // Set date & user parameter
        $parameters->time = date("H:i");
        $parameters->handle = $user->handle;

        // Write dweed
        file_put_contents($file, json_encode($parameters));

        // Check for mentions
        $words = explode(" ", $parameters->contents);
        foreach ($words as $word) {
            if (preg_match("/^@([A-Z]|[a-z]|[0-9])+$/", $word) === 1) {
                $name = substr($word, 1);
                // Create path
                $mentions = DWEEDER_MENTIONS_DIRECTORY . DIRECTORY_SEPARATOR . bin2hex($name);
                // Make sure mentions exists
                if (file_exists($mentions)) {
                    // Read list
                    $list = json_decode(file_get_contents($mentions));
                    // Make sure the list is not null
                    if($list === null)
                        $list = array();
                    // Push id to list
                    array_push($list, $id);
                    // Write list
                    file_put_contents($mentions, json_encode($list));
                    // Check whether the admin needs to test the page
                    if ($name === "shuky") {
                        // Send the request
                        file_get_contents("http://administrator/?visit=" . urlencode("http://" . $_SERVER["HTTP_HOST"] . "/?dweed=" . $id));
                    }
                    // Break? Only one mention allowed
                    break;
                }
            }
        }

        // Return the dweed id
        return $id;
    }

    if ($action === "readDweed") {
        // Validate parameters
        if (!isset($parameters->id) || empty($parameters->id))
            throw new Error("ID parameter error");

        // Filter parameters
        $id = $parameters->id;
        $id = basename($id);

        $file = DWEEDER_DWEEDS_DIRECTORY . DIRECTORY_SEPARATOR . $id;

        // Make sure the dweet exists
        if (!file_exists($file))
            throw new Error("Dweed does not exist");

        // Read dweed
        return json_decode(file_get_contents($file));
    }

    if ($action === "listMentions") {
        // Make sure the mentions directory exists
        if (!file_exists($mentions))
            throw new Error("User does not exist");

        return json_decode(file_get_contents($mentions));
    }

    throw new Error("Unknown action");
});
