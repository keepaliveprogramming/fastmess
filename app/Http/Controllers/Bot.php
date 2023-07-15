<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Http\Controllers\checkedUser; // checkedUser.php
    use Illuminate\Http\Request;
    use App\Http\Controllers\checkedAccessToken;

    class Bot extends Controller
    {
        
        static public function index($user_id, $token) {
            $bot = DB::table('bots_token')->select('user_id')->where('user_id', $user_id)->where('token', $token)->first();
            if ($bot) {
                $user_ids = DB::table('users')->select('user_name','user_lastname', 'is_bot', 'is_real_bot', 'is_support', 'is_father')->where('user_id', $user_id)->first();
                if ($user_ids) {
                return callback_return(true, 200, $user_ids);
                } else {
                return callback_return(false,  401, 'Unauthorized');
                }
            }else {
                return callback_return(false,  401, 'Unauthorized');
            }
        }
        public function getMe($user_id, $token) {
            $bot = DB::table('bots_token')->select('user_id')->where('user_id', $user_id)->where('token', $token)->first();
            if ($bot) {
            $user_ids = DB::table('users')->select('user_name','user_lastname', 'is_bot', 'is_real_bot', 'is_support', 'is_father')->where('user_id', $user_id)->first();
            if ($user_ids) {
                return callback_return(true, 200, $user_ids);
            } else {
                return callback_return(false,  401, 'Unauthorized');
            }
            }else {
            return callback_return(false,  401, 'Unauthorized');
            }
        }
        public function createBotForDB($user_id) {
            if (!$user_id) {
                return callback_return(false, 400, 'Missing required parametr user_id');
            }else {
                $token = Str::random(32);
                $check_bot = DB::table('bots_token')->where('user_id', $user_id);
                if ($check_bot->first()) {
                    $check_bot->update(array(
                        'token' => $token
                    ));
                }else {
                    $dataCreateBot = array(
                        "id_token" => NULL,
                        "user_id" => $user_id,
                        "token" => $token
                    );
                    DB::table('bots_token')->insert($dataCreateBot);
                    
                    return callback_return(true, 200, array(
                        "token" => $user_id.':'.$token,
                    ));
                }
            }
        }
        public function createBot(Request $request) {
            if (!$request['access_token']) {
                return callback_return(false, 400, 'Missing required parametr access_token');
            }else {
                $user_atoken = new checkedAccessToken();
                $user_id = $user_atoken->index($request['access_token'])->getData();

                $checkedUser = new checkedUser();
                $check_u = $checkedUser->checkedUser($user_id->description->user_id)->getData();
                if (!$check_u->description->is_bot) {
                    return callback_return(false, 403, 'You are not a bot');
                }else {
                    return $this->createBotForDB($user_id);
                }
            }
        }
    }
