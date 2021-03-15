<?php
session_start();
require "vendor/autoload.php";
use \Firebase\JWT\JWT;

include ('config/db.php');

header("HTTP/1.1 200 OK");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');

$data = json_decode(file_get_contents('php://input'), true);

/* catch data from the form using Post and encoding password*/
$email = pg_escape_string($data['email']);
$pwd = md5(pg_escape_string($data['password']));

$search = "SELECT password from Account where email='$email';";
$squery = pg_query($pdo, $search);
$nbud = pg_fetch_row($squery);
$loginpwd = $nbud[0];

if($loginpwd!=NULL)
{
    if(!strcmp($pwd,$loginpwd))
    {
        /* link to home page */
        $search = "SELECT * from Account where email='$email';";
        $squery = pg_query($pdo, $search);
        $nbud = pg_fetch_row($squery);
        //$nbud = array("Response Code"=>200, "Value"=>array("ID"=>$nbud[0], "Name"=>$nbud[1], "Email"=>$nbud[2], "Password"=>$nbud[3], "Phone Number"=>$nbud[4]));
        //$value = json_encode($nbud);
        //echo $value;
        $secret_key = "FC07";
        $issuer_claim = "https://flipin-store-api.herokuapp.com/"; // this can be the servername
        //$audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // issued at
        //$notbefore_claim = $issuedat_claim + 10; //not before in seconds
        $expire_claim = $issuedat_claim + 3600; // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            //"aud" => $audience_claim,
            "iat" => $issuedat_claim,
            //"nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "Id" => $nbud[0],
                "Name" => $nbud[1],
                "Email" => $nbud[2],
                "PhoneNumber" => $nbud[4],
        ));

        http_response_code(200);
        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
            array(
                "Response Code"=>200,
                "Value"=>array("Message" => "Successful login.",
                    "jwt" => $jwt,
                    "Id" => $nbud[0],
                    "Name" => $nbud[1],
                    "Email" => $nbud[2],
                    "PhoneNumber" => $nbud[4],
                    "expireAt" => $expire_claim)
            ));
    }
    else
    {
     /* link to login page */
      $nbud = array("Response Code"=>422, "Value"=>array("Error"=>"Invalid Password"));
      $value = json_encode($nbud);
      echo("$value");
    }
}
else
{
 /* link to login page */
  $nbud = array("Response Code"=>422, "Value"=>array("Error"=>"Invalid Login ID"));
  $value = json_encode($nbud);
  echo("$value");
}
?>