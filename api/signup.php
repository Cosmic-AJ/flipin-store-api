<?php
session_start();
require "vendor/autoload.php";
use \Firebase\JWT\JWT;

include ('config/db.php');

header("HTTP/1.1 200 OK");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-Wit

$data = json_decode(file_get_contents('php://input'), true);

/* catch data from the form using Post and encoding password*/
$isSeller = $data['isSeller'];
$name = pg_escape_string($data['name']);
$email = pg_escape_string($data['email']);
$phone = pg_escape_string($data['phone']);
$pwd = md5(pg_escape_string($data['password']));

if($name=="" || $name==" " || !(preg_match('/^[a-zA-Z ]*$/', $name)))
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Name");
    $value = json_encode($nbud);
    echo("$value");
}
else if($email=="" || $email==" "|| !(filter_var($email, FILTER_VALIDATE_EMAIL)))
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Email");
    $value = json_encode($nbud);
    echo("$value");
}
else if($phone=="" || $phone==" "|| !(preg_match('/^[0-9 ]{10,12}$/', $phone)))
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Phone Number");
    $value = json_encode($nbud);
    echo("$value");
}
else if($data['password']=="" || $data['password']==" ")
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Password");
    $value = json_encode($nbud);
    echo("$value");
}
else if(gettype($isSeller)!='boolean')
{
    $nbud = array("responseCode"=>422, "error"=>"Invalid Customer and Seller Selection");
    $value = json_encode($nbud);
    echo("$value");
}
else
{
    $search = "SELECT * from Seller where email='$email';";
    $squery = pg_query($pdo, $search);
    $nbud1 = pg_num_rows($squery);
    
    $search = "SELECT * from Customer where email='$email';";
    $squery = pg_query($pdo, $search);
    $nbud2 = pg_num_rows($squery);
    
    if($nbud1==0 && $nbud2==0){
        if($isSeller)
            $search = "Insert into Seller(Name, PhoneNumber, Email, Password, PremiumMember) Values('$name','$phone','$email','$pwd','NO');";
        else
            $search = "Insert into Customer(Name, PhoneNumber, Email, Password) Values('$name','$phone','$email','$pwd');";
        $squery = pg_query($pdo, $search);

        if($isSeller)
            $search = "SELECT * from Seller where email='$email';";
        else
            $search = "SELECT * from Customer where email='$email';";

        $squery = pg_query($pdo, $search);
        $nbud = pg_fetch_row($squery);
        $secret_key = "AY05AS30YL31AC18";
        $issuer_claim = "https://flipin-store-api.herokuapp.com/";
        $issuedat_claim = time(); 
        $expire_claim = $issuedat_claim + 2592000; // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            "iat" => $issuedat_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $nbud[0],
                "email" => $nbud[3],
            ));
            
        $jwt = JWT::encode($token, $secret_key);
        if($isSeller)
            $id = "S".$nbud[0];
        else
            $id = "C".$nbud[0];

        echo json_encode(
            array(
                "responseCode"=>201,
                "jwt" => $jwt,
                "user"=> array("id" => $id,
                    "isSeller" => $isSeller,
                    "name" => $nbud[1],
                    "email" => $nbud[3],
                    "hasAddress" => false)
                ));
    }
    else{
        $nbud = array("responseCode"=>422, "error"=>"Email id already exists");
        $value = json_encode($nbud);
        echo("$value");
    }
}
?>
