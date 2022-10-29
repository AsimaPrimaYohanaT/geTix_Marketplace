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
  
$data = json_decode(file_get_contents("php://input"), true);

$telepon = $data['telepon'];
$password = md5($data['password']);
$query = "SELECT * FROM user WHERE telepon='$telepon'";
$result = mysqli_query($conn, $query);
$countrow = mysqli_num_rows($result);
$row = mysqli_fetch_array($result);

if(empty($telepon)){
    http_response_code(401);
    echo json_encode(array(
        "Message"=>"Telepon belum ada"
    ));
}else if(empty($password)){
    http_response_code(401);
    echo json_encode(array(
        "Messsage"=>"Password belum diisi"
    ));
}else{
    $query = "SELECT * FROM user WHERE telepon='$telepon'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $countrow = mysqli_num_rows($result);
    $myarray = array();
    if($countrow>0){
        if($password === $row['password']){
            $key = "getix";
            $payload = array(
                "id"=>$row['id'],
                "nama"=>$row['nama'],
                "telepon"=>$row['telepon'],
                "role"=>$row['role']
            );
            $jwt = JWT::encode($payload, $key, 'HS256');
            // $myarray["nama"] = $row['nama'];
            // $myarray["email"] = $row['email'];
            // $myarray["role"] = $row['role'];
            // $myarray["jwt"] = $jwt;
            
            http_response_code(200);
            echo json_encode(array(
                "Message"=>"Login berhasil.",
                "token"=>$jwt
            ));
        }else{
            http_response_code(401);
            echo json_encode(array(
                "Message"=>"Login gagal, password salah."
            ));
        }
    }else{
        http_response_code(400);
        echo json_encode(array(
            "Message"=>"User tidak ditemukan, Silahkan register!"
        ));
    }
}
/*
if($countrow > 0){
    if($password === $row['password']){
        $key="integratif";
        $payload = array(
            "id"=>$row['id'],
            "nama"=>$row['nama'],
            "email"=>$row['email'],
            "role"=>$row['role']
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        $myarray["nama"] = $row['nama'];
        $myarray["role"] = $row['role'];
        $myarray["jwt"] = $jwt;
        $arr = [
                "massage"=>"Login Successfull",
                "Data"=>$myarray
                ];
                http_response_code(200);
    }else{
        $arr = [
            "massage"=>"Login Failed"
        ];
        http_response_code(401);
    }
    echo json_encode($arr);
}else{
    $arr = [
        http_response_code(401),
        "massage"=>"No record found"
    ];
    echo json_encode($arr);
}
*/
     
?>