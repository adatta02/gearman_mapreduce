<?php

namespace Setfive\Gearman;

use Guzzle\Http\Client;
use phpQuery;

class Node extends Base {

    public function getKeywordsForUrl( $url ){
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

    public function processUrl( $payload ){

        Logger::getLogger()->addInfo("Trying " . $payload["url"]);

        $payload["keywords"] = $this->getKeywordsForUrl( $payload["url"] );

        $this->getGearmanClient()->doBackground("countKeywords", json_encode($payload));
    }

    public function getAvailableJobs(){
        return ["processUrl"];
    }

}