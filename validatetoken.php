<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:*');
header('Content-Type:application/json');
 
 // files needed to connect to database
 include_once 'config/database.php';
 include_once 'libs/vendor/firebase/php-jwt-master/src/BeforeValidException.php';
 include_once 'libs/vendor/firebase/php-jwt-master/src/ExpiredException.php';
 include_once 'libs/vendor/firebase/php-jwt-master/src/SignatureInvalidException.php';
 include_once 'libs/vendor/firebase/php-jwt-master/src/JWT.php';
 include_once 'libs/vendor/firebase/php-jwt-master/src/JWK.php';
 
 use Firebase\JWT\JWT;

 function AuthHeader(){
     $headers = NULL;
     if(isset($_SERVER['Authorization'])){
         $headers = trim($_SERVER["Authorization"]);
     }else if(isset($_SERVER['HTTP_AUTHORIZATION'])){
         $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
     }else if(function_exists('apache_request_headers')){
         $requestheaders = apache_request_headers();
         $requestheaders = array_combine(array_map('ucwords', array_keys($requestheaders)), array_values($requestheaders));
         if(isset($requestheaders['Authorization'])){
             $headers = trim($requestheaders['Authorization']);
         }
     }
     return $headers;
 }

 $key = "getix";
 $header = AuthHeader();
 $token = substr($header, 7);


 try{
     $decoded = JWT::decode($token, $key, array('JWT', 'HS256'));
     http_response_code(200);
    //  echo json_encode(array(
    //      "message"=>"Access granted."
    //  ));
 }

 catch(Exception $e){
     http_response_code(401);
    //  echo json_encode(array(
    //      "message"=>"Access denied."
    //  ));
     die;
 }




?>