<?php
  
  
  use Illuminate\Http\Response;
  use App\Http\Controllers\checksTokenForBot;
  

  /**
   * Tworzenie zwrotnej odpowiedzi dla użytkownika czyli callback.
   */
  function callback_return($ok, $error_code = 200, $description) {
    if (!$error_code) {
      $error_code = 200;
    }
    return response()->json(array(
      "ok" => $ok,
      "error_code" => $error_code,
      "description" => $description
    ), $error_code);
  }

  # $router->get('/bots/{user_id}:{token}', 'checksTokenForBot@index');
  
  /**
   * Dołączania include_once api.php.
   * W api.php są konfiguracje.
   */
  include_once __DIR__.'/api.php';

  