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

// $jwt = $token;
// $key = "integratif";
// $decoded = JWT::decode($jwt, $key, array('JWT', 'HS256'));
$produk = $data['produk'];

if($produk == NULL){
    http_response_code(400);
    echo json_encode(array(
        "Pesan"=>"Data produk diperlukan."
    ));
}else{
    $query = "SELECT * FROM produk WHERE produk='$produk'";
    $result = mysqli_query($conn, $query);
    $countrow = mysqli_num_rows($result);
    $myarray = array();

    if($countrow > 0){
        while($row = mysqli_fetch_array($result)){
            $records = array(
                "produk"=>$row['produk'],
                "penjual"=>$row['penjual'],
                "harga"=>$row['harga'],
                "stok"=>$row['stok']
            );
            array_push($myarray, $records);
        }
        http_response_code(200);
        echo json_encode(array(
            "Pesan"=>"Data ditemukan.",
            "Data"=>$myarray
        ));
    }else{
        http_response_code(401);
        echo json_encode(array(
            "Pesan"=>"Data tidak ditemukan."
        ));
    }
}



?>