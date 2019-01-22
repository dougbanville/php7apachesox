<?php
require '../vendor/autoload.php';
$dotenv = new Dotenv\Dotenv('../../');
$dotenv->load();
use phpseclib\Net\SFTP;

$sftp = new SFTP('radiodj.rte.ie');
$user = getenv('sftp_user');
$password = getenv('sftp_password');
$location = getenv('sftp_location');
///var/www/django/radiodj_live/media/flashbriefing
if (!$sftp->login($user, $password)) {
    throw new Exception('Login failed');
}
$sftp->chdir($location);
$files = $sftp->rawlist();
$results = array();
$i = -1;
foreach ($files as $key => $value) {
    $fileInfo = pathinfo($key);
    if (isset($fileInfo["extension"])) {
        $ext = $fileInfo["extension"];
    }

    if ($ext === "mp4") {
        
        $dateCreated = new DateTime();
        $dateCreated->setTimestamp($value["mtime"]);
        
        $today = new DateTime();

        $diff = $today->diff($dateCreated);
        $diff = $diff->format('%d');

        if($diff < 1){
            $i ++;
            //array_push($results,$key,$dateCreated->format("Y-m-d"));
            $results[$i]["file"] = $key;
            $results[$i]["created"] = $dateCreated->format("Y-m-d H:i");
        }
    }
}
header('Content-Type: application/json');
echo $_GET['callback']."(".json_encode($results).")";