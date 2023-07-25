<?php
  
  namespace App\Http\Controllers;
  
  use Illuminate\Http\Request;
  use GuzzleHttp\Client;

  class Upload extends Controller
  {
    private $uploaderURL = 'https://usercontent.pl/supload.php';

    private $name = 'photo';

    public function index(Request $request) {
        $destinationUrl = $this->uploaderURL;

        // Pobieramy ścieżkę do załadowanego pliku tymczasowego na serwerze Lumen
        $tmpImagePath = $request->file($this->name)->getPathname();

        // Tworzymy klienta Guzzle
        $client = new Client();

        // Wysyłamy obraz za pomocą klienta Guzzle jako plik w formularzu
        $response = $client->post($destinationUrl, [
            'multipart' => [
                [
                    'name' => $this->name, // Nazwa pola formularza na serwerze docelowym
                    'contents' => fopen($tmpImagePath, 'r'), // Otwieramy plik jako strumień danych
                    'filename' => $request->file($this->name)->getClientOriginalName(), // Oryginalna nazwa pliku
                ],
            ],
        ]);

        // Odpowiedź z serwera docelowego jest zazwyczaj JSON-em, możesz ją przetworzyć lub zwrócić ją jako odpowiedź w Lumen
        return $response->getBody();
    }

    public function uploaderForUrl(Request $request) {
        //$request = new Request();
        $destinationUrl = $this->uploaderURL;

        
        $fileContent = file_get_contents($request->url);

        

        // Tworzenie cURL handle
        $ch = curl_init();

        // Ustawienie opcji cURL
        curl_setopt($ch, CURLOPT_URL, $destinationUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [ $this->name => $fileContent]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Wykonanie żądania cURL
        $result = curl_exec($ch);

        // Zamknięcie połączenia cURL
        curl_close($ch);

        // Odpowiedź z serwera docelowego jest zazwyczaj JSON-em, możesz ją przetworzyć lub zwrócić ją jako odpowiedź w Lumen
        return callback_return(true, 200, array(
            "upload" => $result,
            "self" => $request->all()
        ));
    }
  }