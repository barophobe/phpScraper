<?php
    // Defining the basic scraping function
    function scrape_between($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }

// Defining the basic cURL function
    function curl($url) {
        // Assigning cURL options to an array
        $options = Array(
            CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
            CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
            CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
            CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
            CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
            CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
            CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
        );
         
        $ch = curl_init();  // Initialising cURL 
        curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        curl_close($ch);    // Closing cURL 
        return $data;   // Returning the data from the function 
    }

   /* $scraped_page = curl("http://www.imdb.com");    // Downloading IMDB home page to variable $scraped_page
    $scraped_data = scrape_between($scraped_page, "<title>", "</title>");   // Scraping downloaded dara in $scraped_page for content between <title> and </title> tags
     
    echo $scraped_data;*/ // Echoing $scraped data, should show "The Internet Movie Database (IMDb)"
/*$url = "http://www.imdb.com/search/title?genres=action";*/
 $continue = TRUE;
$url = "http://www.imdb.com/search/title?genres=action&sort=user_rating,desc&title_type=feature&num_votes=25000,&pf_rd_m=A2FGELUUNOQJNL&pf_rd_p=2406822102&pf_rd_r=05EXZSVG77PEB40EN18Y&pf_rd_s=right-6&pf_rd_t=15506&pf_rd_i=top&ref_=chttp_gnr_1";    // Assigning the URL we want to scrape to the variable $url
    while ($continue == TRUE) {
    $results_page = curl($url); // Downloading the results page using our curl() funtion
     
    $results_page = scrape_between($results_page, "<div class=\"lister-list\">", "<div id=\"sidebar\">"); // Scraping out only the middle section of the results page that contains our results
     
    $separate_results = explode("div class=\"lister-item-content\">", $results_page);   // Exploding the results into separate parts into an array
         
    // For each separate result, scrape the URL
    foreach ($separate_results as $separate_result) {
        if ($separate_result != "") {
            $results_urls[] = "http://www.imdb.com" . scrape_between($separate_result, "href=\"", "\" title="); // Scraping the page ID number and appending to the IMDb URL - Adding this URL to our URL array
        }
    }

     // Searching for a 'Next' link. If it exists scrape the url and set it as $url for the next loop of the scraper
        if (strpos($results_page, "Next&nbsp;&#187;")) {
            $continue = TRUE;
            $url = scrape_between($results_page, "<div class=\"desc\">", "</div>");
            if (strpos($url, "Previous</a>")) {
                $url = scrape_between($url, "Previous</a>", ">Next");
            }
            $url = "http://www.imdb.com" . scrape_between($url, "href=\"", "\"");
        } else {
            $continue = FALSE;  // Setting $continue to FALSE if there's no 'Next' link
        }
        sleep(rand(3,5));   // Sleep for 3 to 5 seconds. Useful if not using proxies. We don't want to get into trouble.
    }
     
    print_r($results_urls); // Printing out our array of URLs we've just scraped

?>