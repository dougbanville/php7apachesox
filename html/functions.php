<?php
require 'vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__.'/../');
$dotenv->load();
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\S3\S3Client;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

function saveFileFromUrl($url,$fileName)
{
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_URL, $url);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);

    $myfile = fopen($fileName, "w") or die("Unable to open file!");
    fwrite($myfile, $output);
    fclose($myfile);
    
    curl_close($ch);
}

function quicks3Upload($file, $bucketName){

    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => 'eu-west-1',
        'credentials' => [
            'key' => getenv('accessKey'),
            'secret' => getenv('secret'),
        ],
    ]);
    $result = $s3Client->putObject(array(
        'Bucket' => $bucketName,
        'Key' => $file,
        'SourceFile' => $file,
        'ACL' => 'public-read',
    ));


    // We can poll the object until it is accessible
    $s3Client->waitUntil('ObjectExists', array(
        'Bucket' => $bucketName,
        'Key' => $file,
    ));

    if (unlink($file)) {
        //echo "Deleted File";
    } else {
        echo "Couldn't delete temp file";
    }
    return "https://s3-eu-west-1.amazonaws.com/" . $bucketName . "/" .$file;

}

function s3Upload($file, $path, $bucketName, $rename=false)
{
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    
    if($rename){
        $newFileName = $path."/".date("ymdhis") . "." . $ext;
    }else{
        $newFileName = $path."/".basename($file);
    }
    
    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => 'eu-west-1',
        'credentials' => [
            'key' => getenv('accessKey'),
            'secret' => getenv('secret'),
        ],
    ]);
    $result = $s3Client->putObject(array(
        'Bucket' => $bucketName,
        'Key' => $newFileName,
        'SourceFile' => $file,
        'ACL' => 'public-read',
    ));

    // We can poll the object until it is accessible
    $s3Client->waitUntil('ObjectExists', array(
        'Bucket' => $bucketName,
        'Key' => $newFileName,
    ));

    if (unlink($file)) {
        //echo "Deleted File";
    } else {
        //echo "Couldn't delete temp file";
    }
    return "https://s3-eu-west-1.amazonaws.com/" . $bucketName . "/" . $newFileName;
}

function uploadFlashBriefingJson($bucketName,$category){

    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => 'eu-west-1',
        'ACL'    => 'public-read',
        'credentials' => [
            'key' => getenv('accessKey'),
            'secret' => getenv('secret'),
        ],
    ]);

    $s3Client->registerStreamWrapper();

    $stream = fopen('s3://'.$bucketName.'/json/'.$category.'.json', 'w');
    fwrite($stream, 'Hello!');
    fclose($stream);

}

function getClipperFileName($siteId, $radiomanid, $modifydate, $slug, $title, $fieldTitle)
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://clipper-next.rte.ie/DistributedServices/Publishing.svc/pox/RegisteriNews?siteId=$siteId&radiomanid=$radiomanid&modifydate=$modifydate&slug=$slug&title=$title&fieldTitle=$fieldTitle&description=",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Postman-Token: 12219352-aed0-4ab8-8cc4-6a8a86db4187",
            "cache-control: no-cache",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        die("clipper failed cURL Error #:" . $err);
    } else {
        $xml = simplexml_load_string($response) or die("Error: Cannot create object");
        $fileName = $xml[0];
        $fileName = str_replace("Successful ", "", $fileName);
        return "inews_".$modifydate.$fileName;
    }
    /*
    Where:
    modifydate = Math.round((new Date()).getTime() / 1000); 
    RadiomanID = Unique integer - try use modifydate above
    siteId = 1322 (test changes)
    slug / title / fieldTitle all the same - (mi-/nw1-/tw-)ClipNameWithNoSpaces

    Responce
    "Successful 10969970_21471137_10969971_JD-Test2"
    Save file with this name, using modified date above and the 3 ids from the response:

    inews_1543481759_10969970_21471137_10969971_1_twjdtest2_a_1543481759_rte54fminews.mp2
    */

    //10967858_21469746_10969147_DB-Test
};

function log2Firebase($log,$type,$logRef)
{
    $serviceAccount = ServiceAccount::fromJsonFile('../../firebase.json');
    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri('https://radio-a8e0f.firebaseio.com')
        ->create();

    $db = $firebase->getDatabase();
    $logData = [
        'log' => $log,
        'type' => $type,
        'time' => date('Y-m-d H:i:s'),
        'audioId' => $type
    ];
    $db->getReference('logs')->push($logData);
}

function makeXMLFile($fileName,$obj){
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" />'); 

    $channel = $xml->addChild('channel'); 
    $channel->addChild('title',$obj["title"]); 
    $channel->addChild('link',$obj["url"]);
    $channel->addChild('title',$obj["title"]);
    $channel->addChild('description',$obj["description"]);
    $channel->addChild('language','en-ie');
    $channel->addChild('category',$obj["category"]);
    $channel->addChild('copyright',date("Y")." RTÉ");
    $channel->addChild('lastBuildDate',gmdate("D, d M Y H:i:s +0000"));
    $channel->addChild('ttl',60);
    $image = $channel->addChild('image');
    $image->addChild('url',$obj["imageUrl"]);
    $image->addChild('link',$obj["imageLink"]);
    $item = $channel->addChild('item');
    $item->addChild('title',$obj["title"]);
    $item->addChild('link',$obj["link"]);
    $item->addChild('description',$obj["description"]);
    $item->addChild('author',"info@rte.ie (RTÉ)");
    $item->addChild('pubDate',gmdate("D, d M Y H:i:s +0000"));
    $item->addChild('guid',$obj["guid"]);
    $item->addChild('ttl',60);
    $enclosure = $item->addChild('enclosure');
    $enclosure->addAttribute('url',$obj["audioUrl"]);
    $enclosure->addAttribute('length',$obj["audioLength"]);
    $enclosure->addAttribute('type',$obj["audioType"]);
    $item->addChild('category',$obj["category"]);
    //$item->addChild('itunes:duration',$obj["duration"]);
    $item->addChild('itunes:duration',$obj["duration"],$obj["duration"]);

    $myfile = fopen($fileName, "w") or die("Unable to open file!");
    fwrite($myfile, $xml->asXML());
    fclose($myfile);
    //print($xml->asXML());
    //$url = quicks3Upload($myfile, $bucketName);
    return $fileName;
}

?>