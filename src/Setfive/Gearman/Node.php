<?php

namespace Setfive\Gearman;

use Guzzle\Http\Client;
use phpQuery;

class Node extends Base {
        
    private function getUrlBody( $url ){
                        
        $options = [CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => $url, CURLOPT_TIMEOUT_MS => 1000,                    
                    CURLOPT_CONNECTTIMEOUT_MS => 1000, CURLOPT_SSLVERSION => 3];
                
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        
        $body = null;        
        $body = curl_exec($curl);
        
        curl_close($curl);        
        
        return $body;
        
        /** Guzzle kept inexplicably hanging so using straight cURL **/
        
        /*        
        $client = new Client();                        
        
        try{            
            $response = $client->get($url, ['connect_timeout' => 1, 'timeout' => 1, 'curl.options' => $options])->send();
            $body = $response->getBody();
        }catch(\Exception $ex){
            
        }
        
        return $body;
        */
    }    
    
    public function getKeywordsForUrl( $url ){
                
        $keywords = [ ];

        try {
                     
            $body = $this->getUrlBody( $url );

            if( !strlen($body) ){
                return $keywords;
            }

            $doc = phpQuery::newDocument($body);
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
