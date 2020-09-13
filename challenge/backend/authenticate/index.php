<?php

// Include APIs
include_once __DIR__ . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "Authenticate.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "APIs.php";

// Handle the API call
Base::handle(function ($action, $parameters) {
    return Authenticate::handle($action, $parameters);
});