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
            $phoneNumber = pg_escape_string($data['phoneNumber']);
            $premiumMember = pg_escape_string($data['premiumMember']);
            $category = pg_escape_string($data['category']);
            $logo = pg_escape_string($data['logo']);
            $firstLineAddress = pg_escape_string($data['firstLineAddress']);
            $secondLineAddress = pg_escape_string($data['secondLineAddress']);
            $city = pg_escape_string($data['city']);
            $state = pg_escape_string($data['state']);
            $country = pg_escape_string($data['country']);
            $pincode = pg_escape_string($data['pincode']);

            if(trim($phoneNumber)=="" || !(preg_match('/^[0-9 ]{10,12}$/', $phoneNumber))){
                $nbud = array("responseCode"=>422, "error"=>"Invalid Phone Number");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($premiumMember)=="" || ($premiumMember!="YES" && $premiumMember!="NO"))
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Premium Member Value");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($category)=="" || ($category!="Furniture" && $category!="Jewellery" && $category!="Cosmetics" && $category!="Footwear" && $category!="Clothing" && $category!="Accessories"))
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Category");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($pincode)=="" || !(preg_match('/^[0-9 ]{4,10}$/', $pincode)))
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Pincode");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($firstLineAddress) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid First Line Address");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($secondLineAddress) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Second Line Address");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($city) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid City");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($state) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid State");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($country) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Country");
                $value = json_encode($nbud);
                echo("$value");
            }
            else{
                if(trim($logo) =="")
                {
                    $search = "update seller set phonenumber='$phoneNumber', premiummember='$premiumMember', category='$category', logo=null, 
                    firstlineaddress='$firstLineAddress', secondlineaddress='$secondLineAddress', city='$city', state='$state', country='$country',
                    pincode='$pincode' where sellerid=$sid;";
                }else{
                    $search = "update seller set phonenumber='$phoneNumber', premiummember='$premiumMember', category='$category', logo='$logo', 
                    firstlineaddress='$firstLineAddress', secondlineaddress='$secondLineAddress', city='$city', state='$state', country='$country',
                    pincode='$pincode' where sellerid=$sid;";
                }

                $query = pg_query($pdo, $search);
                
                $finaldata = array( "responseCode" => 204,
                                    "message" => "Profile Updated Successfully.");
                $value = json_encode($finaldata);
                echo("$value");
            }

        }else if($cat=="C" || $cat=='C'){
            $cid = $sid;
            
            $phoneNumber = pg_escape_string($data['phoneNumber']);
            $firstLineAddress = pg_escape_string($data['firstLineAddress']);
            $secondLineAddress = pg_escape_string($data['secondLineAddress']);
            $city = pg_escape_string($data['city']);
            $state = pg_escape_string($data['state']);
            $country = pg_escape_string($data['country']);
            $pincode = pg_escape_string($data['pincode']);

            if(trim($phoneNumber)=="" || !(preg_match('/^[0-9 ]{10,12}$/', $phoneNumber))){
                $nbud = array("responseCode"=>422, "error"=>"Invalid Phone Number");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($pincode)=="" || !(preg_match('/^[0-9 ]{4,10}$/', $pincode)))
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Pincode");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($firstLineAddress) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid First Line Address");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($secondLineAddress) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Second Line Address");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($city) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid City");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($state) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid State");
                $value = json_encode($nbud);
                echo("$value");
            }
            else if(trim($country) =="")
            {
                $nbud = array("responseCode"=>422, "error"=>"Invalid Country");
                $value = json_encode($nbud);
                echo("$value");
            }
            else{
                $search = "update customer set phonenumber='$phoneNumber', firstlineaddress='$firstLineAddress', secondlineaddress='$secondLineAddress', 
                city='$city', state='$state', country='$country', pincode='$pincode' where customerid=$cid;";
                
                $query = pg_query($pdo, $search);
                
                $finaldata = array( "responseCode" => 204,
                                    "message" => "Profile Updated Successfully.");
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