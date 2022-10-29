<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:*');
header('Content-Type:application/json');
 
// files needed to connect to database
include_once 'config/database.php';
//include_once 'validatetoken.php';
include_once 'integrasi/talangin.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/ExpiredException.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/JWT.php';
include_once 'libs/vendor/firebase/php-jwt-master/src/JWK.php';
 
use Firebase\JWT\JWT;
  
$chTransfer = curl_init();
                $chLogin= curl_init();

                curl_setopt($chTransfer, CURLOPT_POST, 1);
                curl_setopt($chTransfer, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chLogin, CURLOPT_POST, 1);
                curl_setopt($chLogin, CURLOPT_RETURNTRANSFER, true);
                $queryLogin = [
                    'telepon'=>'082110141294',
                     'password'=>'payPhone10'
                ];

                curl_setopt($chLogin, CURLOPT_URL,"http://fp-payphone.herokuapp.com/public/api/login");
                curl_setopt($chLogin, CURLOPT_POSTFIELDS, json_encode($queryLogin));
                curl_setopt($chLogin, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);

                $chLogin_output = curl_exec($chLogin);
                curl_close ($chLogin);
                
                
                $query_transfer = [
                    'telepon'=>"081320667719",
                     'jumlah'=>"10000",
                     'emoney'=>'payphone'
                ];

                curl_setopt($chTransfer, CURLOPT_URL,"http://fp-payphone.herokuapp.com/public/api/transfer");
                curl_setopt($chTransfer, CURLOPT_POSTFIELDS, json_encode($query_transfer));
                curl_setopt($chTransfer, CURLOPT_HTTPHEADER, [
                    "Authorization:Bearer $chLogin_output",
                    'Content-Type:application/json'
                    ]);
                    http_response_code(200);
                echo json_encode(array(
                    "Pesan"=>"Pembayaran berhasil."
                ));


?>