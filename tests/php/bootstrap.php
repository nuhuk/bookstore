<?php
// Ensure the API file can be required without executing the router logic.
define('BOOKS_API_TEST_MODE', true);

date_default_timezone_set('UTC');

require_once __DIR__ . '/../../books_api.php';
