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
        $idval = $decoded->data->id;
        $variable = substr($idval, 1, strlen($idval));
        $id = (int)$variable;
        $cat = substr($idval, 0, 1);
        
        $oidval = pg_escape_string($data['oid']);
        $var = substr($oidval, 1, strlen($oidval));
        $oid = (int)$var;

        $search = "select * from orderdetails where orderid=$oid";
        $query1 = pg_query($pdo, $search);
        $numrows = pg_num_rows($query1);
        
        if($numrows==1)
        {   
            $search = "select productpostid, customerid, sellerid from orderdetails natural join bidding natural join productpost where orderid=$oid";
            $query = pg_query($pdo, $search);
            $row = pg_fetch_row($query);
            $pid = $row[0];
            $ncid = $row[1];
            $nsid = $row[2];
            $flag = 0;

            if($cat=="C" || $cat=='C'){
                if($ncid == $id){
                    $cid = $id;
                    $sid = $nsid;
                    $flag = 1;
                }
            }
            else{
                if($nsid == $id){
                    $sid = $id;
                    $cid = $ncid;
                    $flag = 1;
                }
            }
            
            if($flag==1)
            {
                $find = "select name, firstlineaddress, secondlineaddress, city, state, country, pincode, productname from productpost natural join customer where productpostid=$pid;";
                $query = pg_query($pdo, $find);
                $row = pg_fetch_row($query);
                $cname = $row[0];
                $cfaddress = $row[1];
                $csaddress = $row[2];
                $ccity = $row[3];
                $cstate = $row[4];
                $ccountry = $row[5];
                $cpincode = $row[6];
                $name = $row[7];

                $find = "select price, EXTRACT(Day FRom OrderDATE), TO_CHAR(orderdate, 'Month'), EXTRACT(YEAR FRom OrderDATE) from orderdetails natural join bidding where orderid=$oid;";
                $query = pg_query($pdo, $find);
                $row = pg_fetch_row($query);
                $price = $row[0];
                $camt = ($price*5)/100;
                $tprice = $row[0] + $camt;
                $day = $row[1];
                $month = trim($row[2]);
                $year = $row[3];

                $find = "select name, firstlineaddress, secondlineaddress, city, state, country, pincode from seller where sellerid=$sid;";
                $query = pg_query($pdo, $find);
                $row = pg_fetch_row($query);
                $sname = $row[0];
                $sfaddress = $row[1];
                $ssaddress = $row[2];
                $scity = $row[3];
                $sstate = $row[4];
                $scountry = $row[5]; 
                $spincode = $row[6];

                $finaldata = array( "responseCode" => 200,
                                    "productName" => $name,
                                    "quantity" => 1,
                                    "price" => $price,
                                    "commission" => "5%",
                                    "commissionAmount" => $camt,
                                    "totalPrice" => $tprice,
                                    "date" => array( "day" => $day,
                                                     "month" => $month,
                                                     "year" => $year),
                                    "Customer" => array( "name" => $cname,
                                                         "firstLineAddress" => $cfaddress,
                                                         "secondLineAddress" => $csaddress,
                                                         "city" => $ccity,
                                                         "state" => $cstate,
                                                         "country" => $ccountry,
                                                         "pincode" => $cpincode),
                                    "Seller" => array( "name" => $sname,
                                                       "firstLineAddress" => $sfaddress,
                                                        "secondLineAddress" => $ssaddress,
                                                        "city" => $scity,
                                                        "state" => $sstate,
                                                        "country" => $scountry,
                                                        "pincode" => $spincode));

                $value = json_encode($finaldata);
                echo("$value");
            }
            else
            {
                echo json_encode(array(
                "responseCode" => 403,
                    "message" => "Access denied."));
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