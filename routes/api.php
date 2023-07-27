<?php

  use Illuminate\Support\Facades\DB;
  use Illuminate\Http\Request;
  use Illuminate\Http\Response;
  use Illuminate\Support\Facades\Http;
  
  use Illuminate\Contracts\Encryption\Encrypter;
  use Illuminate\Http\RedirectResponse;
  
  use GuzzleHttp\Client;
  use App\Http\Controllers\checkedAccessToken;
  use App\Http\Controllers\Bot;
  use App\Http\Controllers\checkedUser;
  use App\Http\Controllers\MessagesSender;
  use League\CommonMark\CommonMarkConverter;
  use League\CommonMark\Environment;
  use App\Http\Controllers\User;

  use App\Http\Controllers\ParserDomUrl;
  use App\Http\Controllers\Upload;

  $router->get('/', function () {
    return callback_return(false, 404, "Not found");
  });

  $router->post('/', function () {
    return callback_return(false, 404, "Not found");
  });

  /**
   * Informacja o użytkowniku poprzez user_id, lub alias.
   * Dane wejściowe: user_id type int.
   * Zwraca: user_name,user_lastname,user_id,alias,descr.
   * Method: GET,POST.
   */
  $router->get('/users/{user_id}', 'User@getUser');
  $router->post('/users/{user_id}', 'User@getUser');

  /** 
   * Szukanie użytkownika po imieniu lub nazwisku lub aliasu, bądź wszystkie pasujące do siebie rekordy z tych kolumn.
   * Wyszukiwarka użytkownika z tabeli.
   * Dane weyjściowe: search type string.
   * Zwraca: user_name,user_lastname,alias,user_id.
   * Method: GET,POST.
   */
  $router->get('/users', 'User@getUsers');
  $router->post('/users', 'User@getUsers');

  /**
   * Sprawdzenie czy token isnieje, jak tak to wyświetli ostanią aktywność i id użytkownika.
   * Dane wejściowe: access_token type string.
   * Zwraca: user_id,dt_last_login.
   * Method: GET,POST.
   */
  $router->get('/token', function(Request $request, checkedAccessToken $checkedAccessToken) {
    return $checkedAccessToken->index($request['access_token']);
  });
  $router->post('/token', function(Request $request, checkedAccessToken $checkedAccessToken) {
    return $checkedAccessToken->index($request['access_token']);
  });

  /**
   * Autoryzacja użytkownika, poprzez email.
   * Dane wejściowe: email type string.
   * Zwraca: request_token,message: Check your email. Oraz wysłanie e-mail do użytkownika z kodem.
   * Method: GET,POST.
   */
  $router->post('/authorization', function(Request $request, checkedUser $checkedUser) {
    return $checkedUser->creatingLoginForUser($request['email']);
  });
  $router->get('/authorization', function(Request $request, checkedUser $checkedUser) {
    return $checkedUser->creatingLoginForUser($request['email']);
  });

  /**
   * Weryfikacja kodu autoryzacyjnego.
   * Dane weyjściowe: code type int, request_token type string.
   * Zwraca: access_toke,refresh_token,user_id,dt_login. Jeżeli dane są poprawne.
   * Method: GET,POST.
   */
  $router->post('/checkCode', 'checkedUser@checkedCode');
  $router->get('/checkCode', 'checkedUser@checkedCode');

  /**
   * Rejestracja użytkownika po email, imieniu.
   * Dane wejściowe: email type string, user_name type string.
   * Zwraca: true | false.
   * Method: GET,POST.
   */
  $router->post('/registrationUser', 'checkedUser@registrationUser');
  $router->get('/registrationUser', 'checkedUser@registrationUser');

  /**
   * Uwazać na /newtoken/{user_id}.
   * Poniewaz ta funkcja nadaje dla uzytkownika access_token oraz refresh_token który,
   * może wykorzystać w 
   * Np: /user/{access_token}/getMe,
   * Bądź wysyłać wiadomość do użytkownika jakiegoś itp ogołem lepiej nie odkomentowywać tę funckje ewentualnie kiedy jest potrzebny token
   * ALE! Na localhost.
   * Więc UWAŻAJ!!!
   */

  $router->get('/newtoken/{user_id}', 'checkedAccessToken@createAccessToken');
  
  /**
   * Użytkownik informcje,wysłanie wiadomości, i tworzenia bota jeżeli użytkownik jest botem.
   * Dane wejściowe: access_token type string.
   * Zwraca: 
   *    getMe: user_name,user_lastname,is_bot,is_real_bot,is_support,is_father,user_id,email,descr,dt_last_login,alias.
   *    sendMessage: [].
   *    createBot: token.
   * Method: GET,POST.
   */
  $router->get('/user/{access_token}/getMe', 'checkedUser@checkedUserInAccessToken');
  $router->get('/user/{access_token}/sendMessage', array('middleware' => 'htmlpurifier', 'uses' => 'MessagesSender@sendMessage'));
  $router->get('/user/{access_token}/editTextMessage', array('middleware' => 'htmlpurifier', 'uses' => 'MessagesSender@editTextMessage'));
  $router->get('/createBot', 'checksTokenForBot@createBot');
  $router->get('/user/{access_token}/getChats', 'MessagesSender@getChats');
  $router->get('/user/{access_token}/getChat', 'MessagesSender@getChat');
  $router->get('/user/{access_token}/createChat', 'MessagesSender@createChat');
  $router->get('/user/{access_token}/deleteMessage', 'MessagesSender@deleteMessage');
  

  $router->post('/user/{access_token}/getMe', 'checkedUser@checkedUserInAccessToken');
  $router->post('/user/{access_token}/sendMessage', array('middleware' => 'htmlpurifier', 'uses' => 'MessagesSender@sendMessage'));  $router->post('/createBot', 'checksTokenForBot@createBot');
  $router->post('/user/{access_token}/getChats', 'MessagesSender@getChats');
  $router->post('/user/{access_token}/getChat', 'MessagesSender@getChat');
  $router->post('/user/{access_token}/createChat', 'MessagesSender@createChat');

  // 

  /**
   * Bot, weryfikacja i działanie z nim.
   * Dane wejściowe: user_id type int, access_token type string.
   * Zwraca: [].
   * Method: GET,POST.
   */
  $router->get('/bot{user_id}:{token}/getMe', 'Bot@getMe');
  $router->post('/bot{user_id}:{token}/getMe', 'Bot@getMe');

  
  $router->get('/bot{user_id}:{token}/sendMessage', 'Bot@sendMessages');

  /**
   * Preview link — test
   */

   $router->get('/preview', function(Request $request, ParserDomUrl $ParserDomUrl) {
    return $ParserDomUrl->parseLinkPreview($request['url']);
   });

   
   // Tworzymy trasę w Lumen, która będzie przyjmować obraz
    $router->get('/upload', 'Upload@uploaderForUrl');