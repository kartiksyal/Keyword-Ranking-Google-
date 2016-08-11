<?php

class ControllerKeywordRanking extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->library('curl');
    }

    function index() {
        log_message('debug', 'ControllerKeywordRanking:index');

        $this->load->model('ModelKeywordRanking');

		// Make a select Query and fetch data from a table which includes all the keywords of which you want ranks and put it into $arrFinalKeyword
			
        $arrFinalKeyword =""; //or you can test it with just a single keyword
        

        foreach ($arrFinalKeyword as $row) {

            $Keyword = $row->FINALKEYWORD;
            $userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2';

            $ch = curl_init($this->queryToUrl($Keyword, 0, 100, "CA"));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);
            echo $response;
            $rank = $this->HTMLParsing($response);

            $data = array(
                'KEYWORD' => $Keyword,
                'RANK' => $rank
            );
            $this->load->model('CoreEngine/CoreKeywordRanking');
            $this->CoreKeywordRanking->InsertKeywordRanking($data);

            $iSleepTime = rand(5, 8);
            log_message('debug','sleep: '.$iSleepTime);
            sleep($iSleepTime);
        }

       
    }

    function queryToUrl($query, $start = null, $perPage = 100, $country = "US") {
        log_message('debug', 'ControllerKeywordRanking:queryToUrl');
        return "http://www.google.com/search?" . http_build_query(array(
                    // Query
                    "q" => urlencode($query),
                    // Country (geolocation presumably)
                    "gl" => $country,
                    // Start offset
                    "start" => $start,
                    // Number of result to a page
                    "num" => $perPage
                        ), true);
    }

    function HTMLParsing($pStrResponse) {
        log_message('debug', 'ControllerKeywordRanking:HTMLParsing');
        # Use the Curl extension to query Google and get back a page of result
        # Create a DOM parser object
        $dom = new DOMDocument();

        # Parse the HTML from Google.
        # The @ before the method call suppresses any warnings that
        # loadHTML might throw because of invalid HTML in the page.
        @$dom->loadHTML($pStrResponse);

        # Iterate over all the <a> tags
        $iCounter = 1;
//        if($iCounter = 0){
//            $iCounter = null;
//        }
        foreach ($dom->getElementsByTagName('cite') as $link) {
            # Show the <a href>		
            $strLink = $link->nodeValue;
            if (strpos($strLink, 'carepur.com') != false) {
                echo 'Rank is ' . $iCounter;
                break;
            }
            $iCounter++;
        }
        return $iCounter;
    }


}