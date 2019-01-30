<?php
require '../vendor/autoload.php';
require "../functions.php";
$dotenv = new Dotenv\Dotenv('../../');
$dotenv->load();
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

/*

1. Trim the audio located in audio floder
2. Create JSON file of peaks
3. Upload trimmed audio to s3 - bucket defined in .env
4. If category is flash briefing make a flash briefing json file and upload it to bucket
5. Get a file name from for clipper - that's the next bit
*/

$audioId = str_replace(",", "", $argv[1]);
$inputFile = $argv[2];
$outputFile = $argv[3];
$audioIn = $argv[4];
$duration = $argv[5];
$gain = $argv[6];//defaul value can be overwritten by flash briefing settingsz

$isFlashBriefing = false;
//set bucket override if flash briefing
$awsBucket = getenv('bucketName');


//get databse details 
$serviceAccount = ServiceAccount::fromJsonFile('../../firebase.json');
$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->create();

$db = $firebase->getDatabase();

$audioClip = $db->getReference('audioclips/' . $audioId)
    ->getValue();

    $category = $audioClip["category"];

    if ($audioClip["flashBriefing"]) {
        $isFlashBriefing = true;
        
        
        $flashBriefing = $db->getReference('flashBriefings/' . $category)
        ->getValue();
        $gain = $flashBriefing["gain"];
    }


//use sox to trim the audio and add gain
$trimAudioAddGaincommand = "/usr/bin/sox $inputFile $outputFile trim $audioIn $duration gain $gain";

exec($trimAudioAddGaincommand);
//make json file of audio peaeks
$createAudioWaveCommand = "audiowaveform -i $outputFile -o $outputFile.json  -b 8";
$audiowaveform = exec($createAudioWaveCommand);

//upload the file to AWS
$newFileName = s3Upload($outputFile,$category,$awsBucket,true);
$log = "<p>upload to aws as $newFileName</p>";
//and the json file
$json = s3Upload("$outputFile.json", $category, $awsBucket);
//deletet the file we received
$fileSize =  filesize($inputFile);
log2Firebase("$inputFile File size $fileSize","audio-processor","");
unlink($inputFile);


$log .= "<p>Trim audio add gain:</p> <code>$trimAudioAddGaincommand</code>";
$log .= "<p>make audiowave</p>";
$log .= "<code>$createAudioWaveCommand</code>";
log2Firebase($log,"audio-processor","");

$db->getReference('audioclips/' . $audioId . '/publishStatus')->set("complete");
$db->getReference('audioclips/' . $audioId . '/publishDate')->set(date("Y-m-d H:i:s"));
$db->getReference('audioclips/' . $audioId . '/fileSize')->set($fileSize);

$db->getReference('audioclips/' . $audioId . '/awsaudio')->set($newFileName);
$db->getReference('audioclips/' . $audioId . '/wave-json')->set($json);


if ($isFlashBriefing) {

    //! override $awsBucket
    $awsBucket = getenv("flashbriefingBucket");

    $mainText = $flashBriefing["mainText"];
    $titleText = $flashBriefing["titleText"];
    $videoUrl = $flashBriefing["videoUrl"];
    $redirctionUrl = $flashBriefing["redirectionUrl"];
    $image = $flashBriefing["image"];
    $categoryDesc = $flashBriefing["category"];
    $videoPublishDate = $flashBriefing["videoPublishDate"];

    $db->getReference('flashBriefings/' . $category . '/streamUrl')->set($newFileName);
    $db->getReference('flashBriefings/' . $category . '/uid')->set($audioId);
    $db->getReference('flashBriefings/' . $category . '/lastUpdated')->set(date("Y-m-d H:i:s"));
    $db->getReference('flashBriefings/' . $category . '/fileSize')->set($fileSize);


    //save duration in seconds
    $duration = preg_replace('/\\.[^.\\s]{3,4}$/', '', $duration);

    list($minutes,$seconds) = explode(':', $duration);

    //print_r($minutes." ".$seconds);

    $minutes = $minutes * 60;

    $seconds =  $minutes + $seconds;

    log2Firebase("set duration to $seconds for $category","audio-processor",$audioId);

    $db->getReference('flashBriefings/' . $category . '/duration')->set($seconds);


    $data = array(
        "uid"=>$audioId,
        "updateDate"=>date("Y-m-d\TH:i:s.0\Z"),
        "titleText" => $titleText,
        "mainText" => $mainText,
        "streamUrl" => $newFileName,
        "redirectionUrl" => $redirctionUrl
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

    $file = "audio/feed.json";


    $myfile = fopen($file, "w") or die("Unable to open file!");
    fwrite($myfile, $json);

    fclose($myfile);

    $log = "<p>Save Flash briefing json to $file</p>";

    $log .= "<p>Created new file for $category ".s3Upload($file, $category, $awsBucket)."</p>";

    log2Firebase($log,"audio-processor",$audioId);
    //make google file

    $obj["title"]  = $titleText;
    $obj["url"] = $redirctionUrl;
    $obj["description"] =$mainText;
    $obj["category"] = $categoryDesc;
    $obj["imageUrl"] = $image;
    $obj["imageLink"]  = $redirctionUrl;
    $obj["guid"] =$audioId;
    $obj["link"] = $redirctionUrl;
    $obj["audioUrl"] = $newFileName;
    $obj["audioLength"] = $fileSize;
    $obj["audioType"] ="audio/mpeg";
    $obj["duration"] = $seconds;

    $fileName="audio/rss.xml";
    $xmlUrl = makeXMLFile($fileName,$obj);

    $awsXml = s3Upload($fileName, $category, $awsBucket);

    $log = "<p>making google rss $fileName</p>";
    $log .= "<p>upload $xmlUrl to $awsXml </p>";
    log2Firebase($log,"audio-processor",$audioId);


}

$siteId = 1322;
$radiomanid = time(); //date();
$modifydate = time();
$slug = "TW-DB-Test";
$title = "TW-DB-Test2";
$fieldTitle = "TW-DB-Test";

$clipperFileName = getClipperFileName($siteId, $radiomanid, $modifydate, $slug, $title, $fieldTitle);

$log = "<p>Got Clipper File Name: $clipperFileName</p>";

log2Firebase($log,"audio-processor",$audioId);

$db->getReference('audioclips/' . $audioId . '/clipperFilename')->set($clipperFileName);
