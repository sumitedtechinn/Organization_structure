<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'C:/xampp/htdocs/edtech_organizational_structure/vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Predis\Client as RedisClient;

$obj = new Worker();

try {
    $obj->exceuteWorker();
} catch (Exception $e) {
    $obj->stepsLog .= "Error : " . $e->getMessage() . " line : " . $e->getLine(); 
} finally {
    $obj->saveLog();
}

class Worker {

    public $conn; 
    public $stepsLog; 
    public $request;
    public $sendEmailData;
    public $senderName = "Edtech";
    public $redis;

    public function __construct() {
        $this->redis = new RedisClient();
    }

    public function exceuteWorker() {

        try {
            $this->stepsLog .= date(DATE_ATOM) ." :: exceuteWorker method start to execute \n\n";
            if (!$this->redis) {
                $this->stepsLog .= date(DATE_ATOM) . " :: Redis connection error. Exiting worker. \n\n";
                return false;
            }
            $emptyCount = 0;
            $maxEmptyChecks = 5;
            while (true) {
                $this->sendEmailData = $this->redis->lpop('email_queue'); // Pop an email job from queue
                if ($this->sendEmailData) {
                    $this->stepsLog .= date(DATE_ATOM) ." :: Job run for => " . $this->sendEmailData. "\n\n";
                    $this->sendEmailData = json_decode($this->sendEmailData, true);
                    $this->sendMail();
                    // if(!$this->sendMail()) {
                    //     // Retry after 10sec
                    //     sleep(10);
                    //     $this->redis->lpush('email_queue',$this->sendEmailData);
                    // } 
                    $emptyCount = 0;
                } else {
                    $this->stepsLog .= date(DATE_ATOM) . " :: Queue is empty. Check Empty count => $emptyCount \n\n";
                    $emptyCount++;
                    if ($emptyCount >= $maxEmptyChecks) {
                        $this->stepsLog .= date(DATE_ATOM) . " :: Stopping worker after multiple empty checks.\n\n";
                        break;
                    }
                    sleep(5);
                }
            }
            return true;
        } catch (Exception $e) {
            $this->stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine();
            return false;
        }
    }

    public function sendMail() {

        $this->stepsLog .= date(DATE_ATOM) .  " :: Start Exceution of Send Mail function \n";
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no-reply@edtechinnovate.com';
            $mail->Password   = 'qftsisgdjjafqsvi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('noreply@yourdomain.com',$this->senderName);
            $mail->addAddress($this->sendEmailData['to']);
    
            if(!is_null($this->sendEmailData['cc']) && !empty($this->sendEmailData['cc'])) {
                $cc_email = explode(',',$this->sendEmailData['cc']);
                foreach ($cc_email as $value) {
                    $mail->addCC($value);        
                }
            }
            
            $mail->isHTML(true);
            $mail->Subject = $this->sendEmailData['subject'];
            $mail->Body    = $this->sendEmailData['body'];
            $mail->AltBody = strip_tags($this->sendEmailData['body']);
            
            $mail->send();
            
            $this->stepsLog .= date(DATE_ATOM) . " :: Mail send successfully \n\n";
            return true;
        } catch (Exception $e) {
            $this->stepsLog .= date(DATE_ATOM) . " :: Failed to send email. Mailer Error: ". $e->getMessage() ." \n\n";
            return false;
        }
    }


    public function saveLog() {
        
        $this->stepsLog .= " ============ End Of Script ================== \n\n";
        $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/mailWorker_log/';
        $this->deleteOldTicketLogs($pdf_dir);
        $fh = fopen($pdf_dir . 'worker_' . date('y-m-d') . '.log' , 'a');
        fwrite($fh,$this->stepsLog);
        fclose($fh);
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
