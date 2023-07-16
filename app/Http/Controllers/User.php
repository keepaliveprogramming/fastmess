<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class User extends Controller
{
    public function getUsers(Request $request) {
        if (!$request['search']) {
            return callback_return(false, 400, 'Missing required parameter search');
          }else {
            $users = DB::table('users')->select('user_name', 'user_lastname', 'user_id', 'alias')
            ->where('user_name', 'LIKE', '%'.$request['search'].'%')
            ->orWhere('user_lastname', 'LIKE', '%'.$request['search'].'%')
            ->orWhere('alias', 'LIKE', '%'.$request['search'].'%')
            ->get();
            $ok = true;
            $code = 200;
            if (is_array($users) && $users != array()) {
              $users = 'no_users';
              $ok = false;
              $code = 404;
            }
            return callback_return($ok, $code, $users);
          }
    }
    public function getUser($user_id) {
        $user = DB::table('users')->select('user_name', 'user_lastname', 'user_id', 'alias', 'descr')->where('user_id', $user_id)->orWhere('alias', $user_id)->first();
        if ($user) {
        return callback_return(true, 200, $user);
        } else {
        return callback_return(false, 404, "User not found");
        }
        if (!$user_id) {
        return callback_return(false, 400, 'Missing required parametr user_id');
        }
    }
}