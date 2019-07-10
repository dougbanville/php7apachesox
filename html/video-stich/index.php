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
$command = "ffmpeg -f concat -safe 0 -i mylist.txt -c copy output.mp4";

exec($command);

echo "DONE! <a href='output.mp4'>Video</a>";
