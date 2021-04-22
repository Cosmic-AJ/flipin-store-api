<?php
session_start();
require "vendor/autoload.php";
use \Firebase\JWT\JWT;

include ('config/db.php');

header("HTTP/1.1 200 OK");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');

pg_query($pdo, "set timezone='posix/Asia/Kolkata'");

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$arr = explode(" ", $authHeader);
$secret_key = "AY05AS30YL31AC18";
$jwt=$arr[1];

try {
    $decoded = JWT::decode($jwt, $secret_key, ['HS256']);
    $now = new DateTimeImmutable();

    if($jwt!=NULL && $decoded->iss=="https://flipin-store-api.herokuapp.com/" && $decoded->exp > $now->getTimestamp()){
    
        $sidval = $decoded->data->id;
        $variable = substr($sidval, 1, strlen($sidval));
        $sid = (int)$variable;
        $cat = substr($sidval, 0, 1);

        if($cat=="S" || $cat=='S'){
            $search = "select * from seller where sellerid=$sid;";
            $query = pg_query($pdo, $search);
            $find = pg_fetch_row($query);
            $d1 = array("name"=>$find[1], "phoneNumber"=>$find[2], "email"=>$find[3], "premiumMember"=>$find[5], "category"=>$find[6], "logo"=>$find[7],"firstLineAddress"=>$find[8], "secondLineAddress"=>$find[9], "city"=>$find[10], "state"=>$find[11], "country"=>$find[12], "pincode"=>$find[13]);
            
            $finaldata = array( "responseCode" => 200,
                                "user" => $d1);
            $value = json_encode($finaldata);
            echo("$value");

        }else if($cat=="C" || $cat=='C'){
            $cid = $sid;
            
            $search = "select * from customer where customerid=$cid;";
            $query = pg_query($pdo, $search);
            $find = pg_fetch_row($query);
            $d1 = array("name"=>$find[1], "phoneNumber"=>$find[2], "email"=>$find[3], "firstLineAddress"=>$find[5], "secondLineAddress"=>$find[6], "city"=>$find[7], "state"=>$find[8], "country"=>$find[9], "pincode"=>$find[10]);
            
            $finaldata = array( "responseCode" => 200,
                                "user" => $d1);
            $value = json_encode($finaldata);
            echo("$value");
        } else{
            echo json_encode(array(
                "responseCode" => 403,
                "message" => "Access denied."
            ));
        }
    }else{
        echo json_encode(array(
            "responseCode" => 403,
            "message" => "Access denied."
        ));
    }
}catch (Exception $e){
    echo json_encode(array(
        "responseCode" => 403,
        "message" => "Access denied."
    ));
}
?>