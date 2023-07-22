<?php
    
    namespace App\Http\Controllers;
    use DOMDocument;

    class ParserDomUrl extends Controller
    {
        public function parseLinkPreview($url = '') {
            if (!$url) {
                return callback_return(false, 400, 'No link');
            }else {
                $data = array();
                
                // Pobierz zawartość strony za pomocą cURL lub funkcji file_get_contents
                // Tutaj użyjemy cURL do pobrania zawartości
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                
                // Wykorzystaj bibliotekę DOMDocument do analizy HTML i pobrania danych meta
                $doc = new \DOMDocument();
                libxml_use_internal_errors(true); // Wyłącz raportowanie błędów
                
                if ($doc->loadHTML($response)) {
                    // Pobierz tytuł strony
                    $titleNodes = $doc->getElementsByTagName('title');
                    if ($titleNodes->length > 0) {
                        $data['title'] = $titleNodes->item(0)->textContent;
                    }
                    
                    // Pobierz metadane strony - znaczniki meta z atrybutami name i property
                    $metaTags = $doc->getElementsByTagName('meta');
                    foreach ($metaTags as $metaTag) {
                        $name = $metaTag->getAttribute('name');
                        $property = $metaTag->getAttribute('property');
                        $content = $metaTag->getAttribute('content');
                        
                        if (!empty($name)) {
                            $data[$name] = $content;
                        } elseif (!empty($property)) {
                            $data[$property] = $content;
                        }
                    }

                    $data['url'] = $url;
                
                }
                
                // Zwróć dane jako tablicę
                return callback_return(true, 200, $data);
            }
        }

    }