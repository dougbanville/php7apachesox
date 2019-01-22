<?php
/*
Grab the latest video from http://radiodj.rte.ie/media/flashbriefing/
And upload it to AWS - run the exec below doSave.php
*/
require("../functions.php");
$dotenv = new Dotenv\Dotenv('../../');
$dotenv->load();

$fileName = $_GET["fileName"];
$category = $_GET['category'];

$json = array('category'=>$category,'fileName' => $fileName);
$json = json_encode($json);
echo $_GET['callback'] . '('.$json.')';// Response that will be sent to the user

$command = "nohup php doSave.php $fileName $category >/dev/null 2>&1 &";
$log = "<p>saving video</p>";
$log .= "<code>$command</code>";
log2Firebase($log,"video","");
exec("nohup php doSave.php $category $fileName >/dev/null 2>&1 &");