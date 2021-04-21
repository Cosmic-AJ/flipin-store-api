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
            $nlsearch = "Select logo from seller where sellerid=$sid;";
            $nlquery = pg_query($pdo, $nlsearch);
            $nlfind = pg_fetch_row($nlquery);
            $logo = $nlfind[0];
            
            $tbsearch = "select * from bidding where sellerid=$sid;";
            $tbquery = pg_query($pdo, $tbsearch);
            $tb = pg_num_rows($tbquery);
            
            $tocsearch = "Select * from orderdetails natural join bidding where sellerid=$sid and orderstatus='COMPLETE';";
            $tocquery = pg_query($pdo, $tocsearch);
            $toc = pg_num_rows($tocquery);

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

            $rsearch = "Select round(avg(srating),1) from orderdetails natural join bidding where sellerid=$sid and orderstatus='COMPLETE';";
            $rquery = pg_query($pdo, $rsearch);
            $rfind = pg_fetch_row($rquery);
            if($rfind[0]==NULL)
                $r = 5.0;
            else    
                $r = $rfind[0];

            $bid = array();
            $bidsearch = "Select productpost.productpostid, mediaurl, productname, price, bidstatus from bidding join productpost on bidding.productpostid = productpost.productpostid where sellerid=$sid;";
            $bidquery = pg_query($pdo, $bidsearch);
            $bidrow = pg_num_rows($bidquery);
            $v=0;
            while($v<$bidrow){
                $nbud = pg_fetch_row($bidquery);
                $id = "P".$nbud[0];
                $url = $nbud[1];
                $name = $nbud[2];
                $ybid = $nbud[3];
                $status = $nbud[4];

                $search1 = "SELECT min(price) from Bidding where productpostid=$nbud[0];";
                $squery1 = pg_query($pdo, $search1);
                $nbud1 = pg_fetch_row($squery1);
                if($nbud1[0]==NULL)
                    $lowestBid = 0;
                else    
                    $lowestBid = $nbud1[0];
                
                $d1 = array("id"=>$id, "src"=>$url, "pName"=>$name, "lBid"=>$lowestBid, "yBid"=>$ybid, "status"=>$status);
                array_push($bid,$d1);
                $v++;
            }

            $finaldata = array( "responseCode" => 200,
                                "src" => $logo,
                                "summary"=> array("s1" => array("key" => "Total Bids",
                                                                "value" => $tb),
                                                "s2"=> array("key" => "Completed Orders",
                                                            "value" => $toc),
                                                "s3" => array("key" => "Ongoing Orders",
                                                            "value" => $ao)),
                                "e" => $te, 
                                "rating" => $r, 
                                "itemsArray" => $bid);
            
            $value = json_encode($finaldata);
            echo("$value");
        }else if($cat=="C" || $cat=='C'){
            $cid = $sid;
            
            $tpsearch = "Select * from productpost where customerid=$cid;";
            $tpquery = pg_query($pdo, $tpsearch);
            $tp = pg_num_rows($tpquery);
            
            $tvsearch = "Select sum(postviews) from productpost where customerid=$cid;";
            $tvquery = pg_query($pdo, $tvsearch);
            $tvfind = pg_fetch_row($tvquery);
            if($tvfind[0]==NULL)
                $tv = 0;
            else    
                $tv = $tvfind[0];

            $obsearch = "Select * from productpost natural join bidding where customerid=$cid and bidstatus='OPEN';";
            $obquery = pg_query($pdo, $obsearch);
            $ob = pg_num_rows($obquery);

            $tesearch = "Select sum(price) from orderdetails natural join bidding natural join productpost where customerid=$cid and orderstatus='COMPLETE';";
            $tequery = pg_query($pdo, $tesearch);
            $tefind = pg_fetch_row($tequery);
            if($tefind[0]==NULL)
                $te = 0;
            else    
                $te = $tefind[0];

            $rsearch = "Select round(avg(crating),1) from orderdetails natural join bidding natural join productpost where customerid=$cid and orderstatus='COMPLETE';";
            $rquery = pg_query($pdo, $rsearch);
            $rfind = pg_fetch_row($rquery);
            if($rfind[0]==NULL)
                $r = 5.0;
            else    
                $r = $rfind[0];

            $bid = array();
            $bidsearch = "Select distinct productpost.productpostid, mediaurl, productname,postviews from productpost left outer join bidding on productpost.productpostid = bidding.productpostid where customerid=$cid;";
            $bidquery = pg_query($pdo, $bidsearch);
            $bidrow = pg_num_rows($bidquery);
            $v=0;
            while($v<$bidrow){
                $nbud = pg_fetch_row($bidquery);
                $id = "P".$nbud[0];
                $url = $nbud[1];
                $name = $nbud[2];
                $pviews = $nbud[3];

                $search1 = "SELECT min(price) from Bidding where productpostid=$nbud[0];";
                $squery1 = pg_query($pdo, $search1);
                $nbud1 = pg_fetch_row($squery1);
                if($nbud1[0]==NULL)
                    $lowestBid = 0;
                else    
                    $lowestBid = $nbud1[0];
                    

                $search1 = "SELECT count(*) from Bidding where productpostid=$nbud[0];";
                $squery1 = pg_query($pdo, $search1);
                $nbud1 = pg_fetch_row($squery1);
                if($nbud1[0]==NULL)
                    $tbids = 0;
                else    
                    $tbids = $nbud1[0];
                
                $d1 = array("id"=>$id, "src"=>$url, "pName"=>$name, "lBid"=>$lowestBid, "pViews"=>$pviews, "tBids"=>$tbids);
                array_push($bid,$d1);
                $v++;
            }

            $finaldata = array( "responseCode" => 200,
                                "summary"=> array("s1" => array("key" => "Total Product Listing",
                                                                "value" => $tp),
                                                "s2"=> array("key" => "Total Views",
                                                            "value" => $tv),
                                                "s3" => array("key" => "Ongoing Bids",
                                                            "value" => $ob)),
                                "e" => $te, 
                                "rating" => $r, 
                                "itemsArray" => $bid);

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