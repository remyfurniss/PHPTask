<?php
    include_once 'ScraperFunctions.php';

    //get the html returned from the url
    $html = file_get_contents('https://videx.comesconnected.com/');

    //create a new DOMDocument
    $videx_doc = new DOMDocument();

    //disable libxml errors
    libxml_use_internal_errors(TRUE);

    if(!empty($html)){ //if any html is actually returned

        //load html into doc
        $videx_doc->loadHTML($html);

        //remove errors for html
        libxml_clear_errors();

        //Create new DOMX path for the doc
        $videx_xpath = new DOMXPath($videx_doc);

        //Get all the subscriptions as a JSON values in an array
        $subscriptions = ScraperFunctions::getSubscriptionsAsJSONArray($videx_xpath);

        //Print all the JSON values
        print_r($subscriptions);
    }
?>



