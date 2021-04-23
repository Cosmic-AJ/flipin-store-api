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
        
        $pidval = pg_escape_string($data['pid']);
        $var = substr($pidval, 1, strlen($pidval));
        $pid = (int)$var;

        if($cat=="C" || $cat=='C'){
            $cid = $sid;

            $search = "Select status from productpost where productpostid=$pid and customerid=$cid";
            $query = pg_query($pdo, $search);
            $numrows = pg_num_rows($query);
            
            if($numrows==1){
                $row = pg_fetch_row($query);
                $status = $row[0];
                if($status=='OPEN'){
                    $search = "Update productpost set status='UNPUBLISHED', lastedit=CURRENT_TIMESTAMP where productpostid=$pid";
                    pg_query($pdo, $search);
                    
                    $update = "Update bidding set bidstatus='UNPUBLISHED' where productpostid=$pid;";
                    $updated = pg_query($pdo, $update);

                    $finaldata = array( "responseCode" => 204,
                                        "message" => "Product Unpublished Successfully");

                    $value = json_encode($finaldata);
                    echo("$value");
                }
                else if($status=='UNPUBLISHED'){
                    $search = "Update productpost set status='OPEN', lastedit=CURRENT_TIMESTAMP where productpostid=$pid";
                    pg_query($pdo, $search);
                    
                    $update = "Update bidding set bidstatus='OPEN' where productpostid=$pid;";
                    $updated = pg_query($pdo, $update);

                    $finaldata = array( "responseCode" => 204,
                                        "message" => "Product Published Successfully");

                    $value = json_encode($finaldata);
                    echo("$value");
                }
                else{
                    $finaldata = array( "responseCode" => 200,
                                        "message" => "Product Cannot be updated.");

                    $value = json_encode($finaldata);
                    echo("$value");
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