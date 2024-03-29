<?php

class MyLog {

    const LOG_FILE = "log.txt";

    public function write($text){
        $logMessage = Date("y-m-d H:i:s") . " | ". $text;
        file_put_contents(self::LOG_FILE, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function read(){
        return file_get_contents(self::LOG_FILE);
    }

    public function readBackwards($limit){
        $input = file(self::LOG_FILE);
        $rev_input = array_reverse($input);
        $text = "";
        $counter = 0;
        foreach ($rev_input as $line) {
            $text .= trim($line) . '<br>';
            $counter++;
            if ($counter >= $limit) return $text;
        }
        return $text;

/*
        $handle = fopen(self::LOG_FILE, "r");
        $backwardsText = "";
        $counter = 0;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $backwardsText = $line . $backwardsText;
                $counter++;
            }
            fclose($handle);
        } else {
            // error opening the file.
        }
*/
        //return $backwardsText;
    }

}