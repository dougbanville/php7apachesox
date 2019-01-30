<?php
/*
if (!headers_sent()) {
    echo "NO HEADERS";
    exit;
}*/
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value\n";
    $headers = getallheaders();
};
//  "headers" => $headers["client-security-token"]
if($headers["client-security-token"] == "yyyyyy"){
    $attrs = array("title"=>"hello", "message"=>"Hello World", "title" => "Donald Trump");
    $data = array("id"=>1,"message"=>"Hello World", "title" => "Donald Trump", "attributes"=>$attrs);
    $reponse = array($data);
    //$data = array("data" => $data);
    header('Content-Type: application/json');
    echo json_encode($reponse);
}else{
   header("HTTP/1.1 401 Unauthorized");
   exit;
}
