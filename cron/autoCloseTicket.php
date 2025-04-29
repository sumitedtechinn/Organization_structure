<?php

## Database configuration
include '../../includes/db-config.php';
require '../../app/mailSystem/MailJob.php';
require '../../app/mailSystem/CreateMailStructure.php';

$stepsLog = "";
$baseUrl = BASE_URL;
try {
    $stepsLog .= date(DATE_ATOM) . " :: AutoCloseTicket CRON Start to execut \n\n";
    checkTicketforAutoClose();
} catch (Exception $e) {
    $stepsLog .= date(DATE_ATOM) . " :: Error => " . $e->getMessage() . " on file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
} finally {
    saveLog();
}

function checkTicketforAutoClose() {

    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the checkTicketforAutoClose \n\n";
    try{
        $checkTicketIdPresentInReview_query = "SELECT ticket_record.id as `ID` FROM ticket_record LEFT JOIN ticket_status ON ticket_status.id = ticket_record.status WHERE ticket_status.name LIKE ('%review%') AND (ticket_record.reopenStatus = '0' OR ticket_record.reopenStatus IS NULL)";
        $stepsLog .= date(DATE_ATOM) . " checkTicketIdPresentInReview_query  => $checkTicketIdPresentInReview_query \n\n";
        $checkTicketIdPresentInReview = $conn->query($checkTicketIdPresentInReview_query);
        if($checkTicketIdPresentInReview->num_rows > 0) {
            $checkTicketIdPresentInReview = mysqli_fetch_all($checkTicketIdPresentInReview,MYSQLI_ASSOC);
            $checkTicketIdPresentInReview = implode(",",array_column($checkTicketIdPresentInReview,"ID"));
            $stepsLog .= date(DATE_ATOM) . " :: response from checkTicketIdPresentInReview query => $checkTicketIdPresentInReview \n\n"; 
            $checkReviewTicketTimePeriod_query = "SELECT th.ticket_id FROM ticket_history th LEFT JOIN ticket_status ts ON ts.id = th.status INNER JOIN (SELECT ticket_id, MAX(id) AS latest_id FROM ticket_history GROUP BY ticket_id) latest ON latest.ticket_id = th.ticket_id AND latest.latest_id = th.id WHERE th.ticket_id IN (" . $checkTicketIdPresentInReview . ") AND ts.name LIKE '%review%' AND TIMESTAMPDIFF(DAY, th.created_at,NOW()) > '3'";

            $stepsLog .= date(DATE_ATOM) . " :: checkReviewTicketTimePeriod_query =>  $checkReviewTicketTimePeriod_query \n\n"; 
            $checkReviewTicketTimePeriod = $conn->query($checkReviewTicketTimePeriod_query);
            if($checkReviewTicketTimePeriod->num_rows > 0) {

                $checkReviewTicketTimePeriod = mysqli_fetch_all($checkReviewTicketTimePeriod,MYSQLI_ASSOC);
                $checkReviewTicketTimePeriod = array_column($checkReviewTicketTimePeriod,"ticket_id");

                updateTicketStatus($checkReviewTicketTimePeriod);       
            } else {
                $stepsLog .= date(DATE_ATOM) . " :: Ticket present in review state but time period is not exceed more then 3 day. \n\n";
            }
        } else {
            $stepsLog .= date(DATE_ATOM) . " :: No ticket on the Review State \n\n";
        }
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Error => " . $e->getMessage() . " on file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
    }
}

function updateTicketStatus($ticketIdsArr) {

    global $stepsLog , $baseUrl;

    $stepsLog .= date(DATE_ATOM) . " :: method inside the updateTicketStatus \n\n";
    $url = $baseUrl ."/app/tickets/storeAndupdateTicket";
    foreach ($ticketIdsArr as $ticket_id) {
        try {
            $request = [];
            $request['ticket_id'] = $ticket_id; 
            $request['method'] = "updateTicketStatusReviewToClose";
            $request['requestFromAutoClose'] = true;
            $request = json_encode($request);
            $stepsLog .= date(DATE_ATOM) . " :: REQUEST URL => $url , Request Data => $request \n\n";
            $opt = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $request , 
                    'timeout' => 60
                )
            );
            $context = stream_context_create($opt);
            $response = file_get_contents($url,false,$context);
            $stepsLog .= date(DATE_ATOM) . " :: Response => $response \n\n";   
        } catch (Exception $e) {
            $stepsLog .= date(DATE_ATOM) . " :: Error => " . $e->getMessage() . " on file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
        }
    }
}

function saveLog() {
        
    global $stepsLog;

    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ticket_log/';
    deleteOldTicketLogs($pdf_dir,5);
    $fh = fopen($pdf_dir . 'autoCloseCron_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    //echo json_encode($finalRes);
    exit;
}

function deleteOldTicketLogs($logDir, $daysOld = 5) {
    $maxFileAge = $daysOld * 86400; // 5 days in seconds
    if (!is_dir($logDir)) return;
    foreach (glob($logDir . '/autoCloseCron_*.log') as $file) {
        if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
            unlink($file);
        }
    }
}
?>