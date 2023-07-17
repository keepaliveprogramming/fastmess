<?php
  namespace App\Http\Controllers;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\DB;
  use App\Http\Controllers\checkedUser;
  class User extends Controller
  {
    public function getUsers(Request $request, checkedUser $checkedUser) {
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
          $my = 'no_users';
          $ok = false;
          $code = 404;
        }else {
          
          foreach ($users as $user) {
            $checked = $checkedUser->getAvatar($user->user_id)->getData();
            $ava = $checked->description;
            $my[] = array(
              "user_name" => $user->user_name,
              "user_lastname" => $user->user_lastname,
              "user_id" => $user->user_id,
              "alias" => $user->alias,
              "ava" => $ava
            );
          }
        }
        return callback_return($ok, $code, $my);
      }
    }
    public function getUser($user_id, checkedUser $checkedUser) {
      $user = array();
      
      $user = DB::table('users')->select('user_name', 'user_lastname', 'user_id', 'alias', 'descr')->where('user_id', $user_id)->orWhere('alias', $user_id)->first();
      if ($user) {
        # $checkedUser = new checkedUser();
        $checked = $checkedUser->getAvatar($user->user_id)->getData();
        $user->ava = $checked->description;
        
        // $user;
        return callback_return(true, 200, $user);
      } else {
        return callback_return(false, 404, "User not found");
      }
      if (!$user_id) {
        return callback_return(false, 400, 'Missing required parametr user_id');
      }
    }
  }