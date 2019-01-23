<?php
require '../vendor/autoload.php';
require "../functions.php";
$dotenv = new Dotenv\Dotenv('../../');
$dotenv->load();
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;


$serviceAccount = ServiceAccount::fromJsonFile('../../firebase.json');

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->withDatabaseUri('https://radio-a8e0f.firebaseio.com')
    ->create();

$db = $firebase->getDatabase();




$category = $argv[1];
$fileName = $argv[2];



$url = "http://radiodj.rte.ie/media/flashbriefing/$fileName";

$filename = "audio/".$category."-".date("Y-m-d-his").".mp4";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);

if (curl_error($ch)) {
    $log = "ERROR!!! ".curl_error($ch);
    log2Firebase($log,"video","");
}

$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $output);
fclose($myfile);

curl_close($ch);

$log = "saved $url as $filename";
log2Firebase($log,"video","");


$bucketName = getenv('bucketName');
$awsVideo = s3Upload($filename, $category, $bucketName, true);

$log = "Upload video to $awsVideo";
log2Firebase($log,"video","");

$db->getReference('flashBriefings/' . $category . '/videoUrl')->set($awsVideo);
$db->getReference('flashBriefings/' . $category . '/videoPublishDate')->set(date("Y-m-d H:i:s"));

$flashBriefing = $db->getReference('flashBriefings/' . $category)
->getValue();

$uid = $db->getReference('flashBriefings/' . $category)
->getKey();

$data = array(
    "uid"=>$flashBriefing["uid"],
    "updateDate"=>date("Y-m-d\TH:i:s.0\Z"),
    "titleText" => $flashBriefing["titleText"],
    "mainText" => $flashBriefing["mainText"],
    "streamUrl" => $flashBriefing["streamUrl"],
    "videoUrl" => $flashBriefing["videoUrl"],
    "redirectionUrl" => $flashBriefing["redirectionUrl"]
);

$json = json_encode($data,JSON_UNESCAPED_SLASHES);

$file = "audio/feed.json";


$myfile = fopen($file, "w") or die("Unable to open file!");
fwrite($myfile, $json);

fclose($myfile);

$log = "<p>Save Flash briefing json to $file</p>";

$log .= "<p>Created new file for $category ".s3Upload($file, $category, getenv("flashbriefingBucket"), false)."</p>";

log2Firebase($log,"video","");
