<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:*');
header('Content-Type:application/json');
 
// files needed to connect to database
include_once 'config/database.php';
include_once 'validatetoken.php';
include_once 'integrasi/peacepay.php';
include_once 'integrasi/talangin.php';
include_once 'integrasi/payphone.php';
include_once 'integrasi/buski.php';
include_once 'integrasi/kcn.php';
include_once 'integrasi/ecoin.php';
include_once 'integrasi/galle.php';
include_once 'integrasi/cuan.php';
include_once 'integrasi/moneyz.php';
include_once 'integrasi/payfresh.php';
include_once 'integrasi/padpay.php';
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

$organizer = $decoded['nama'];
$phone = $decoded['telepon'];

$tiket = $data['tiket'];
$pembeli = $data['pembeli'];
$jumlah = $data['jumlah'];
$total = $data['total'];
$emoney = $data['emoney'];
$email = $data['email'];
$telepon=$data['telepon'];
$password=$data['password'];
$username=$data['username'];

if($decoded['role'] == 'penjual' || $decoded['role'] == 'user' || $decoded['role'] == "admin"){
    if($tiket == NULL ||  $jumlah == NULL || $total == NULL || $emoney == NULL){
        http_response_code(401);
        echo json_encode(array(
            "Pesan"=>"Data tidak boleh kosong."
        ));
    }else{
        $query = "SELECT * FROM statuspengiriman WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
        $query1 = "SELECT * FROM beli WHERE pembeli='$pembeli' AND tiket='$tiket' AND organizer='$organizer' AND jumlah='$jumlah' AND total='$total'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_array($result);
        $result1 = mysqli_query($conn, $query1);
        $row1 = mysqli_fetch_array($result1);

        if($row['status'] == 'Paket sedang dikirim'){
            if($emoney == 'buski'){
                $data_login = array(
                    'username'=>'payPhone',
                    'password'=>'payPhone'
                );
                $output = curl_login_buski('https://arielaliski.xyz/e-money-kelompok-2/public/buskidicoin/publics/login', $data_login);
                $jwt = json_decode($output, true);
                $jwt = $jwt["message"]["token"];
                
                $data = array(
                    'nomer_hp'=>'081263239502',
                    'nomer_hp_tujuan'=>$telepon,
                    'e_money_tujuan'=>$emoney,
                    'amount'=>$total,
                    'description'=>'Payment marketplace'
                );
                $transfer_buski = curl_transfer_buski('https://arielaliski.xyz/e-money-kelompok-2/public/buskidicoin/admin/transfer', $jwt, $data);
                //echo($transfer_buski);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }  
            }else if($emoney == 'kcn'){
                $data_login = array(
                    "email"=>"payPhone@gmail.com",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_kcn('https://kecana.herokuapp.com/login', $data_login);
                $jwt = $output;
                
                $data = array(
                    "id"=>"29",
                    "nohp"=>$phone,
                    "nominaltransfer"=>(int)$total
                );
                $transfer_kcn = curl_transfer_kcn('https://kecana.herokuapp.com/transfer', json_encode($data), $jwt);
                //echo($transfer_kcn);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'galle'){
                $data_login = array(
                    "username"=>"payPhone",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_galle('https://gallecoins.herokuapp.com/api/users', $data_login);
                $jwt = json_decode($output,true);
                $jwt = $jwt['token'];
                
                $data = array(
                    "amount"=>$total,
                    "phone"=>$telepon,
                    "description"=>"Payment marketplace"
                );
                $transfer_galle = curl_transfer_galle('https://gallecoins.herokuapp.com/api/transfer', json_encode($data), $jwt);
                //echo($transfer_galle);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'cuan'){
                $data_login = array(
                    "notelp"=>"081263239502",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_cuan('https://e-money-kelompok5.herokuapp.com/cuanind/user/login', $data_login);
                $jwt = $output;
                
                $data = array(
                    "amount"=>$jumlah,
                    "target"=>$pembeli
                );
                $transfer_cuan = curl_transfer_cuan('https://e-money-kelompok5.herokuapp.com/cuanind/transfer', json_encode($data), $jwt);
                //echo($transfer_cuan);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'moneyz'){
                $data_login = array(
                    "phone"=>"081263239502",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_moneyz('https://moneyz-kelompok6.herokuapp.com/api/login', $data_login);
                $jwt = json_decode($output, true);
                $jwt = $jwt["token"];
                
                $data = array(
                    "nominal"=>$total,
                    "nomortujuan"=>$telepon
                );
                $transfer_moneyz = curl_transfer_moneyz('https://moneyz-kelompok6.herokuapp.com/api/user/transfer', json_encode($data), $jwt);
                //echo($transfer_moneyz);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'payfresh'){
                $data_login = array(
                    "email"=>"payPhone@gmail.com",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_payfresh('https://payfresh.herokuapp.com/api/login', $data_login);
                $jwt = json_decode($output, true);
                $jwt = $jwt["token"];
                
                $data = array(
                    "amount"=>$total,
                    "phone"=>$telepon
                );
                $transfer_payfresh = curl_transfer_payfresh('https://payfresh.herokuapp.com/api/user/transfer/44', json_encode($data), $jwt);
                //echo($transfer_payfresh);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'padpay'){
                $data_login = array(
                    "email"=>"payPhone@gmail.com",
                    "password"=>"payPhone"
                );
                $output = curl_login_padpay('https://mypadpay.xyz/padpay/api/login.php', json_encode($data_login));
                $jwt = json_decode($output, true);
                $jwt = $jwt["Data"]["jwt"];
                
                $data = array(
                    "email"=>"payPhone@gmail.com",
                    "password"=>"payPhone",
                    "jwt"=>$jwt,
                    "tujuan"=>$telepon,
                    "jumlah"=>$total
                );
                $transfer_padpay = curl_transfer_padpay('https://mypadpay.xyz/padpay/api/transaksi.php/73', json_encode($data), $jwt);
                //echo($transfer_padpay);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'payphone'){
                $data_login = array(
                    'telepon'=>'081263239502',
                    'password'=>'payPhone'
                );
                $output = curl_login_payphone('http://fp-payphone.herokuapp.com/public/api/login', $data_login);
                $jwt = json_decode($output, true);
                $jwt = $jwt["token"];

                $data = array(
                    'telepon'=>$telepon,
                    'jumlah'=>$total,
                    'emoney'=>'payPhone'
                );
                $transfer_payphone = curl_transfer_buski('http://fp-payphone.herokuapp.com/public/api/transfer', $jwt, $data);
                //echo($transfer_payphone);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'ecoin'){
                $data_login = array(
                    "phone"=>"081263239502",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_ecoin('https://ecoin10.my.id/api/masuk', $data_login);
                $jwt = json_decode($output, true);
                $jwt = $jwt["accessToken"];
                
                $data = array(
                    "amount"=>$total,
                    "phone2"=>$telepon,
                    "description"=>"Payment marketplace"
                );
                $transfer_ecoin = curl_transfer_ecoin('https://ecoin10.my.id/api/transfer', json_encode($data), $jwt);
                //echo($transfer_ecoin);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'talangin'){
                $data_login_talangin = array(
                    "email"=>"payPhone@gmail.com",
                    "password"=>"payPhone"
                );
                $output_talangin = curl_login_talangin('https://e-money-kelomok-11.000webhostapp.com/api/login.php', json_encode($data_login_talangin));
                $jwt_talangin = json_decode($output_talangin, true);
                $jwt_talangin = $jwt_talangin["jwt"];

  
                $data_transfer_talangin = array(
                    "jwt"=>$jwt_talangin,
                    "pengirim"=>"081263239502",
                    "penerima"=>$telepon,
                    "jumlah"=>$total
                );
                $transfer_talangin = curl_transfer_talangin('https://e-money-kelomok-11.000webhostapp.com/api/transfer.php', json_encode($data_transfer_talangin));
                echo $transfer_talangin;  
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else if($emoney == 'peacepay'){
                $data_login = array(
                    "number"=>"081263239502",
                    "password"=>"payPhone"
                );
                $data_login = json_encode($data_login);
                $output = curl_login_peacepay('https://e-money-kelompok-12.herokuapp.com/api/login', $data_login);
                $jwt = json_decode($output, true);
                $jwt = $jwt["token"];
                
                $data = array(
                    "amount"=>$total,
                    "tujuan"=>$telepon
                );
                $transfer_peace = curl_transfer_peacepay('https://e-money-kelompok-12.herokuapp.com/api/transfer', json_encode($data), $jwt);
                //echo($transfer_peace);
                $query = "UPDATE statuspengiriman set status='Pesanan selesai' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
                if($result){
                    http_response_code(200);
                    echo json_encode(array(
                        "Pesan"=>"Update order berhasil."
                    ));
                }else{
                    http_response_code(401);
                    echo json_encode(array(
                        "Pesan"=>"Update order gagal. Silahkan hubungi admin."
                    ));
                }
            }else{
                http_response_code(401);
                echo json_encode(array(
                    "Pesan"=>"Emoney tidak terdaftar."
                ));
            }
        }else{
            http_response_code(401);
            echo json_encode(array(
                "Pesan"=>"Order belum dikonfirmasi penjual."
            ));
        }
    }
}
?>
