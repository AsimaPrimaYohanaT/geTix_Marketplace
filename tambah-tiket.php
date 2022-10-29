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


$tiket = $data['tiket'];
$stok = $data['stok'];
$harga = $data['harga'];
$organizer = $data['organizer'];

$decoded = JWT::decode($jwt, $key, array('JWT', 'HS256'));
//$decoded = (array) $decoded;
if($tiket == NULL || $stok == NULL || $harga == NULL || $organizer == NULL){
    http_response_code(401);
    echo json_encode(array(
        "Message"=>"tiket, stok, harga, atau penjual perlu diisi"
    ));
}else{
    if($decoded->nama == $organizer || $decoded->role == 'admin'){
    $query = "SELECT * FROM tiket WHERE tiket='$tiket' AND organizer='$organizer'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    if($tiket != $row["tiket"] && $organizer != $row["organizer"]){
                $insertquery = "INSERT INTO tiket SET 
                        tiket = '$tiket',
                        stok = '$stok',
                        harga = '$harga',
                        organizer = '$organizer'";
        $insertresult = mysqli_query($conn, $insertquery);
        if($insertresult){
            http_response_code(200);
            echo json_encode(array(
                "Message"=>"Tiket berhasil ditambahkan"
            ));
        }else{
            http_response_code(401);
            echo json_encode(array(
                "Message"=>"Tiket gagal ditambahkan"
            ));
        }
    }else{
        http_response_code(401);
        echo json_encode(array(
            "Message"=>"Tiket sudah ada."
        ));
        die;
    }
}else{
    http_response_code(401);
    echo json_encode(array(
        "Message"=>"Nama penjual tidak sesuai"
    ));
}
}



?>