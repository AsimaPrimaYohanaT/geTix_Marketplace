<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:*');
header('Content-Type:application/json');

// files needed to connect to database
include_once 'config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$nama = $data['nama'];
$password = md5($data['password']);
$role = $data['role'];
$telepon = $data['telepon'];

$cekuser = "SELECT * FROM user WHERE nama='$nama'";
$cekphone = "SELECT * FROM user WHERE telepon='$telepon'";

$result1 = mysqli_query($conn, $cekuser);
$result2 = mysqli_query($conn, $cekphone);

$cekquery1 = mysqli_query($conn, $cekuser);
$cekquery2 = mysqli_query($conn, $cekphone);

if(mysqli_num_rows($cekquery1) > 0){
    http_response_code(401);
    echo json_encode(array(
        "Message"=>"Nama telah terdaftar"
    ));
}else if(mysqli_num_rows($cekquery2) > 0){
    http_response_code(401);
    echo json_encode(array(
        "Message"=>"Telepon telah terdaftar"
    ));
}else{
    $query = "INSERT INTO user SET 
                nama = '$nama',
                password = '$password',
                role = '$role',
                telepon = '$telepon'";
    $result = mysqli_query($conn, $query);
    if($result){
        http_response_code(200);
        echo json_encode(array(
            "Message"=>"Registrasi berhasil!"
        ));
    }else{
        http_response_code(401);
        echo json_encode(array(
            "Message"=>"Registrasi gagal"
        ));
    }
}

?>