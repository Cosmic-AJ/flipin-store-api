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
            $data = array();
            
            $search = "SELECT category from seller where sellerid=$sid";
            $squery = pg_query($pdo, $search);
            $findcate = pg_fetch_row($squery);
            $sellercategory = $findcate[0];

            $search = "SELECT * from ProductPost where category='$sellercategory' order by createdat desc;";
            $squery = pg_query($pdo, $search);
            $nbudco = pg_num_rows($squery);
            $v=0;
            while($v<$nbudco){
                $nbud = pg_fetch_row($squery);
                $id = "P".$nbud[0];
                $name = $nbud[1];
                $img = $nbud[8];
                $category = $nbud[2];

                $datetime = new DateTime("$nbud[5]");
                $date = $datetime->format(DateTime::ATOM);
                
                $search1 = "SELECT min(price) from Bidding where productpostid=$nbud[0];";
                $squery1 = pg_query($pdo, $search1);
                $nbudco1 = pg_num_rows($squery1);
                $nbud1 = pg_fetch_row($squery1);
                if($nbud1[0]!=null)
                    $lowestBid = $nbud1[0];
                else
                    $lowestBid = "Not Available";

                $cid = $nbud[4];
                $search2 = "SELECT city,state from Customer where customerid=$cid;";
                $squery2 = pg_query($pdo, $search2);
                $nbud2 = pg_fetch_row($squery2);
                $location = $nbud2[0].", ".$nbud2[1];

                $v=$v+1;
                $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
                array_push($data,$d1);
            }

            $finaldata = array( "responseCode" => 200,
                                "products" => $data);
            
            $value = json_encode($finaldata);
            echo("$value");
        }else if($cat=="C" || $cat=='C'){
            $data = array();
            
            $search = "SELECT * from ProductPost order by createdat desc;";
            $squery = pg_query($pdo, $search);
            $nbudco = pg_num_rows($squery);
            $v=0;
            while($v<$nbudco){
                $nbud = pg_fetch_row($squery);
                $id = "P".$nbud[0];
                $name = $nbud[1];
                $img = $nbud[8];
                $category = $nbud[2];

                $datetime = new DateTime("$nbud[5]");
                $date = $datetime->format(DateTime::ATOM);
                
                $search1 = "SELECT min(price) from Bidding where productpostid=$nbud[0];";
                $squery1 = pg_query($pdo, $search1);
                $nbudco1 = pg_num_rows($squery1);
                $nbud1 = pg_fetch_row($squery1);
                if($nbud1[0]!=null)
                    $lowestBid = $nbud1[0];
                else
                    $lowestBid = "Not Available";

                $cid = $nbud[4];
                $search2 = "SELECT city,state from Customer where customerid=$cid;";
                $squery2 = pg_query($pdo, $search2);
                $nbud2 = pg_fetch_row($squery2);
                $location = $nbud2[0].", ".$nbud2[1];

                $v=$v+1;
                $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
                array_push($data,$d1);
            }

            $finaldata = array( "responseCode" => 200,
                                "products" => $data);
            
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