<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:*');
header('Content-Type:application/json');
 
// files needed to connect to database
include_once 'config/database.php';
include_once 'validatetoken.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/ExpiredException.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/JWT.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/JWK.php';
 
use Firebase\JWT\JWT;
  
$data = json_decode(file_get_contents("php://input"), true);


?>