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
        $cid = (int)$variable;
        $cat = substr($sidval, 0, 1);

        if($cat=="C" || $cat=='C'){
            $name = pg_escape_string($data['name']);
            $desc = pg_escape_string($data['description']);
            $category = pg_escape_string($data['category']);
            $url = pg_escape_string($data['mediaUrl']);
            $ppid = pg_escape_string($data['pid']);
            $variable = substr($ppid, 1, strlen($ppid));
            $pid = (int)$variable;

            if(trim($name)==""){
                $nbud = array("responseCode"=>422, "error"=>"Invalid Item Name");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($desc)=="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Description");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($category)=="" || ($category!="Furniture" && $category!="Jewellery" && $category!="Cosmetics" && $category!="Footwear" && $category!="Clothing" && $category!="Accessories"))
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Category");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($url)=="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Media Url");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($pid)=="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Product ID");
                $value = json_encode($nbud);
                echo("$value");
            }
            else{          
                $search = "select * from productpost where productpostid=$pid";
                $query = pg_query($pdo, $search);
                $nbud = pg_num_rows($query);
                
                if($nbud==1)
                {
                    $search = "Update productpost set productname='$name', category='$category', description='$desc',mediaurl='$url', lastedit=CURRENT_TIMESTAMP where productpostid=$pid;";
                    $query = pg_query($pdo, $search);
                
                    $finaldata = array( "responseCode" => 204,
                                        "message" => "Product Updated Successfully");
                    $value = json_encode($finaldata);
                    echo("$value");
                }else{
                    echo json_encode(array(
                        "responseCode" => 422,
                        "message" => "Product doesn't exist"
                    ));
                }
            }
        }else{
            echo json_encode(array(
                "responseCode" => 403,
                "message" => "Access Denied."
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