<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../includes/db-config.php';
require '../../vendor/autoload.php';

class ValidateJWTToken {

    public $key = "edtechTicket";
    public $jwt ;
    public $stepsLog;
    public $tokenResponse;
    public $finalRes;
    public $conn;

    public function __construct($request,$conn = null) {
        $this->jwt = $request;
        $this->conn = $conn;
    }

    public function decodeJWTToken() {

        $this->stepsLog .= date(DATE_ATOM) . " :: Inside the decodeJWTToken \n\n";
        try {
            $this->tokenResponse = JWT::decode($this->jwt, new Key($this->key, 'HS256'));
            $this->stepsLog .= date(DATE_ATOM) . " :: Data came in token => ". json_encode((array)$this->tokenResponse) ." \n\n";
            $this->tokenResponse = (array) $this->tokenResponse;
            return $this->tokenResponse;
        } catch (Exception $e) {
            return $this->showResponse(false,"Token is invalid","Error : ".$e->getMessage());
        }
    }
    
    public function checkExpireDateAndTime() {
        
        $this->stepsLog .= date(DATE_ATOM) . " :: Inside the checkExpireDateAndTime \n\n";
        $tokenExpireDateAndTime = date("d-m-Y H:i:s", $this->tokenResponse['exp']);
        $currentDateAndTime = date("d-m-Y H:i:s");
        $this->stepsLog .= date(DATE_ATOM) . " :: expireDateAndTime => $tokenExpireDateAndTime and currentTime => $currentDateAndTime \n\n";
        $expireTimestamp = strtotime($tokenExpireDateAndTime);
        $currentTimestamp = strtotime($currentDateAndTime);
        $timeDifference =  $expireTimestamp - $currentTimestamp;
        $this->stepsLog .= date(DATE_ATOM) . " :: timeDifference between current and expire => $timeDifference \n\n";
        if($timeDifference < 0) {
            return $this->showResponse(false,"Token Expire","Please login to portal for see all details.");
        } else {
            return $this->showResponse(true,"Please Wait..","Please login to portal for see all details.",$this->tokenResponse['ticket_id'],$this->tokenResponse['method']);
        }
    }

    public function checkTicketReopenStatus($ticket_id) {

        $checkTicketReopenStatus_query = "SELECT IF(reopenStatus = '1','open','close') as `status` FROM ticket_record WHERE id = '$ticket_id'";
        $checkTicketReopenStatus = $this->conn->query($checkTicketReopenStatus_query);
        $checkTicketReopenStatus = mysqli_fetch_column($checkTicketReopenStatus);
        if($checkTicketReopenStatus == 'open') {
            $title = "";
            if($this->tokenResponse['method'] == 'updateTicketStatusReviewToClose') {
                $title = "Ticket is Re-open state close not possible";
            } else {
                $title = "Ticket Successfully Re-open";
            }
            return $this->showResponse(false,$title,"Please login to portal for see all details.");
        } else {
            return $this->showResponse(true,"Please Wait..","Please login to portal for see all details.",$this->tokenResponse['ticket_id'],$this->tokenResponse['method']);
        }
    }

    public function showResponse($response,$title,$messsage,$ticket_id=null,$action=null) {
        $this->finalRes = ($response) ? ['status'=>200,'title'=>$title,'message'=>$messsage,'ticket_id' => $ticket_id,'method'=>$action] : ['status'=>400,'title'=>$title,'message'=>$messsage,'ticket_id' => $ticket_id,'method'=>$action]; 
        return $this->finalRes;
    }

}

?>