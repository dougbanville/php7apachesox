<?php
require 'vendor/autoload.php';
$dotenv = new Dotenv\Dotenv('../../');
$dotenv->load();
$version = getenv("version");
echo "<h1>Version $version</h2>";
echo phpinfo();