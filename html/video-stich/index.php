<?php
//https://trac.ffmpeg.org/wiki/Concatenate
$command = "ffmpeg -f concat -safe 0 -i mylist.txt -c copy output";

exec($command);