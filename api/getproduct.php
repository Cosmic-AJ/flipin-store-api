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
$ppid = pg_escape_string($data['pid']);
$variable = substr($ppid, 1, strlen($ppid));
$pid = (int)$variable;

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
            //$search = "SELECT category from seller where sellerid=$sid";
            //$squery = pg_query($pdo, $search);
            //$findcate = pg_fetch_row($squery);
            //$sellercategory = $findcate[0];
            
            $itsearch = "Select productname, category, description, customerid, mediaurl, status, name from productpost natural join customer where productpostid=$pid;";
            $itquery = pg_query($pdo, $itsearch);
            $itfind = pg_fetch_row($itquery);
            $pname = $itfind[0];
            $pcategory = $itfind[1];
            $pdescription = $itfind[2];
            $pcustomerId = $itfind[3];
            $pimage = $itfind[4];
            $pstatus = $itfind[5];
            $cname = $itfind[6];
            if($pstatus=="OPEN")
                $pstatus = true;
            else
                $pstatus = false;

            $upsearch = "update productpost set postviews=postviews+1 where productpostid=$pid;";
            $upquery = pg_query($pdo, $upsearch);

            $search1 = "SELECT min(price) from Bidding where productpostid=$pid;";
            $squery1 = pg_query($pdo, $search1);
            $nbudco1 = pg_num_rows($squery1);
            $nbud1 = pg_fetch_row($squery1);
            if($nbud1[0]!=null)
                $plowestBid = $nbud1[0];
            else
                $plowestBid = "Not Available";

            $search2 = "SELECT city,state from Customer where customerid=$pcustomerId;";
            $squery2 = pg_query($pdo, $search2);
            $nbud2 = pg_fetch_row($squery2);
            $plocation = $nbud2[0].", ".$nbud2[1];


            $pbids = array();
            $bidsearch = "Select sellerid, logo, name, biddescription, price from bidding natural join seller where productpostid=$pid;";
            $bidquery = pg_query($pdo, $bidsearch);
            $bidrow = pg_num_rows($bidquery);
            $v=0;
            while($v<$bidrow){
                $nbud = pg_fetch_row($bidquery);
                $sellerId = "S".$nbud[0];
                $logo = $nbud[1];
                $sellerName = $nbud[2];
                $description2 = $nbud[3];
                $amount = $nbud[4];
                
                $d1 = array("sellerId"=>$sellerId, "logo"=>$logo, "sellerName"=>$sellerName, "description"=>$description2, "amount"=>$amount);
                array_push($pbids,$d1);
                $v++;
            }
            
            //$ser = substr($pname, 0, 1);
            //$serl = strtolower($ser);
            
            $search = "SELECT * from ProductPost where category='$pcategory' and productpostid!=$pid limit 3";
            //$search = "SELECT * from ProductPost where category='$pcategory' and (productname like '%$serl%' or productname like '%$ser%') and productpostid!=$pid";
            $squery = pg_query($pdo, $search);
            $nbudco = pg_num_rows($squery);
            $v=0;
            $psuggestions = array();
            while($v<$nbudco){
                $nbud = pg_fetch_row($squery);
                $id = "P".$nbud[0];
                $name = $nbud[1];
                $img = $nbud[8];
                
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
                $d1 = array("id"=>$id, "name"=>$name, "img"=>$img, "lowestBid"=>$lowestBid, "location"=>$location);
                array_push($psuggestions,$d1);
            }

            $finaldata = array( "responseCode" => 200,
                                "name" => $pname,
                                "lowestBid" => $plowestBid,
                                "location" => $plocation,
                                "category" => $pcategory,
                                "active" => $pstatus,
                                "description" => $pdescription,
                                "customerId" => "C".$pcustomerId,
                                "customerName" => $cname,
                                "image" => $pimage,
                                "bids" => $pbids,
                                "suggestions" => $psuggestions);
            
            $value = json_encode($finaldata);
            echo("$value");
        }else if($cat=="C" || $cat=='C'){
            $itsearch = "Select productname, category, description, customerid, mediaurl, status from productpost where productpostid=$pid;";
            $itquery = pg_query($pdo, $itsearch);
            $itfind = pg_fetch_row($itquery);
            $pname = $itfind[0];
            $pcategory = $itfind[1];
            $pdescription = $itfind[2];
            $pcustomerId = $itfind[3];
            $pimage = $itfind[4];
            $pstatus = $itfind[5];
            if($pstatus=="OPEN")
                $pstatus = true;
            else
                $pstatus = false;

            $upsearch = "update productpost set postviews=postviews+1 where productpostid=$pid;";
            $upquery = pg_query($pdo, $upsearch);

            $search1 = "SELECT min(price) from Bidding where productpostid=$pid;";
            $squery1 = pg_query($pdo, $search1);
            $nbudco1 = pg_num_rows($squery1);
            $nbud1 = pg_fetch_row($squery1);
            if($nbud1[0]!=null)
                $plowestBid = $nbud1[0];
            else
                $plowestBid = "Not Available";

            $search2 = "SELECT city,state from Customer where customerid=$pcustomerId;";
            $squery2 = pg_query($pdo, $search2);
            $nbud2 = pg_fetch_row($squery2);
            $plocation = $nbud2[0].", ".$nbud2[1];


            $pbids = array();
            $bidsearch = "Select sellerid, logo, name, biddescription, price from bidding natural join seller where productpostid=$pid;";
            $bidquery = pg_query($pdo, $bidsearch);
            $bidrow = pg_num_rows($bidquery);
            $v=0;
            while($v<$bidrow){
                $nbud = pg_fetch_row($bidquery);
                $sellerId = "S".$nbud[0];
                $logo = $nbud[1];
                $sellerName = $nbud[2];
                $description2 = $nbud[3];
                $amount = $nbud[4];
                
                $d1 = array("sellerId"=>$sellerId, "logo"=>$logo, "sellerName"=>$sellerName, "description"=>$description2, "amount"=>$amount);
                array_push($pbids,$d1);
                $v++;
            }

            $finaldata = array( "responseCode" => 200,
                                "name" => $pname,
                                "lowestBid" => $plowestBid,
                                "location" => $plocation,
                                "category" => $pcategory,
                                "active" => $pstatus,
                                "description" => $pdescription,
                                "customerId" => "C".$pcustomerId,
                                "image" => $pimage,
                                "bids" => $pbids);
            
            $value = json_encode($finaldata);
            echo("$value");
        }else{
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