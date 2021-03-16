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

/* catch data from the form using Post and encoding password*/
$name = pg_escape_string($data['name']);
$email = pg_escape_string($data['email']);
$message = pg_escape_string($data['message']);

if($name=="" || $name==" " || !(preg_match('/^[a-zA-Z ]*$/', $name)))
{
    $nbud = array("responseCode"=>422, "message"=>"Invalid Name");
    $value = json_encode($nbud);
    echo("$value");
}
else if($email=="" || $email==" "|| !(filter_var($email, FILTER_VALIDATE_EMAIL)))
{
    $nbud = array("responseCode"=>422, "message"=>"Invalid Email");
    $value = json_encode($nbud);
    echo("$value");
}
else if($message=="" || $message==" ")
{
    $nbud = array("responseCode"=>422, "message"=>"Invalid Message");
    $value = json_encode($nbud);
    echo("$value");
}
else
{
    $search = "Insert into Contact(Name, Email, Message) Values('$name','$email','$message');";
    $squery = pg_query($pdo, $search);

    http_response_code(200);
    echo json_encode(
        array(
            "Response Code"=>200,
            "message" => "successMessage"));
}
?>