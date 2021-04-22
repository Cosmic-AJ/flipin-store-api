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

/* catch data from the form using Post and encoding password*/
$email = pg_escape_string($data['email']);
$pwd = md5(pg_escape_string($data['password']));

if($email=="" || $email==" "|| !(filter_var($email, FILTER_VALIDATE_EMAIL)))
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Email");
    $value = json_encode($nbud);
    echo("$value");
}
else if($data['password']=="" || $data['password']==" ")
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Password");
    $value = json_encode($nbud);
    echo("$value");
}
else
{
    $search1 = "SELECT * from Seller where email='$email';";
    $squery1 = pg_query($pdo, $search1);
    $nbudco1 = pg_num_rows($squery1);
    $nbud1 = pg_fetch_row($squery1);
    
    $search2 = "SELECT * from Customer where email='$email';";
    $squery2 = pg_query($pdo, $search2);
    $nbudco2 = pg_num_rows($squery2);
    $nbud2 = pg_fetch_row($squery2);
    
    if($nbudco1==1){
        $nbud=$nbud1;
        $id = "S".$nbud[0];
        $isSeller = true;
        $loginpwd = $nbud[4];
        $url = $nbud[7];
        if($nbud[8]==NULL || $nbud[9]==NULL || $nbud[10]==NULL || $nbud[11]==NULL || $nbud[12]==NULL || $nbud[13]==NULL)
            $hasAddress = false;
        else
            $hasAddress = true; 
    }
    else if($nbudco2==1){
        $nbud=$nbud2;
        $id = "C".$nbud[0];
        $isSeller = false;
        $loginpwd = $nbud[4];
        $url = null;
        if($nbud[5]==NULL || $nbud[6]==NULL || $nbud[7]==NULL || $nbud[8]==NULL || $nbud[9]==NULL || $nbud[5]==NULL)
            $hasAddress = false;
        else
            $hasAddress = true; 
    }else{
        $loginpwd = NULL;
    }

    if($loginpwd!=NULL)
    {
        if(!strcmp($pwd,$loginpwd))
        {
            /* link to home page */
            $secret_key = "AY05AS30YL31AC18";
            $issuer_claim = "https://flipin-store-api.herokuapp.com/";
            $issuedat_claim = time(); 
            $expire_claim = $issuedat_claim + 2592000; // expire time in seconds
            $token = array(
                "iss" => $issuer_claim,
                "iat" => $issuedat_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "id" => $id,
                    "email" => $nbud[3],
                ));
                
            $jwt = JWT::encode($token, $secret_key, 'HS256');

            echo json_encode(
                array(
                    "responseCode"=>200,
                    "jwt" => $jwt,
                    "user"=> array("id" => $id,
                        "isSeller" => $isSeller,
                        "name" => $nbud[1],
                        "email" => $nbud[3],
                        "url" => $url,
                        "hasAddress" => $hasAddress)
                    ));
        }
        else
        {
        /* link to login page */
        $nbud = array("responseCode"=>422, "error"=>"Invalid Email or Password");
        $value = json_encode($nbud);
        echo("$value");
        }
    }
    else
    {
    /* link to login page */
    $nbud = array("responseCode"=>422, "error"=>"Invalid Email or Password");
    $value = json_encode($nbud);
    echo("$value");
    }
}
?>