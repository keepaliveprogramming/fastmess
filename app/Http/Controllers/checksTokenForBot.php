<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;

    class checksTokenForBot extends Controller
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
        
        public function createBot($user_id) {
            if ($this->index($user_id)) {
                callback_return(false, 404, 'Bot exits');
            }
        }
    }
