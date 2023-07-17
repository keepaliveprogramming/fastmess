<?php
    namespace App\Http\Controllers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
    use App\Http\Controllers\checkedAccessToken;

    class Channels extends Controller
    {
        protected $transport;

        public function createChannels(Request $request, checkedAccessToken $checkedAccessToken)
        {
            $name_channels = $request['name_channels'];
            $description_channels = $request['description_channels'];
            $check_token = $checkedAccessToken->index($request['access_token'])->getData();
            if (!$check_token->ok) {
                return $check_token;
            }else if (!$name_channels) {
                return callback_return(false, 400, 'Missing required parametr name_channels');
            }else {
                $id_channels = rand().rand();
                $inData = array(
                    "id_channels" => $id_channels,
                    "name_channels" => $name_channels,
                    "dt_add" => time()
                );
                $add = DB::table('channels')->insert($inData);
                return callback_return(true, 200, array(
                    "name_channels" => $name_channels,
                    "id_channels" => $id_channels,
                    "owner" => $check_token->description->user_id
                ));
            }
        }
        
    }

?>