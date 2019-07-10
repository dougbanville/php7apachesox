<?php
// Script start

$startTime = microtime(true);

// Code ...

// Script end
function rutime($ru, $rus, $index)
{
    return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
         - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
}
//https://trac.ffmpeg.org/wiki/Concatenate
$command = "ffmpeg -f concat -safe 0 -i mylist.txt -c copy output-p1.mp4";

echo exec($command);

//echo "hello";

$endTime = microtime(true);
$diff = round($endTime - $startTime);
$minutes = floor($diff / 60); //only minutes
$seconds = $diff % 60; //remaining seconds, using modulo operator
echo "script execution time: minutes:$minutes, seconds:$seconds"; //value in seconds
