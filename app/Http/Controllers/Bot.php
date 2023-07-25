<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\DB;
    
    use Illuminate\Support\Str;
    use App\Http\Controllers\checkedUser; // checkedUser.php
    use Illuminate\Http\Request;
    use App\Http\Controllers\checkedAccessToken;

    use App\Http\Controllers\MessagesSender;
    use Illuminate\Contracts\Encryption\Encrypter;
    use App\Http\Controllers\ParserDomUrl;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;




    class Bot extends Controller
    {
        
        public function index($user_id, $token) {
            $bot = DB::table('bots_token')->select('user_id')->where('user_id', $user_id)->where('token', $token)->first();
            if ($bot) {
                $user_ids = DB::table('users')->select('user_name','user_lastname', 'is_bot', 'is_real_bot', 'is_support', 'is_father', 'user_id')->where('user_id', $user_id)->first();
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
            $user_ids = DB::table('users')->select('user_name','user_lastname', 'is_bot', 'is_real_bot', 'is_support', 'is_father', 'user_id')->where('user_id', $user_id)->first();
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
        public function createRequestCurlBot($url = '', $data = array()) {
            $client = new Client();
            try {
                // Wykonujemy zapytanie HTTP typu POST na serwer Lumen
                $response = $client->get($url, [
                    'form_params' => $data, // Wysyłamy dane w formacie x-www-form-urlencoded
                ]);
                
                // Odczytujemy odpowiedź z serwera Lumen
                return $response->getBody();
            }catch (RequestException $e) {
                // W przypadku błędu, wyświetlamy treść odpowiedzi z serwera (jeśli jest dostępna)
                if ($e->hasResponse()) {
                    $res = $e->getResponse()->getBody();
                    return $res;
                } else {
                    // Jeśli odpowiedź serwera jest niedostępna, możemy wyświetlić dowolny komunikat błędu
                    return callback_return(false, 500, 'Unknown error');
                }
            }
        }
        public function sendMessages($user_id, $token, checkedAccessToken $checkedAccessToken, Request $request) {
            // checkedAccessToken@createAccessToken
            $checked_bot = $this->index($user_id, $token)->getData();
            if (!$checked_bot->ok) {
                return callback_return($checked_bot->ok, $checked_bot->error_code, $checked_bot->description);
            }else {
                $generate_access_token = $checkedAccessToken->createAccessToken($checked_bot->description->user_id)->getData();

                if ($generate_access_token->ok) {
                    $messagesSender = new MessagesSender();
                    $access_tokens = $generate_access_token->description->access_token;
                    // $sendMessage_ = $messagesSender->sendMessage($access_tokens)->getData();
                    //$path = ?chat_id={$request->chat_id}&text={$request->text}&parse_mode={$request->parse_mode}";
                    $build_query = http_build_query(array(
                        "chat_id" => $request->chat_id,
                        "text" => $request->text,
                        "parse_mode" => $request->parse_mode,
                        "disable_web_page_preview" => $request->disable_web_page_preview
                    ));
                    if ($_SERVER['HTTP_HOST'] != 'localhost') {
                        $path_u = '';
                    }else {
                        $path_u = '/GITHUB/fastmess';
                    }
                    $send_m = json_decode($this->createRequestCurlBot(
                        'http://'.$_SERVER['HTTP_HOST'].$path_u.'/user/'.$access_tokens.'/sendMessage?'.$build_query, 
                    ), true);

                    return callback_return($send_m['ok'], $send_m['error_code'], $send_m['description']);
                    $checkedAccessToken->revokeAccessToken($access_tokens);
                }else {
                    return callback_return(false, 500, 'Invalid access_token');
                }
            }
        }
    }
