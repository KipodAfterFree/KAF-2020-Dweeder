<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "APIs.php";

include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "WebDriverPlatform.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "WebDriverHasInputDevices.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "JavaScriptExecutor.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "WebDriverSearchContext.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "WebDriver.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "WebDriverCapabilities.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "WebDriverCommandExecutor.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Chrome" . DIRECTORY_SEPARATOR . "ChromeOptions.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Firefox" . DIRECTORY_SEPARATOR . "FirefoxDriver.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "WebDriverCommand.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "WebDriverResponse.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "DriverCommand.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "WebDriverBrowserType.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "HttpCommandExecutor.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "DesiredCapabilities.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "WebDriverCapabilityType.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Remote" . DIRECTORY_SEPARATOR . "RemoteWebDriver.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Exception" . DIRECTORY_SEPARATOR . "WebDriverException.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Exception" . DIRECTORY_SEPARATOR . "UnknownErrorException.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Exception" . DIRECTORY_SEPARATOR . "WebDriverCurlException.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "selenium" . DIRECTORY_SEPARATOR . "Exception" . DIRECTORY_SEPARATOR . "SessionNotCreatedException.php";

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

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
                    // Push id to list
                    array_push($list, $id);
                    // Write list
                    file_put_contents($mentions, json_encode($list));
                    // Check whether the admin needs to test the page
                    if ($name === "shuky") {
//                        throw new Error("https://" . $_SERVER["HTTP_HOST"] . "/?dweed=" . $id);

                        $host = "http://selenium:4444/wd/hub";

                        $capabilities = DesiredCapabilities::chrome();
                        $capabilities->setCapability("acceptSslCerts", true);
                        $capabilities->setCapability("javascriptEnabled", true);
//                        $capabilities->setJavascriptEnabled(true);

                        $driver = RemoteWebDriver::create($host, $capabilities, 5000);
                        $driver->get("http://" . $_SERVER["HTTP_HOST"] . "/?dweed=" . $id . "&hehe");
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
