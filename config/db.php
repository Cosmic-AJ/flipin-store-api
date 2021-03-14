<?php

$host = "ec2-18-207-95-219.compute-1.amazonaws.com";
$user = "sclhfuwzyeozhn";
$password = "710d9dc6f8def69f87965fa0b9bd77ae62e2f1be68abbcfbb7f1433c958fd224";
$dbname = "da8umdeu5jatdk";
$port = "5432";
try{
  //Set DSN data source name
    $dsn = "pgsql:host=" . $host . ";port=" . $port .";dbname=" . $dbname . ";user=" . $user . ";password=" . $password . ";";
    $connStr = "host=$host port=$port dbname=$dbname user=$user password=$password";

  //create a pdo instance
  $pdo = pg_connect($connStr);
  $pod = new PDO($dsn, $user, $password);
  $pod->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
  $pod->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $pod->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
echo 'Connection failed: ' . $e->getMessage();
}
?>