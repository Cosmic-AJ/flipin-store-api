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
        $cidval = $decoded->data->id;
        $variable = substr($cidval, 1, strlen($cidval));
        $cid = (int)$variable;
        $cat = substr($cidval, 0, 1);
        
        $pidval = pg_escape_string($data['pid']);
        $var = substr($pidval, 1, strlen($pidval));
        $pid = (int)$var;

        $sidval = pg_escape_string($data['sid']);
        $var = substr($sidval, 1, strlen($sidval));
        $sid = (int)$var;

        if($cat=="C" || $cat=='C'){
            $search = "Select biddingid from bidding where productpostid=$pid and sellerid=$sid";
            $query = pg_query($pdo, $search);
            $numrows = pg_num_rows($query);

            $search = "Select customerid from productpost where productpostid=$pid and status='OPEN'";
            $query1 = pg_query($pdo, $search);
            $numrows1 = pg_num_rows($query1);
            
            if($numrows==1)
            {
                if($numrows1==1)
                {
                    $row = pg_fetch_row($query1);
                    $ncid = $row[0];

                    if($cid==$ncid)
                    {
                        $row = pg_fetch_row($query);
                        $bid = $row[0];
                        
                        $insert = "Insert into orderdetails(biddingid, orderstatus, crating, srating) values($bid, 'STARTED', NULL, NULL)";
                        pg_query($pdo, $insert);

                        $search = "Update productpost set status='CLOSED' where productpostid=$pid";
                        pg_query($pdo, $search);
                            
                        $update = "Update bidding set bidstatus='CLOSED' where productpostid=$pid;";
                        $updated = pg_query($pdo, $update);
                            
                        $finaldata = array( "responseCode" => 201,
                                            "message" => "Order Created Successfully");

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
                        "responseCode" => 400,
                        "message" => "Order already created or product set as Unpublished."
                    ));
                }
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