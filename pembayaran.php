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

$pembeli = $decoded['nama'];
$phone = $decoded['telepon'];

$tiket = $data['tiket'];
$organizer = $data['organizer'];
$jumlah = $data['jumlah'];
$total = $data['total'];
$emoney = $data['emoney'];
$email = $data['email'];
$telepon=$data['telepon'];
$password=$data['password'];
$username=$data['username'];


if($decoded['role'] == 'user'){
  if($tiket == NULL || $organizer == NULL || $jumlah == NULL || $total == NULL || $emoney == NULL){
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
      $statusrow = $row['status'];
      //echo $statusrow;

      if($statusrow == 'Menunggu Pembayaran'){
          if($emoney == 'peacepay'){
                $data_login_peacepay = array(
                    'number'=>$telepon,
                    'password'=>$password
                );
                $output_peacepay = curl_login_peacepay('https://e-money-kelompok-12.herokuapp.com/api/login', json_encode($data_login_peacepay));
                //echo $output_peacepay;
                $jwt_peacepay = json_decode($output_peacepay, true);
                $jwt_peacepay = $jwt_peacepay["token"];
                //echo $jwt_peacepay;
                
                $data_transfer_peacepay = array(
                    'tujuan'=>"081320667719",
                    'amount'=>$jumlah,
                );
                $transfer_peacepay = curl_transfer_peacepay('https://e-money-kelompok-12.herokuapp.com/api/transfer', json_encode($data_transfer_peacepay), $jwt_peacepay);
                http_response_code(200);
                echo json_encode(array(
                    "Pesan"=>"Pembayaran berhasil."
                ));
                //echo $transfer_peacepay;
                $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
          }else if($emoney == 'Talangin'){
                $data_login_talangin = array(
                    "email"=>$email,
                    "password"=>$password
                );
                $output_talangin = curl_login_talangin('https://e-money-kelomok-11.000webhostapp.com/api/login.php', json_encode($data_login_talangin));
                $jwt_talangin = json_decode($output_talangin, true);
                $jwt_talangin = $jwt_talangin["jwt"];
                //echo $jwt_talangin;
                
                $data_transfer_talangin = array(
                    "jwt"=>$jwt_talangin,
                    "pengirim"=>$telepon,
                    "penerima"=>"081320667719",
                    "jumlah"=>$total
                );
                $transfer_talangin = curl_transfer_talangin('https://e-money-kelomok-11.000webhostapp.com/api/transfer.php', json_encode($data_transfer_talangin));
                http_response_code(200);
                echo json_encode(array(
                    "Pesan"=>"Pembayaran berhasil."
                ));
                echo $transfer_talangin;
                $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
          }else if($emoney == 'payphone'){
                $data_login_payphone = array(
                    'telepon'=>$telepon,
                    'password'=>$password
                );
                $output_payphone = curl_login_payphone('http://fp-payphone.herokuapp.com/public/api/login', $data_login_payphone);
                $jwt_payphone = json_decode($output_payphone, true);
                $jwt_payphone = $jwt_payphone["token"];
                //echo $jwt_payphone;
                
                $data_transfer_payphone = array(
                    'telepon'=>"081320667719",
                    'jumlah'=>$jumlah,
                    'emoney'=>'payphone'
                );
                $transfer_payphone = curl_transfer_payphone('http://fp-payphone.herokuapp.com/public/api/transfer', $data_transfer_payphone, $jwt_payphone);
                http_response_code(200);
                echo json_encode(array(
                    "Pesan"=>"Pembayaran berhasil."
                ));
                //echo $transfer_payphone;
                $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);

                
          }else if($emoney == 'buski'){
                $data_login_buski = array(
                    'username'=>$username,
                    'password'=>$password
                );
                $output_buski = curl_login_buski('https://arielaliski.xyz/e-money-kelompok-2/public/buskidicoin/publics/login', $data_login_buski);
                // echo $output_buski;
                $jwt_buski = json_decode($output_buski, true);
                $jwt_buski = $jwt_buski["message"]["token"];
                //echo $jwt_buski;
                
                $data_transfer_buski = array(
                    'nomer_hp'=>$telepon,
                    'nomer_hp_tujuan'=>'082110141294',
                    'e_money_tujuan'=>'buski',
                    'amount'=>$jumlah,
                );
                $transfer_buski = curl_transfer_buski('https://arielaliski.xyz/e-money-kelompok-2/public/buskidicoin/admin/transfer', $data_transfer_buski, $jwt_buski);
                http_response_code(200);
                echo json_encode(array(
                    "Pesan"=>"Pembayaran berhasil."
                ));
                //echo $transfer_buski;
                $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
          }else if($emoney == 'ecoin'){
                $data_login = array(
                    'phone' => $telepon,
                    'password' => $password,
                );
                $output = curl_login_ecoin('https://ecoin10.my.id/api/masuk', $data_login);
                // echo $output_buski;
                $jwt = json_decode($output, true);
                $jwt = $output;
                //echo $jwt_buski;
                
                $data_transfer = array(
                    'phone' => $telepon,
                    'tfmethod' => 2,
                    'phone2' => '082110141294',
                    'amount' => $jumlah,
                    'description' => 'transfer from PayPhone',
                );
                $transfer = curl_transfer_ecoin('https://ecoin10.my.id/api/transfer', $data_transfer, $jwt);
                http_response_code(200);
                echo json_encode(array(
                    "Pesan"=>"Pembayaran berhasil."
                ));
                //echo $transfer;
                $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
                $result = mysqli_query($conn, $query);
      }else if($emoney == 'kcn'){
            $data_login = array(
                'email' => $email,
                'password' => $password,
            );
            $output = curl_login_kcn('https://kecana.herokuapp.com/login', $data_login);
            // echo $output_buski;
            $jwt = json_decode($output, true);
            $jwt = $output;
            //echo $jwt_buski;
            
            $data_transfer = array(
                'id' => "29",
                'nohp' => "082110141294",
                'nominaltransfer' => $jumlah,
            );
            $transfer = curl_transfer_kcn('https://kecana.herokuapp.com/transfer/29', $data_transfer, $jwt);
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Pembayaran berhasil."
            ));
            //echo $transfer;
            $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
            $result = mysqli_query($conn, $query);
        }else if($emoney == 'galle'){
            $data_login = array(
                'username' => $username,
                'password' => $password,
            );
            $output = curl_login_galle('https://gallecoins.herokuapp.com/api/users', $data_login);
            // echo $output_buski;
            $jwt = json_decode($output, true);
            $jwt = $output;
            //echo $jwt_buski;
            
            $data_transfer = array(
                'phone' => '082110141294',
                'amount' => $jumlah,
                'description' => 'transfer from PayPhone',
            );
            $transfer = curl_transfer_galle('https://gallecoins.herokuapp.com/api/transfer', $data_transfer, $jwt);
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Pembayaran berhasil."
            ));
            //echo $transfer;
            $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
            $result = mysqli_query($conn, $query);
        }else if($emoney == 'cuan'){
            $data_login = array(
                'notelp' => $telepon,
                'password' => $password,
            );
            $output = curl_login_cuan('https://e-money-kelompok5.herokuapp.com/cuanind/user/login', $data_login);
            // echo $output_buski;
            $jwt = json_decode($output, true);
            $jwt = $output;
            //echo $jwt_buski;
            
            $data_transfer = array(
                'target' => '082110141294',
                'amount' => $jumlah,
            );
            $transfer = curl_transfer_cuan('https://e-money-kelompok5.herokuapp.com/cuanind/transfer', $data_transfer, $jwt);
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Pembayaran berhasil."
            ));
            //echo $transfer;
            $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
            $result = mysqli_query($conn, $query);
        }else if($emoney == 'moneyz'){
            $data_login = array(
                'phone' => $telepon,
                'password' => $password,
            );
            $output = curl_login_moneyz('https://moneyz-kelompok6.herokuapp.com/api/login', $data_login);
            // echo $output_buski;
            $jwt = json_decode($output, true);
            $jwt = $output;
            //echo $jwt_buski;
            
            $data_transfer = array(
                'nomortujuan' => '082110141294',
                'nominal' => $jumlah,
            );
            $transfer = curl_transfer_moneyz('https://moneyz-kelompok6.herokuapp.com/api/user/transfer', $data_transfer, $jwt);
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Pembayaran berhasil."
            ));
            //echo $transfer;
            $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
            $result = mysqli_query($conn, $query);
        }else if($emoney == 'payfresh'){
            $data_login = array(
                'email' => $email,
                'password' => $password,
            );
            $output = curl_login_payfresh('https://payfresh.herokuapp.com/api/login', $data_login);
            // echo $output_buski;
            $jwt = json_decode($output, true);
            $jwt = $output;
            //echo $jwt_buski;
            
            $data_transfer = array(
                'phone' => "082110141294",
                'amount' => $jumlah,
            );
            $transfer = curl_transfer_payfresh('https://payfresh.herokuapp.com/api/user/transfer/33', $data_transfer, $jwt);
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Pembayaran berhasil."
            ));
            //echo $transfer;
            $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
            $result = mysqli_query($conn, $query);
        }else if($emoney == 'padpay'){
            $data_login = array(
                'email' => $email,
                'password' => $password,
            );
            $output = curl_login_padpay('https://mypadpay.xyz/padpay/api/login.php', $data_login);
            // echo $output_buski;
            $jwt_padpay = json_decode($output, true);
            $jwt_padpay = $jwt_padpay["Data"]["jwt"];
            //echo $jwt_padpay;
            
            $data_transfer = array(
                'email' => $email,
                'password' => $email,
                'jwt' => $jwt_padpay,
                'tujuan' => "082110141294",
                'jumlah' => $jumlah,
            );
            $transfer = curl_transfer_padpay('https://mypadpay.xyz/padpay/api/transaksi.php/63', $data_transfer, $jwt_padpay);
            http_response_code(200);
            echo json_encode(array(
                "Pesan"=>"Pembayaran berhasil."
            ));
            //echo $transfer;
            $query = "UPDATE statuspengiriman set status='Telah dibayar' WHERE pembeli='$pembeli' AND tiket='$tiket' AND jumlah='$jumlah'";
            $result = mysqli_query($conn, $query);
        }else{
            http_response_code(401);
            echo json_encode(array(
                "Pesan"=>"Tiket telah dibayar."
            ));
        }
    }
  }
}else{
    http_response_code(401);
    echo json_encode(array(
        "Pesan"=>"Akses ditolak."
    ));
}


?>
