<?php
// Include the authenticate API
include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "Base.php";

// Initialize the API
Authenticate::initialize();

// Handle the API call
Authenticate::handle();