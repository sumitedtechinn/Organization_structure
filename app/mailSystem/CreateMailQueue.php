<?php

require '../../vendor/autoload.php';
//include '../../includes/db-config.php';
//session_start();

use Predis\Client as RedisClient;

$obj = new CreateMailQueue();
$obj->request = file_get_contents('php://input');
$obj->request = json_decode($obj->request,true);
$obj->stepsLog .= date(DATE_ATOM) . " :: request data => " . json_encode($obj->request) . "\n\n";
try {
    if(empty($obj->request)) {
        $obj->showResponse(false,"Empty request found!!");
    }
    $obj->request = $obj->request['data'];
    $obj->insertEmailDataInQueue();
} catch (Exception $e) {
    $obj->showResponse(false,"Error : " . $e->getMessage() . " line : " . $e->getLine()); 
} finally {
    $obj->saveLog();
}

class CreateMailQueue {

    public $conn;
    public $request;
    public $finalRes;
    public $redis;
    public $stepsLog = "";

    public function __construct() {

        $this->redis = new RedisClient();
    }

    public function insertEmailDataInQueue() {
        $this->stepsLog .=  date(DATE_ATOM) . " :: Inside => insertEmailDataInQueue() \n\n";
        try {
            foreach ($this->request as $emaildata) {
                $payload = json_encode([
                    'cc' => $emaildata['cc'] ?? null, 
                    //'to' => $emaildata['to'],
                    'to' => "sumitpathak901@gmail.com" ,  
                    'subject' => $emaildata['subject'],
                    'body' => $emaildata['body']
                ]);
                $this->stepsLog .= date(DATE_ATOM) . " :: data to push => $payload \n\n";
                $this->redis->rpush('email_queue', $payload);
            }
            //$output = shell_exec('php app/mailSystem/Worker.php');
            $this->showResponse(true,"Worker Start");
        } catch (Exception $e) {
            $this->showResponse(false,"Error : " . $e->getMessage() . " on file : " . $e->getFile() . " line : " . $e->getLine());
            echo json_encode($this->finalRes);
            die;
        }
    }

    public function showResponse($response, $message = "Something went wrong!") {
        $this->finalRes = ($response) ? ['status' => 200, 'message' => "$message successfully!"] : ['status' => 400, 'message' => $message]; 
        $this->stepsLog .= date(DATE_ATOM) . " :: respose => " . json_encode($this->finalRes) . "\n\n";
    }

    public function saveLog() {
        
        $this->stepsLog .= " ============ End Of Script ================== \n\n";
        $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/mailWorker_log/';
        $this->deleteOldTicketLogs($pdf_dir,5);
        $fh = fopen($pdf_dir . 'CreateMailQueue_' . date('y-m-d') . '.log' , 'a');
        fwrite($fh,$this->stepsLog);
        fclose($fh);
        echo json_encode($this->finalRes);
        exit;
    }

    public function deleteOldTicketLogs($logDir, $daysOld = 5) {
        $maxFileAge = $daysOld * 86400; // 5 days in seconds
        if (!is_dir($logDir)) return;
        foreach (glob($logDir . '/createTicket_*.log') as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
                unlink($file);
            }
        }
    }
}

?>