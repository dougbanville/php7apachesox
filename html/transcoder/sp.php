<?php
require '../vendor/autoload.php';
$dotenv = new Dotenv\Dotenv('../../');
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
$dotenv->load();
echo __DIR__;
echo getenv("version");
$serviceAccount = ServiceAccount::fromJsonFile('../firebase.json');
$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->create();

$db = $firebase->getDatabase();
$audioClip = $db->getReference('audioclips')
    ->getValue();

print_r($audioClip);
echo "hello";

echo getenv("version");
/*
$obj = array("title"=>"RTÉ News");

$obj["title"]  = "RTÉ News";
$obj["url"] ="https://www.rte.ie";
$obj["description"] ="descript";
$obj["language"] ="en-ie";
$obj["category"] ="News &amp; Politics";
$obj["copyrigh"] = date("Y")." RTÉ";
$obj["ttl"] = 60;
$obj["imageUrl"] ="https://s3-eu-west-1.amazonaws.com/radiodj-alexa-live/english-news/RTE_News_Square_1600.png";
$obj["imageLink"]  ="https://dffsd";
$obj["audioUrl"] ="https://radiodj-alexa-live.s3.amazonaws.com/english-news/audio.mp3";
$obj["guid"] ="1234567";
$obj["pubDate"] =gmdate("D, d M Y H:i:s +0000");
$obj["author"] ="doug";
$obj["link"] ="link";
$obj["audioUrl"] ="hjkhkjh";
$obj["audioLength"] =8787887987;
$obj["audioType"] ="audio/mpeg";
$obj["duration"] =120;

$fileName="audio/audio.xml";
 */

//Header('Content-type: text/xml');

//echo s3Upload("audio/xml.xml", "testpath", getenv('bucketName'),false);

//echo quicks3Upload("audio/audio.xml", getenv('bucketName'));

/*
$date1=date_create("2013-03-15");
$date2=date_create("2013-12-12");
$diff=date_diff($date1,$date2);

echo $diff->d;
 */
