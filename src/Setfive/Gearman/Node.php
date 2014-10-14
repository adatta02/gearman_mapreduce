<?php

namespace Setfive\Gearman;

use Guzzle\Http\Client;
use phpQuery;

class Node extends Base {

    public function getKeywordsForUrl( $url ){
        $client = new Client();
        $keywords = [ ];

        try {

            $options = ["CURLOPT_TCP_NODELAY" => true, "CURLOPT_TIMEOUT_MS" => 500, 'CURLOPT_CONNECTTIMEOUT_MS' => 500];
            $response = $client->get($url, ['connect_timeout' => 1, 'timeout' => 1, 'curl.options' => $options])->send();
            
            $body = $response->getBody();

            if( !strlen($body) ){
                return $keywords;
            }

            $doc = phpQuery::newDocument($response);
            $matchedTags = $doc["meta[name='keywords'], meta[name='Keywords'], meta[name='description']"];

            foreach( $matchedTags as $meta ){            
                $kw = preg_split("/[\s,]+/", pq($meta)->attr("content"));
                $keywords = array_merge($keywords, $kw);                              
            }

        }catch(\Exception $ex){
            Logger::getLogger()->addError($ex->getMessage());
        }

        $keywords = array_unique($keywords);
        return $keywords;
    }

    public function processUrl( $payload ){

        Logger::getLogger()->addInfo("Trying " . $payload["url"]);

        $payload["keywords"] = $this->getKeywordsForUrl( $payload["url"] );

        $this->getGearmanClient()->doBackground("countKeywords", json_encode($payload));
    }

    public function getAvailableJobs(){
        return ["processUrl"];
    }

}