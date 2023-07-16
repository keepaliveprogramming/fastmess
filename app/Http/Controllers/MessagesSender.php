<?php
    
    namespace App\Http\Controllers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use App\Http\Controllers\checkedAccessToken;
    class MessagesSender extends Controller
    {
        
        public function chechedChatId($user_id_chats = '', $user_id = '') {
            $chat_check = DB::table('chats')->where(function ($query) use ($user_id, $user_id_chats) {
                $query->where('user_id', '=', $user_id)
                    ->where('user_id_chats', '=', $user_id_chats)
                    ->orWhere('user_id_chats', '=', $user_id)
                    ->where('user_id', '=', $user_id_chats);
            })->first();
            if (!$chat_check) {
                return callback_return(false, 404, 'Chat not found');
            }else {
                return callback_return(true, 200, $chat_check);
            }
        }
        public function getChats($user_id) {
            $getChat = DB::table('chats')->select()->where()->get();
        }
        public function id_message($chat_id) {
            $count = DB::table('messages_chats')->where('chat_id', $chat_id)->count();
            return $count;
        }
        public function sendMessage($access_token, Request $request, checkedAccessToken $checkedAccessToken) {
            $id_mess = array();
            
            $check = $checkedAccessToken->index($access_token)->getData();
            $check_chat = $this->chechedChatId($request['chat_id'], $check->description->user_id)->getData();
            $parse_mode = $request['parse_mode'];

            $user_id = $check->description->user_id;
            $chat_id = $request['chat_id'];
            $com_chat_id = $chat_id != $user_id ? $chat_id : $user_id;
            if (!$check->ok) {
                return callback_return($check->ok, $check->error_code, $check->description);
            }else if (!$request['chat_id']) {
                return callback_return(false, 400, 'Missing required parametr chat_id');
            }else if (!$check_chat->ok) {
                return callback_return($check_chat->ok, $check_chat->error_code, $check_chat->description);
            }else if (!$request['text']) {
                return callback_return(false, 400, 'Missing required parametr text');
            }else {
                $text = $request['text'];
                $id_mess = $this->id_message($check_chat['chat_id']);
                
                $insertData = array(
                    "id_mess" => NULL,
                    "id_messages" => $id_mess, 
                    "chat_id" => $check_chat['chat_id'],
                    "user_id" => $user_id,
                    "content" => $text,
                    "dt_add" => time(),
                    "dt_send" => time(),
                    "parse_mode" => $parse_mode
                );
                $insert_db = DB::table('messages_chats')->insert($insertData);
                return callback_return(true, 200, array(
                    "text" => $text,
                    "dt_add" => $insertData['dt_add'],
                ));
            }
        }

        public function sendM() {}
    }
