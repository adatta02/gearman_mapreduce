<?php

namespace Setfive\Gearman;

class Master extends Base {

    private $keywordCounts = [];

    public function countKeywords($payload){

        Logger::getLogger()->addInfo( json_encode($payload) );

        foreach( $payload["keywords"] as $keyword ){

            $keyword = strtolower(trim($keyword));

            if( strlen($keyword) < 5 ){
                continue;
            }

            if( !array_key_exists($keyword, $this->keywordCounts) ){
                $this->keywordCounts[ $keyword ] = 0;
            }

            $this->keywordCounts[ $keyword ] += 1;
        }

        arsort($this->keywordCounts);

        $targetFile = dirname(__FILE__) . "/../../../bin/keyword_results.json";
        file_put_contents( $targetFile, json_encode($this->keywordCounts) );
    }

    public function queueUrlForKeywords( $url ){
        Logger::getLogger()->addInfo("Queuing " . $url);
        $this->getGearmanClient()->doBackground("processUrl", json_encode(["url" => $url]));
    }

    public function getAvailableJobs(){
        return ["countKeywords"];
    }

}