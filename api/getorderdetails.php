<?php
session_start();
require "vendor/autoload.php";
use \Firebase\JWT\JWT;

include ('config/db.php');

header("HTTP/1.1 200 OK");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');

$data = json_decode(file_get_contents('php://input'), true);

pg_query($pdo, "set timezone='posix/Asia/Kolkata'");

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$arr = explode(" ", $authHeader);
$secret_key = "AY05AS30YL31AC18";
$jwt=$arr[1];

try{
    $decoded = JWT::decode($jwt, $secret_key, ['HS256']);
    $now = new DateTimeImmutable();

    if($jwt!=NULL && $decoded->iss=="https://flipin-store-api.herokuapp.com/" && $decoded->exp > $now->getTimestamp()){
        $sidval = $decoded->data->id;
        $variable = substr($sidval, 1, strlen($sidval));
        $sid = (int)$variable;
        $cat = substr($sidval, 0, 1);
        
        $oidval = pg_escape_string($data['oid']);
        $var = substr($oidval, 1, strlen($oidval));
        $oid = (int)$var;

        if($cat=="S" || $cat=='S'){
            $search = "Select orderid, sellerid from orderdetails natural join bidding where sellerid=$sid and orderid=$oid;";
            $query = pg_query($pdo, $search);
            $numrows = pg_num_rows($query);

            if($numrows==1){
                $osearch = "Select productname, mediaurl, price, orderstatus, city, state, description, name from orderdetails natural join bidding natural join productpost natural join customer where sellerid=$sid and orderid=$oid;";
                $oquery = pg_query($pdo, $osearch);
                $od = pg_fetch_row($oquery);
                $name = $od[0];
                $url = $od[1];
                $price = $od[2];
                $status = $od[3];
                $location = $od[4].", ".$od[5];
                $desc = $od[6];
                $customerName = $od[7];

                $finaldata = array( "responseCode" => 200,
                                    "name" => $name,
                                    "mediaUrl" => $url,
                                    "price" => $price,
                                    "status" => $status,
                                    "location" => $location,
                                    "personType" => "Customer Name",
                                    "personName" => $customerName,
                                    "description" => $desc);

                $value = json_encode($finaldata);
                echo("$value");
            }
            else{
                echo json_encode(array(
                    "responseCode" => 403,
                    "message" => "Access denied."
                ));
            }
        }else if($cat=="C" || $cat=='C'){
            $cid = $sid;

            $search = "Select orderid, customerid from orderdetails natural join bidding natural join productpost natural join customer where customerid=$cid and orderid=$oid;";
            $query = pg_query($pdo, $search);
            $numrows = pg_num_rows($query);
            
            if($numrows==1){
                $osearch = "Select productname, mediaurl, price, orderstatus, city, state, description, sellerid from orderdetails natural join bidding natural join productpost natural join customer where customerid=$cid and orderid=$oid;";
                $oquery = pg_query($pdo, $osearch);
                $od = pg_fetch_row($oquery);
                $name = $od[0];
                $url = $od[1];
                $price = $od[2];
                $status = $od[3];
                $location = $od[4].", ".$od[5];
                $desc = $od[6];
                $sid = $od[7];

                $ssearch = "select name from seller where sellerid=$sid;";
                $squery = pg_query($pdo, $ssearch);
                $sd = pg_fetch_row($squery);
                $sellerName = $sd[0];

                $finaldata = array( "responseCode" => 200,
                                    "name" => $name,
                                    "mediaUrl" => $url,
                                    "price" => $price,
                                    "status" => $status,
                                    "location" => $location,
                                    "personType" => "Seller Name",
                                    "personName" => $sellerName,
                                    "description" => $desc);

                $value = json_encode($finaldata);
                echo("$value");
            }
            else{
                echo json_encode(array(
                    "responseCode" => 403,
                    "message" => "Access denied."
                ));
            }
        }
        else{
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