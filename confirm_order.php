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

$jwt = $token;
$key = "getix";
$decoded = JWT::decode($jwt, $key, array('JWT', 'HS256'));
$decoded = (array) $decoded;

$pembeli = $decoded['nama'];
$telepon = $decoded['telepon'];
$tiket = $data['tiket'];
$jumlah = $data['jumlah'];
$role = $data['role'];
//$status = $data['status'];

if($decoded['role'] == 'user'){
        $updatequery = "UPDATE statuspengiriman SET pembeli='$pembeli', tiket='$tiket', jumlah='$jumlah',status='Paket sedang dikirim' WHERE status='Telah dibayar'";
        $resultupdate = mysqli_query($conn, $updatequery);
        if($resultupdate){
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Status pengiriman telah diperbarui."
            ));
        }else{
            http_response_code(401);
            echo json_encode(array(
                "Pesan"=>"Status pengiriman gagal diperbarui."
            ));
        }
    }else{
        http_response_code(401);
        echo json_encode(array(
            "Pesan"=>"Hanya penjual yang memiliki akses."
        ));
    }
?>