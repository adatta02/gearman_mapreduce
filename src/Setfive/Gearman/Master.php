<?php

namespace Setfive\Gearman;

class Master extends Base {

    private $keywordCounts = [];
    protected $exitOnZero = true;    
    
    public function countKeywords($payload, $outputFile = "keyword_results.json"){
        
        if( $this->startedAt == null ){
          $this->startedAt = time();
        }
        
        // Logger::getLogger()->addInfo( json_encode($payload) );

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

        $targetFile = dirname(__FILE__) . "/../../../bin/" . $outputFile;
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