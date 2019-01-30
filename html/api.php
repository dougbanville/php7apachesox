<?php
$data = array("id"=>1,"message"=>"hello");
header('Content-Type: application/json');
echo json_encode($data);