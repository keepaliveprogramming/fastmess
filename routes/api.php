<?php

  use Illuminate\Support\Facades\DB;
  use Illuminate\Http\Request;
  use Illuminate\Http\Response;
  use Illuminate\Support\Facades\Http;
  
  use Illuminate\Contracts\Encryption\Encrypter;
  use Illuminate\Http\RedirectResponse;
  
  use GuzzleHttp\Client;
  use Illuminate\Support\Facades\Storage;

  use App\Http\Controllers\checkedAccessToken;
  use App\Http\Controllers\checksTokenForBot;
  use App\Http\Controllers\checkedUser;
  use App\Http\Controllers\MessagesSender;
  use League\CommonMark\CommonMarkConverter;
  use League\CommonMark\Environment;

  $router->get('/', function () {
    return callback_return(false, 404, "Not found");
  });

  /**
   * Informacja o użytkowniku poprzez user_id, lub alias.
   */
  $router->get('/users/{user_id}', function ($user_id) {
    $user = DB::table('users')->select('user_name', 'user_lastname', 'user_id', 'alias', 'descr')->where('user_id', $user_id)->orWhere('alias', $user_id)->first();
    if ($user) {
      return callback_return(true, 200, $user);
    } else {
      return callback_return(false, 404, "User not found");
    }
    if (!$user_id) {
      return callback_return(false, 400, 'Missing required parametr user_id');
    }
  });
  
  /** 
   * Szukanie użytkownika po imieniu lub nazwisku lub aliasu, bądź wszystkie pasujące do siebie rekordy z tych kolumn.
   * Wyszukiwarka użytkownika z tabeli.
   */
  $router->get('/users', function (Request $request) {
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
  });

  // Sprawdzenie czy token isnieje.
  $router->get('/token', function(checkedAccessToken $checkedAccessToken, Request $request, Encrypter $encrypter) {
    if (!$request['access_token']) {
      return callback_return(false, 400, "Missing required parameter access_token");
    }else {
      return $checkedAccessToken->index($request['access_token'], $encrypter);
    }
  });

  $router->get('/addtoken', function(checkedAccessToken $checkedAccessToken) {
    return $checkedAccessToken->createAccessToken(5);
  });
  $router->get('/authorization', function(Request $request, checkedUser $checkedUser) {
    return $checkedUser->creatingLoginForUser($request['email']);
  });

  $router->get('/checkCode', 'checkedUser@checkedCode');

  $router->get('/user/{access_token}/getMe', 'checkedUser@checkedUserInAccessToken');
  $router->get('/user/{access_token}/sendMessage', 'MessagesSender@sendMessage');
  $router->get('/createBot', 'checksTokenForBot@createBot');
  /**
   * Bot, weryfikacja i działanie z nim.
   */
  $router->get('/bot{user_id}:{token}', function ($user_id, $token) {
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
  });

