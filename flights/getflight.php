<?php
        header('Content-Type: text/plain');
		
        $header = array(
                "GET /zones/fcgi/feed.js?bounds=55.9142084705325,47.74010429497699,-10.8984375,2.7685546875&faa=1&mlat=1&flarm=1&adsb=1&gnd=1&air=1&vehicles=1&estimated=1&maxage=900&gliders=1&stats=1 HTTP/1.1",
                "Host: arn.data.fr24.com",
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0',
                'Accept: */*',
                'Accept-Language: en-gb,en;q=0.5',
                'Accept-Encoding: gzip',
                "Referer: http://www.flightradar24.com",
                'Connection: keep-alive',
                'Pragma: no-cache',
                'Cache-Control: no-cache'
        );

        $response = SendGetRequest(array('url'=>'http://arn.data.fr24.com/zones/fcgi/feed.js?bounds=55.9142084705325,47.74010429497699,-10.8984375,2.7685546875&faa=1&mlat=1&flarm=1&adsb=1&gnd=1&air=1&vehicles=1&estimated=1&maxage=900&gliders=1&stats=1','header'=>$header));

		$json = json_decode($response['body']);
        echo json_encode($json);
        	
        function SendGetRequest($http_request_header) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_VERBOSE, 1);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, $http_request_header['url']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $http_request_header['header']);

                $response = curl_exec($curl);

                if ($response===false) {
                        return array('header'=>'', 'body'=>'', 'error'=>curl_error($curl));
                }

                $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
                $http_response_header = substr($response, 0, $header_size);
                $body = UnzipContent(substr($response, $header_size));

                $header_values = explode("\r\n", $http_response_header);
                $header_values = array_map(function($value){return htmlentities($value);}, $header_values);

                return array('header'=>$header_values, 'body'=>$body, 'error'=>false);
        }

        function UnzipContent($content) {
                $decompress = @gzinflate($content);
                if ($decompress===false) {
                        $decompress = @gzinflate(substr($content,10,-8));
                        if ($decompress===false) {
                                return $content;
                        }
                } 
                return $decompress;
        }       
?>