<?php

// Suppress deprecation warnings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Also set ini value
ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php'; 