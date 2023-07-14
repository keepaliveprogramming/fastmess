<?php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Models\Link;

    class LinkController extends Controller
    {
        public function preview(Request $request, $link)
        {
            // Tworzenie obiektu linku na podstawie parametru $link
            $linkObject = new Link();
            $linkObject->url = $link;

            // Tutaj możesz dodać dodatkową logikę związaną z zapisem linku w bazie danych
            // np. walidację, sprawdzanie duplikatów, itp.

            // Zwróć odpowiedź w formacie JSON
            return response()->json(['link' => $linkObject]);
        }
    }


?>