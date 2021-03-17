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

$data = json_decode(file_get_contents('php://input'), true);

pg_query($pdo, "set timezone='posix/Asia/Kolkata'");

$data = array();

$search = "SELECT * from ProductPost where category='Accessories';";
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
    if($nbudco1==1)
        $lowestBid = $nbud1[0];
    else
        $lowestBid = "Not Available";

    $cid = $nbud[4];
    $search2 = "SELECT city,state from Customer where customerid=$cid;";
    $squery2 = pg_query($pdo, $search2);
    $nbud2 = pg_fetch_row($squery2);
    $location = $nbud2[0].", ".$nbud2[1];

    if($v==3){
        break;
    }

    $v=$v+1;
    $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
    array_push($data,$d1);
}

$search = "SELECT * from ProductPost where category='Furniture';";
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
    if($nbudco1==1)
        $lowestBid = $nbud1[0];
    else
        $lowestBid = "Not Available";

    $cid = $nbud[4];
    $search2 = "SELECT city,state from Customer where customerid=$cid;";
    $squery2 = pg_query($pdo, $search2);
    $nbud2 = pg_fetch_row($squery2);
    $location = $nbud2[0].", ".$nbud2[1];

    if($v==3){
        break;
    }

    $v=$v+1;
    $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
    array_push($data,$d1);
}

$search = "SELECT * from ProductPost where category='Cosmetics';";
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
    if($nbudco1==1)
        $lowestBid = $nbud1[0];
    else
        $lowestBid = "Not Available";

    $cid = $nbud[4];
    $search2 = "SELECT city,state from Customer where customerid=$cid;";
    $squery2 = pg_query($pdo, $search2);
    $nbud2 = pg_fetch_row($squery2);
    $location = $nbud2[0].", ".$nbud2[1];

    if($v==3){
        break;
    }

    $v=$v+1;
    $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
    array_push($data,$d1);
}

$search = "SELECT * from ProductPost where category='Footwear';";
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
    if($nbudco1==1)
        $lowestBid = $nbud1[0];
    else
        $lowestBid = "Not Available";

    $cid = $nbud[4];
    $search2 = "SELECT city,state from Customer where customerid=$cid;";
    $squery2 = pg_query($pdo, $search2);
    $nbud2 = pg_fetch_row($squery2);
    $location = $nbud2[0].", ".$nbud2[1];

    if($v==3){
        break;
    }

    $v=$v+1;
    $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
    array_push($data,$d1);
}

$search = "SELECT * from ProductPost where category='Jewellary';";
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
    if($nbudco1==1)
        $lowestBid = $nbud1[0];
    else
        $lowestBid = "Not Available";

    $cid = $nbud[4];
    $search2 = "SELECT city,state from Customer where customerid=$cid;";
    $squery2 = pg_query($pdo, $search2);
    $nbud2 = pg_fetch_row($squery2);
    $location = $nbud2[0].", ".$nbud2[1];

    if($v==3){
        break;
    }

    $v=$v+1;
    $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
    array_push($data,$d1);
}

$search = "SELECT * from ProductPost where category='Clothing';";
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
    if($nbudco1==1)
        $lowestBid = $nbud1[0];
    else
        $lowestBid = "Not Available";

    $cid = $nbud[4];
    $search2 = "SELECT city,state from Customer where customerid=$cid;";
    $squery2 = pg_query($pdo, $search2);
    $nbud2 = pg_fetch_row($squery2);
    $location = $nbud2[0].", ".$nbud2[1];

    if($v==3){
        break;
    }

    $v=$v+1;
    $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "category"=>$category, "location"=>$location, "Date"=>$date);
    array_push($data,$d1);
}

$value = json_encode($data);
echo("$value");
?>