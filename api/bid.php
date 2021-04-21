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

try {
    $decoded = JWT::decode($jwt, $secret_key, ['HS256']);
    $now = new DateTimeImmutable();

    if($jwt!=NULL && $decoded->iss=="https://flipin-store-api.herokuapp.com/" && $decoded->exp > $now->getTimestamp()){
    
        $sidval = $decoded->data->id;
        $variable = substr($sidval, 1, strlen($sidval));
        $sid = (int)$variable;
        $cat = substr($sidval, 0, 1);

        if($cat=="S" || $cat=='S'){
            $ppid = pg_escape_string($data['pid']);
            $desc = pg_escape_string($data['desc']);
            $price = pg_escape_string($data['price']);
            $variable = substr($ppid, 1, strlen($ppid));
            $pid = (int)$variable;

            if(trim($price)==""){
                $nbud = array("responseCode"=>422, "error"=>"Invalid Price");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($desc)=="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Description");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($pid)=="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Product Id");
                $value = json_encode($nbud);
                echo("$value");
            }
            else{
                $search = "select * from bidding where sellerid=$sid and productpostid=$pid";
                $query = pg_query($pdo, $search);
                $nbud = pg_num_rows($query);
                $price = (double) $price;
                if($nbud==1)
                {
                    $search = "update bidding set price=$price, biddescription='$desc', last_edit=CURRENT_TIMESTAMP where sellerid=$sid and productpostid=$pid;";
                    $query = pg_query($pdo, $search);
                
                    $finaldata = array( "responseCode" => 204,
                                        "message" => "Bid Updated Successfully");
                    $value = json_encode($finaldata);
                    echo("$value"); 
                }else if($nbud==0){
                    $search = "insert into bidding(sellerid, productpostid, price, biddescription, bidstatus) values($sid,$pid,$price,'$desc','OPEN');";
                    $query = pg_query($pdo, $search);
                
                    $finaldata = array( "responseCode" => 201,
                                        "message" => "Bid Added Successfully");
                    $value = json_encode($finaldata);
                    echo("$value"); 
                }else{
                    echo json_encode(array(
                        "responseCode" => 403,
                        "message" => "Error"
                    ));
                }
            }
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