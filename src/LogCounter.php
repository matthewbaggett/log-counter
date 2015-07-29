<?php

namespace LogCounter;

class LogCounter{
    const apacheDateFormat = "D M d H:i:s.u Y";

    private $buckets;
    private $pids;
    private $errors;
    private $lines;
    private $errorCount;
    private $logLinesCount;

    private $showProgress = true;

    public function setShowProgress($showProgress){
        $this->showProgress = $showProgress;
    }

    public function run($apacheLog, $numberOfErrorsToDisplay = 10){
        $errorLog = file_get_contents($apacheLog);

        $logLines = explode("\n", $errorLog);

        $this->buckets = array();
        $this->pids = array();
        $this->errors = array();
        $this->lines = array();
        $this->errorCount = 0;

        $this->logLinesCount = count($logLines);

        echo "Parsing {$this->logLinesCount} log lines... \n";
        foreach($logLines as $i => $logLine){
            if($this->showProgress) {
                $percentage = (100 / $this->logLinesCount) * $i;
                $percentage = number_format($percentage);
                if (!isset($lastPercentage) || $percentage > $lastPercentage) {
                    $memory = number_format(memory_get_usage() / 1024 / 1024);
                    echo "\r > {$percentage}% {$memory} MB.";
                }
                $lastPercentage = $percentage;
            }
            $elements = explode("]", $logLine);
            foreach($elements as &$element){
                $element = str_replace("[", "", $element);
                $element = trim($element);
            }
            if(stripos($logLine, "PHP") !== false) {
                $line = new \StdClass();
                //Tue Jul 28 07:35:02.340153 2015

                if(count($elements) == 4) {
                    $line->error = $elements[3];
                }elseif(count($elements) == 5){
                    $line->error = $elements[4];
                }

                // Exclude PHP Stack traces.
                #echo $logLine . "\n > substr: " . substr($line->error,0,6) . "\n";
                if(substr($line->error,0,6) != 'PHP   ' && strpos($line->error, 'PHP Stack trace') === false){
                    $dateTime = \DateTime::createFromFormat(self::apacheDateFormat, $elements[0]);
                    $line->time = $dateTime;
                    $line->application = $elements[1];
                    $line->pid = $elements[2];
                    $line->hash = substr(md5($line->error),0,7);
                    #echo "Hash of \"{$line->error}\" is {$line->hash}\n";
                    $this->pids[$line->pid] = isset($this->errors[$line->pid]) ? $this->errors[$line->pid] + 1 : 1;
                    $this->errors[$line->hash] = isset($this->errors[$line->hash]) ? $this->errors[$line->hash] + 1 : 1;
                    $this->buckets[$line->application][$line->pid]array() = $line;

                    $this->lines[$line->hash] = $line;
                    $this->errorCount++;
                }

            }
        }
        echo"\n";
        echo"\n";

        asort($this->errors);
        $this->errors = array_reverse($this->errors);

        $sortedErrors = array();
        foreach($this->errors as $errorHash => $frequency){
            $sortedErrorsarray() = ['hash' => $errorHash, 'frequency' => $frequency];
        }


        echo "There were " . count($this->buckets) . " applications\n";
        echo " > Unique PIDs: " . count($this->pids) . "\n";
        echo " > Unique Errors: " . count($this->errors) . "\n";
        echo " > Total Errors: {$this->errorCount}\n";
        echo "\n";

        echo "Top {$numberOfErrorsToDisplay} errors:\n";
        foreach(array_slice($sortedErrors, 0, $numberOfErrorsToDisplay) as $error){
            $line = $this->lines[$error['hash']];
            echo " > {$line->hash} ({$error['frequency']} times) {$line->error}\n";
        }
    }
}