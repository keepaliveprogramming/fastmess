<?php
    
    namespace App\Http\Controllers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Http\Response;
    use Illuminate\Http\Request;
    use Laravel\Lumen\Http\ResponseFactory;
    use Illuminate\Support\Str;
    use App\Http\Controllers\checkedAccessToken;
    

    class checkedUser extends Controller
    {

        protected $mailer;
        protected $email;
        protected $body_mail;
        protected $subject_mail;

        public function checkedUserInAccessToken($access_token, Request $request, checkedAccessToken $checkedAccessToken) {
            if (!$access_token) {
                return callback_return(false, 400, "Missing required parameter access_token");
              }else {
                $atoken = $checkedAccessToken->index($access_token)->getData();
                if (!$atoken->ok) {
                  return callback_return(false, 401, "Unauthorized");
                }else {
                  $user_id = $this->checkedUser($atoken->description->user_id, '', true);
                  
                  return $user_id;
                }
            }
        }
        public function getAvatar($user_id = '') {
            $ava = DB::table('ava_users')->select('image_url', 'id_ava', 'user_id', 'is_home', 'dt_add')->where('user_id', $user_id)->get();
            return callback_return(true, 200, $ava);
        }
        /**
         * Weryfikacja użytkownika czy isnieje czy nie.
         */
        static  public function checkedUser($user_id = '', $email = '', $is_access_token = false) {
            //$res = array();
            if ($email && !$user_id) {
                $user_id = $email;
                $tbl_sel = "email";
                $s_tb = 'email';
            }else {
                $tbl_sel = "user_id";
                $s_tb = 'user_id';
            }
            if ($is_access_token) {
                $select_ac = array('email', 'dt_last_active');
            }else $select_ac = array();
            $user_ids = DB::table('users')->select('user_name','user_lastname', 'is_bot', 'is_real_bot', 'is_support', 'is_father', 'user_id', 'descr', 'alias', $s_tb, ...$select_ac)->where($tbl_sel, $user_id)->first();
           // $res = new stdClass();
            if ($user_ids) {
                $res['ok'] = true;
                $res['error_code'] = 200;
                $res['description'] = $user_ids;
                // $res['ava'] = $this->getAvatar($user_ids['user_id']);
            } else {
                $res['ok'] = false;
                $res['error_code'] = 404;
                $res['description'] = 'User not found';
            }
            return callback_return($res['ok'], $res['error_code'], $res['description']);
        }
        /**
         * Wysyłanie E-mail.
         */
        function mailSend() {
            ini_set('SMTP', 's1.ct8.pl');
            ini_set('smtp_port', 25);
            ini_set('sendmail_from', 'mail@fastmess.ct8.pl');
            ini_set('MAIL_ENCRYPTION', 'tls');
            ini_set('auth_username', 'mail@fastmess.ct8.pl');
            ini_set('auth_password', 'B6~h5iiej.8fhkVOO0Ut>1zeXguO54');
            define('MAIL_APP_NAME', 'FastMess');
            $to = $this->email;
            $subject = $this->subject_mail;
            $message = $this->body_mail;
            $headers = 'From: '.MAIL_APP_NAME.' <mail@fastmess.ct8.pl>' . "\r\n";
            $headers .= 'Reply-To: '.MAIL_APP_NAME.' <mail@fastmess.ct8.pl>' . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();
            $mailSent = mail($to, $subject, $message, $headers);
            return $mailSent;
        }
        
        /**
         * Weryfikacja kodu jednorazowego logowania dla x użytkownika.
         */
        protected function checkedCodeUser($code, $request_token) {
            $code_normal = $code;
            $code = strtoupper(md5(sha1($code)));
            $check = DB::table('code_auth')->select('ip', 'request_token', 'user_id')->where('code', $code)->first();
            if (!$code_normal) {
                return callback_return(false, 400, 'Missing required parametr code');
            }else if(!$check) {
                return callback_return(false, 404, 'Invalid code');
            }else if ($check->request_token != $request_token) {
                return callback_return(false, 400, 'Invalid request_token');
            }else {
                $del = DB::delete("DELETE FROM code_auth WHERE request_token = ?", array($request_token));
                $checkedAccessToken = new checkedAccessToken;
                return $checkedAccessToken->createAccessToken($check->user_id);
            }
        }
        /**
         * Generowanie kodu dla użytkownika a potem wysyłając go na pocztę.
         */
        protected function generateCodeForUser($user_id) {
            $requests = new Request();
            $code_generate = rand(100000, 1000000);
            $md5_sha1_upper_code = strtoupper(md5(sha1($code_generate)));
            $request_token = Str::random(32);
            $ip = $requests->ip();
            //var_dump($ip);
            $insert_code = DB::table('code_auth')->insert(array(
                "id_code" => NULL,
                "user_id" => $user_id,
                "code" => $md5_sha1_upper_code,
                "request_token" => $request_token,
                "ip" => $ip,
                "dt" => time()
            ));
            
            if ($insert_code) {
                return callback_return(true, 200, array(
                    "user" => array(
                        "message" => "Check your email",
                        "request_token" => $request_token,
                    ),
                    "code" => $code_generate
                ));
            }else {
                return callback_return(false, 400, 'errno_added');
            }
        }
        // checkedAccessToken z checkedAccessToken.php

        /**
         * Logowanie za pomocą email użytkownika, i sprawdzenie czy nie jest botem. 
         * Jak będzie botem wyświetli komunikat.
         */
        public function creatingLoginForUser($email) {
            $checkEmail = $this->checkedUser('', $email)->getData();
            if (!$email) {
                return callback_return(false,  404, "Missing required parameter email");
            }else if (!$checkEmail->ok) {
                return callback_return(false,  404, $checkEmail->description);
            }else if ($checkEmail->description) {
                if ($checkEmail->description->is_bot) {
                    return callback_return(false,  403, 'User is bot');
                }else {
                    $this->email = $checkEmail->description->email;
                    $req = $this->generateCodeForUser($checkEmail->description->user_id)->getData();
                    $this->subject_mail = "Kod autoryzacji - {$req->description->code}";
                    $this->body_mail = "Kod autoryzacji - <b>{$req->description->code}</b>";
                    $this->mailSend();
                    
                    return callback_return(true, 200, $req->description->user);
                }
            }
        }
        /**
         * Sprawdzenie kodu weryfikacyjnego który został wysłany do użytkownika.
         */
        public function checkedCode(Request $request) {
            $code_normal = $request['code'];
            $request_token = $request['request_token'];
            if (!$code_normal) {
                return callback_return(false, 400, 'Missing required parametr code');
            }else {
                $code = $code_normal;
                $code_checked = $this->checkedCodeUser($code, $request_token);
                return $code_checked;
            }
            
        }
    }
