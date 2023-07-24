<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Contracts\Encryption\Encrypter;
    use Illuminate\Support\Str;
    use Illuminate\Http\Request;

    class checkedAccessToken extends Controller
    {
        /**
         * Sprawdzenie czy token isnieje.
         */
        static public function index($access_token = '') {
            $request = new Request();
            $access_token = strtoupper(sha1($access_token));
            $atoken = DB::table('authorize_device')->select('user_id', 'dt_last_login')->where('access_token', $access_token)->first();
            if ($atoken) {
                //$atoken->rand = Str::random(128);
                $update_last_active = DB::table('users')->where('user_id', $atoken->user_id)->update(array('dt_last_active' => time()));
                return callback_return(true, 200, $atoken);
            } else {
                // Unauthorized
                return callback_return(false,  401, 'Unauthorized');
            }
            if (!$access_token) {
                return callback_return(false, 400, "Missing required parameter access_token");
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
                return callback_return(true, 200, $data_added);
            }
        }

        public function revokeAccessToken($access_token) {
            $access_token = strtoupper(sha1($access_token));
            $del = DB::delete("DELETE FROM authorize_device WHERE access_token = ?", array($access_token));
            if (!$del) {
                return callback_return(false, 500, 'Not revoked access_token');
            }else {
                return callback_return(true, 200, 'Revoked access_token');
            }
        }

    }
