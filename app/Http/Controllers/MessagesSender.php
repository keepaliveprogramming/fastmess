<?php
    
    namespace App\Http\Controllers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use App\Http\Controllers\checkedAccessToken;
    class MessagesSender extends Controller
    {
        
        public function chechedChatId($chat_id = '') {
            $chat_check = DB::table('chats')->where('chat_id', $chat_id)->first();
            if (!$chat_check) {
                return callback_return(false, 404, 'Chat not found');
            }else {
                return callback_return(true, 200, $chat_check);
            }
        }
        public function sendMessage($access_token, Request $request, checkedAccessToken $checkedAccessToken) {
            $check = $checkedAccessToken->index($access_token)->getData();
            $check_chat = $this->chechedChatId($request['chat_id'])->getData();
            if (!$check->ok) {
                return callback_return(false, 401, 'Unauthorized');
            }else if (!$request['chat_id']) {
                return callback_return(false, 400, 'Missing required parametr chat_id');
            }else if (!$check_chat->ok) {
                return callback_return($check_chat->ok, $check_chat->error_code, $check_chat->description);
            }else {
                $insertData = array(
                    "id_mess" => NULL,
                    "id_message" => rand(),
                    "text" => $text,
                    "date" => time(),
                    "is_edit" => 0,
                    "for_user_id" => $for_user_id,
                    "from_user_id" => $from_user_id,
                    "history_user_id" => $history_user_id
                );
                $insert_db = DB::table('messages_in_chats')->insert($insertData);
            }
        }

        public function sendM() {}
    }
