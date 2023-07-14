<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Contracts\Encryption\Encrypter;
    use Illuminate\Support\Str;

    class checkedAccessToken extends Controller
    {
        /**
         * Sprawdzenie czy token isnieje.
         */
        static public function index($access_token) {
            $access_token = strtoupper(sha1($access_token));
            $atoken = DB::table('authorize_device')->select('user_id', 'dt_last_login')->where('access_token', $access_token)->first();
            if ($atoken) {
                //$atoken->rand = Str::random(128);
                return callback_return(true, 200, $atoken);
            } else {
                return callback_return(false,  401, 'Unauthorized');
            }
        }
        /**
         * Tworzenia access_token.
         * Dla użytkownika/bota podczas logowania, bądź autoryzacji bota poprzez bot123:ABC.
         */
        public function createAccessToken($user_id) {
            // , Encrypter $encrypter
            if (!$user_id) {
                return callback_return(false, 400, 'Missing required parameter user_id');
            }else {
                
                $access_token = Str::random(32);
                $refresh_token = Str::random(32);
                
                $atoken_crypto = strtoupper(sha1($access_token));
                $data_added = array(
                    "id_authorize" => NULL,
                    "user_id" => $user_id,
                    "access_token" => $atoken_crypto,
                    "refresh_token" => $refresh_token,
                    "dt_login" => time()
                );
                $db = DB::table('authorize_device')->insert($data_added);
                $data_added['added'] = $db;
                $data_added['access_token'] = $access_token;
                return $data_added;
            }
        }

    }
