<?php
//https://kronos.rte.ie/realmedia//2019/0718/audiobrowse/?C=M;O=D
$url = "https://kronos.rte.ie/realmedia//2019/0718/audiobrowse/18072019230000-rnag-scothandeardaoin-pid3125-25380000_audio.mp3";
$url = $_GET["url"];
$path = parse_url($url, PHP_URL_PATH);
$outputFile = "audio/".basename($path);
$jsonFileName = "audio/".str_replace(".mp3",".json", $outputFile);

$ch = curl_init($url);
$fp = fopen($outputFile, 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);


$jsonFileName = str_replace(".mp3",".json", $outputFile);
//echo $outputFile." ".$jsonFileName;

$createAudioWaveCommand = "audiowaveform -i $outputFile -o $jsonFileName -b 8";
$audiowaveform = exec($createAudioWaveCommand);

unlink($outputFile);
echo $outputFile." deleted?";

echo "<a href='peaks.php?file=".$url."'>File</a>";
echo $jsonFileName;
?>