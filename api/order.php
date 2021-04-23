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

try{
    $decoded = JWT::decode($jwt, $secret_key, ['HS256']);
    $now = new DateTimeImmutable();

    if($jwt!=NULL && $decoded->iss=="https://flipin-store-api.herokuapp.com/" && $decoded->exp > $now->getTimestamp()){
        $sidval = $decoded->data->id;
        $variable = substr($sidval, 1, strlen($sidval));
        $sid = (int)$variable;
        $cat = substr($sidval, 0, 1);

        if($cat=="S" || $cat=='S'){
            $tocsearch = "Select * from orderdetails natural join bidding where sellerid=$sid and orderstatus='COMPLETE';";
            $tocquery = pg_query($pdo, $tocsearch);
            $toc = pg_num_rows($tocquery);

            $tbsearch = "Select * from bidding where sellerid=$sid;";
            $tbquery = pg_query($pdo, $tbsearch);
            $tb = pg_num_rows($tbquery);

            $absearch = "Select * from bidding where sellerid=$sid AND bidstatus='OPEN';";
            $abquery = pg_query($pdo, $absearch);
            $ab = pg_num_rows($abquery);

            $aosearch = "Select * from orderdetails natural join bidding where sellerid=$sid and orderstatus!='COMPLETE';";
            $aoquery = pg_query($pdo, $aosearch);
            $ao = pg_num_rows($aoquery);

            $tesearch = "Select sum(price) from orderdetails natural join bidding where sellerid=$sid and orderstatus='COMPLETE';";
            $tequery = pg_query($pdo, $tesearch);
            $tefind = pg_fetch_row($tequery);
            if($tefind[0]==NULL)
                $te = 0;
            else    
                $te = $tefind[0];

            $order = array();
            $ordersearch = "Select orderid, name, productname, price, orderstatus from orderdetails natural join bidding natural join productpost natural join customer where sellerid=$sid;";
            $orderquery = pg_query($pdo, $ordersearch);
            $orderrow = pg_num_rows($orderquery);
            $v=0;
            while($v<$orderrow){
                $nbud = pg_fetch_row($orderquery);
                $id = "O".$nbud[0];
                $name = $nbud[1];
                $pname = $nbud[2];
                $price = $nbud[3];
                $status = $nbud[4];
                
                $d1 = array("oid"=>$id, "src" => null, "name"=>$name, "productName"=>$pname, "price"=>$price, "status"=>$status);
                array_push($order,$d1);
                $v++;
            }

            $finaldata = array( "responseCode" => 200,
                                "summary"=> array(
                                    "s1" => array("key" => "Total Orders Completed",
                                                    "value" => $toc),
                                    "s2" => array("key" => "Total Bids",
                                                    "value" => $tb),
                                    "s3" => array("key" => "Active Bids",
                                                    "value" => $ab),
                                    "s4" => array("key" => "Active Orders",
                                                    "value" => $ao),
                                    "s5" => array("key" => "Total Earnings",
                                                    "value" => $te)),
                                "orders" => $order);

            $value = json_encode($finaldata);
            echo("$value");
        }else if($cat=="C" || $cat=='C'){
            $cid = $sid;

            $tpfsearch = "select * from orderdetails natural join bidding natural join productpost where customerid=$cid and orderstatus='COMPLETE';";
            $tpfquery = pg_query($pdo, $tpfsearch);
            $tpf = pg_num_rows($tpfquery);

            $tbsearch = "Select * from productpost natural join bidding where customerid=$cid;";
            $tbquery = pg_query($pdo, $tbsearch);
            $tb = pg_num_rows($tbquery);

            $alsearch = "select * from productpost where customerid=$cid and status='OPEN';";
            $alquery = pg_query($pdo, $alsearch);
            $al = pg_num_rows($alquery);

            $aosearch = "select * from orderdetails natural join bidding natural join productpost where customerid=$cid and orderstatus!='COMPLETE';";
            $aoquery = pg_query($pdo, $aosearch);
            $ao = pg_num_rows($aoquery);

            $tesearch = "Select sum(price) from orderdetails natural join bidding natural join productpost where customerid=$cid and orderstatus='COMPLETE';";
            $tequery = pg_query($pdo, $tesearch);
            $tefind = pg_fetch_row($tequery);
            if($tefind[0]==NULL)
                $te = 0;
            else    
                $te = $tefind[0];

            $order = array();
            $ordersearch = "select orderid, logo, name, productname, price, orderstatus from orderdetails natural join bidding natural join productpost join seller on bidding.sellerid = seller.sellerid where customerid=$cid;";
            $orderquery = pg_query($pdo, $ordersearch);
            $orderrow = pg_num_rows($orderquery);
            $v=0;
            while($v<$orderrow){
                $nbud = pg_fetch_row($orderquery);
                $id = "O".$nbud[0];
                $src = $nbud[1];
                $name = $nbud[2];
                $pname = $nbud[3];
                $price = $nbud[4];
                $status = $nbud[5];
                
                $d1 = array("oid"=>$id, "src" => $src, "name"=>$name, "productName"=>$pname, "price"=>$price, "status"=>$status);
                array_push($order,$d1);
                $v++;
            }

            $finaldata = array( "responseCode" => 200,
                                "summary"=> array(
                                    "s1" => array("key" => "Total Products Fulfilled",
                                                    "value" => $tpf),
                                    "s2" => array("key" => "Total Bids",
                                                    "value" => $tb),
                                    "s3" => array("key" => "Active Listings",
                                                    "value" => $al),
                                    "s4" => array("key" => "Active Orders",
                                                    "value" => $ao),
                                    "s5" => array("key" => "Total Expenditure",
                                                    "value" => $te)),
                                "orders" => $order);

            $value = json_encode($finaldata);
            echo("$value");
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