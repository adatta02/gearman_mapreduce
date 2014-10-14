<?php

require_once dirname(__FILE__) . "/../vendor/autoload.php";

use Setfive\Gearman\Logger;
use Guzzle\Http\Client;

$handle = fopen( dirname(__FILE__) . "/100sites.txt", "r+" );
$keywordCounts = [];
$start = time();

while( ($line = fgets($handle)) ){

    $url = "http://" . trim($line);
    $keywords = getKeywordsForUrl($url);

    Logger::getLogger()->addInfo("Fetching " . $url);

    foreach( $keywords as $keyword ){

        $keyword = strtolower(trim($keyword));

        if( strlen($keyword) < 5 ){
            continue;
        }

        if( !array_key_exists($keyword, $keywordCounts) ){
            $keywordCounts[ $keyword ] = 0;
        }

        $keywordCounts[ $keyword ] += 1;
    }

    arsort($keywordCounts);

    $targetFile = dirname(__FILE__) . "/nogearman_keyword_results.json";
    file_put_contents( $targetFile, json_encode($keywordCounts) );
}

$totalTime = time() - $start;
Logger::getLogger()->addInfo("Total time: " . $totalTime);

function getKeywordsForUrl( $url ){

    $client = new Client();
    $keywords = [ ];

    try {

        $response = $client->get($url, ['connect_timeout' => 1, 'timeout' => 1])->send();
        $body = $response->getBody();

        if( !strlen($body) ){
            return $keywords;
        }

        $doc = phpQuery::newDocument($response);
        $matchedTags = $doc["meta[name='keywords'], meta[name='Keywords'], meta[name='description']"];

        foreach( $matchedTags as $meta ){
            $keywords = array_merge( $keywords, explode(" ", pq($meta)->attr("content")) );
        }

    }catch(\Exception $ex){
        Logger::getLogger()->addError($ex->getMessage());
    }

    $keywords = array_unique($keywords);
    return $keywords;

}