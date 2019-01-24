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
    ->create();

$db = $firebase->getDatabase();

$category = $_GET["category"];


$flashBriefing = $db->getReference('flashBriefings/' . $category)
->getValue();

$uid = $db->getReference('flashBriefings/' . $category)
->getKey();


$streamUrl = $flashBriefing["streamUrl"];

$mainText = $flashBriefing["mainText"];
$titleText = $flashBriefing["titleText"];
$videoUrl = $flashBriefing["videoUrl"];
$redirctionUrl = $flashBriefing["redirectionUrl"];
$image = $flashBriefing["image"];
$categoryDesc = $flashBriefing["category"];
$audioId = $flashBriefing["uid"];
$duration = $flashBriefing["duration"];
$fileSize = $flashBriefing["fileSize"];
$videoPublishDate = $flashBriefing["videoPublishDate"];
//set Bucket to default overwritten if flash briefing

$data = array(
    "uid"=>$audioId,
    "updateDate"=>date("Y-m-d\TH:i:s.0\Z"),
    "titleText" => $titleText,
    "mainText" => $mainText,
    "streamUrl" => $streamUrl,
    "redirectionUrl" => $redirctionUrl,
);


if($flashBriefing["hasVideo"]){

    $dateCreated = new DateTime($videoPublishDate);
    $today = new DateTime();
    $diff = $today->diff($dateCreated);
    $diff = $diff->format('%d');

    log2Firebase("Video updated $diff days ago","audio-processor",$audioId);


    if($diff < 1){
        $data["videoUrl"] = $videoUrl;
    }else{
        $data["videoUrl"] = "";
    }
}
$json = json_encode($data,JSON_UNESCAPED_SLASHES);

$file = "audio/".$category.".json";


$myfile = fopen($file, "w") or die("Unable to open file!");
fwrite($myfile, $json);

fclose($myfile);

$log = "<p>Save Flash briefing json to $file</p>";

$log .= "<p>Created new file for $category ".s3Upload($file, $category, getenv("flashbriefingBucket"), false)."</p>";

log2Firebase($log,"audio-updater",$uid);

$obj["title"]  = $titleText;
$obj["url"] = $redirctionUrl;
$obj["description"] =$mainText;
$obj["category"] = $categoryDesc;
$obj["imageUrl"] = $image;
$obj["imageLink"]  = $redirctionUrl;
$obj["guid"] = $audioId;
$obj["link"] = $redirctionUrl;
$obj["audioUrl"] = $streamUrl;
$obj["audioLength"] = $fileSize;
$obj["audioType"] ="audio/mpeg";
$obj["duration"] = $duration;

$fileName="audio/$category.xml";
$xmlUrl = makeXMLFile($fileName,$obj);

$awsXml = s3Upload($fileName, $category, getenv("flashbriefingBucket"), false);

$log = "<p>making google rss $fileName</p>";
$log .= "<p>upload $xmlUrl to $awsXml </p>";
log2Firebase($log,"audio-updater",$audioId);


echo $_GET['callback'] . '('.$json.')';// Response that will be sent to the user
?>