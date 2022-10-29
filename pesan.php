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

$tiket = $data['tiket'];
$jumlah = $data['jumlah'];
$pembeli = $decoded['nama'];
//$cekpenjual = "SELECT * FROM users WHERE role='penjual' AND produk='$produk'";
$organizer = $data['organizer'];
$harga = $data['harga'];
//$harga="SELECT harga FROM tiket WHERE tiket = '$tiket' ";
//$resultharga = mysqli_query($conn, $harga);
//$rowharga = mysqli_fetch_array($resultharga);
//$harga = $data['harga'];
//$rowpenjual = mysqli_fetch_array($penjual);


if($decoded['role'] == 'user' || $decoded['role'] == 'organizer' || $decoded['role'] == 'admin'){
    if($pembeli == NULL || $tiket == NULL || $jumlah == NULL){
        http_response_code(401);
        echo json_encode(array(
            "Message"=>"Data perlu diisi"
        ));
    }else{
        $query = "SELECT * FROM tiket WHERE organizer='$organizer' AND tiket='$tiket'";
        $query2 = "SELECT * FROM user WHERE nama='$pembeli'";

        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_array($result);

        $result2 = mysqli_query($conn, $query2);
        $row2 = mysqli_fetch_array($result2);

        if($row['stok'] < $jumlah){
            http_response_code(400);
            echo json_encode(array(
                "Message"=>"Stok habis"
            ));
        }else{
            $updatequery = "UPDATE tiket SET stok = (stok - $jumlah) WHERE organizer='$organizer' AND tiket='$tiket'";
            $result = mysqli_query($conn, $query);
            $countrow = mysqli_num_rows($result);
            $myarray = array();
            if($countrow > 0){
                $result = mysqli_query($conn, $updatequery);
                $records = array(
                    "tiket"=>$row['tiket'],
                    'stok'=>$row['stok'] - $jumlah
                );
                array_push($myarray, $records);
                http_response_code(200);
                echo json_encode(array(
                    "Message"=>"Pemesanan berhasil!",
                    "Data"=>$myarray
                ));    
            }else{
                http_response_code(400);
                echo json_encode(array(
                    "Message"=>"Pemesanan gagal"
                ));
                die;    
            }
        }
        $query3 = "INSERT INTO beli SET pembeli='$pembeli', tiket='$tiket', organizer='$organizer',jumlah='$jumlah', total=($harga * $jumlah)";
        $result1 = mysqli_query($conn, $query3);
        if($result1){
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Data order telah masuk."
            ));
        }else{
            http_response_code(400);
            echo json_encode(array(
                "Pesan"=>"Data order gagal masuk."
            ));
        }
        $query4 = "INSERT INTO statuspengiriman SET pembeli='$pembeli' ,tiket='$tiket', jumlah='$jumlah', status='Menunggu Pembayaran'";
        $result3 = mysqli_query($conn, $query4);
        if($result3){
            http_response_code(200);
            echo json_encode(array(
                "Message"=>"Status order berhasil masuk."
            ));
        }else{
            http_response_code(401);
            echo json_encode(array(
                "Message"=>"Status order gagal masuk."
            ));
        }
    }
}else{
    http_response_code(401);
    echo json_encode(array(
        "Pesan"=>"Hanya user yang memiliki akses."
    ));
}
?>