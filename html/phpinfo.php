<?php
require_once './vendor/autoload.php';

use Char0n\FFMpegPHP\Movie;

$movie = new Movie('test.mp4');
var_dump($movie->getDuration()); 