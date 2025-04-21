<?php

use Firebase\JWT\JWT;

require '../../vendor/autoload.php';

class CreateMailStructure {

    public $to;
    public $cc = null;
    public $mailSubject;
    public $mailBody;
    public $baseUrl;

    public function __construct() {
        $this->baseUrl = $this->createBaseURL();
    }

    public function setData($mailBody,$mailSubject,$to,$cc) {
        $this->to = $to;
        $this->cc = $cc;
        $this->mailSubject = $mailSubject;
        $this->mailBody = $mailBody;
        return $this;
    }

    public function toArray() : array {
        return [$this->mailBody,$this->mailSubject,$this->to,$this->cc];
    }

    public function messageForTicketCloseConfirmation($ticket_info_data,$emailData) {
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "$ticketQniqueId Close By client";
        $mailBody = "
        <p>Hello ,</p>
        <div>The client has closed the ticket <strong>$ticketQniqueId</strong> that you were working on.</div>
        <div>If any further follow-up is needed, please coordinate accordingly.</div>
        <br>
        <div>Best Regards</div>
        <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    public function messageToDepartmentHeadAboutTicketReopen($ticket_info_data,$emailData) {
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "Ticket $ticketQniqueId Reopened by Client";
        $mailBody = "
        <p>Dear Manager,</p>
        <div>The client has reopened ticket <strong>$ticketQniqueId</strong> .</div>
        <h4>Client's Query/Message:</h4>
        <p>$clientQueryMessage</p>
        <p>Please review the ticket and proceed accordingly.</p>
        <div>Best Regards</div>
        <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    public function messageToCreateUserForReviewTicket($ticket_info_data,$emailData) {
        
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        } 
        $mailSubject = "Action Required: Please Review Your Ticket - $ticketQniqueId";
        $url = $this->baseUrl."/app/tickets/createTicketReviewMessage"; 
        $close_url = $this->baseUrl."/app/tickets/showCloseTicketResponse?token=". $this->generateJWTToken($ticket_id,"updateTicketStatusReviewToClose");
        $reopen_url = $this->baseUrl."/api/ticket/ReopenTicketReviewMessage?token=" . $this->generateJWTToken($ticket_id,"insertReopenTicketQuery");
        $request = [];
        $request['ticketQniqueId'] = $ticketQniqueId;
        $request['createdPersonName'] = $createdPersonName;
        $request['ticket_id'] = $ticket_id; 
        $request['close_url'] = $close_url;
        $request['reopen_url'] = $reopen_url;
        $request = json_encode($request);
        $opt = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $request , 
                'timeout' => 60
            )
        );
        $context = stream_context_create($opt);
        $mailBody = file_get_contents($url,false,$context);
        return $this->setData($mailBody,$mailSubject,$to,$cc); 
    }
    
    public function generateJWTToken($ticket_id, $method = null)
    {
        $key = "edtechTicket";
        $payload = [
            "ticket_id" => $ticket_id,
            "method" => $method,
            "iat" => time(),
            "exp" => time() + (2 * 24 * 60 * 60)
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    public function messageToCreateUserForTicketAssignedDetails($ticket_info_data,$emailData) {
        
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "Ticket Has Been Assigned - $ticketQniqueId";
        $mailBody ="
            <p>Dear $createdPersonName,<p>
            <p>Your ticket has been successfully assigned. Please find the ticket details below</p>
            <p>Ticket Details:</p>
            <div><b>- Ticket ID: </b>$ticketQniqueId</div>
            <div><b>- Subject: </b>$ticketSubject</div>
            <div><b>- Priority: </b> $ticketPriority</div>
            <div><b>- Assigned To: </b> $assignToUser</div>
            <div><b>- Date Assigned: </b>$assignDate</div>
            <br>
            <div>Best Regards</div>
            <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    public function messageTicketAssignUser($ticket_info_data,$emailData) {
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "New Ticket Assigned: [Ticket $ticketQniqueId]";
        $mailBody ="
            <p>Dear $assignToUser,<p>
            <p>A new support ticket has been assigned to you. Please review the details below:</p>
            <p>Ticket Details:</p>
            <div><b>- Ticket ID: </b>$ticketQniqueId</div>
            <div><b>- Subject: </b>$ticketSubject</div>
            <div><b>- Priority: </b> $ticketPriority</div>
            <div><b>- Assigned By: </b> $assignByUser</div>
            <div><b>- Date Assigned: </b>$assignDate</div>
            <br>
            <div>Best Regards</div>
            <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    
    public function messageToDepartHeadForNewTicketCreate($ticket_info_data,$emailData) {
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "Action Required: New Ticket Created - $ticketQniqueId";
        $mailBody = "
        <p>Dear Manager,</p>
        <p>This is to inform you that a new ticket (Reference ID: <b>$ticketQniqueId</b>) has been created by <b>$raisedByPersonName</b> regarding $ticketSubject.</p>
        <p>Ticket Details:</p>
        <div><b>- Ticket ID: </b>$ticketQniqueId</div>
        <div><b>- Subject: </b>$ticketSubject</div>
        <div><b>- Created On: </b>$createdTime</div>
        <p>Kindly review and assign this ticket to the appropriate team member for further handling.</p>
        <div>Best Regards</div>
        <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    public function successfulTicketGenerationMessageForTicketRaisedPerson($ticket_info_data,$emailData) {
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "Ticket Created Successfully Reference - $ticketQniqueId";
        $mailBody = "<p>Dear $raisedByPersonName,</p>
            <p>Thank you for reaching out to our support team.</p>
            <div>We have received your ticket (Reference ID: $ticketQniqueId) regarding <b>$ticketSubject</b>.</div>
            <div>Our team is currently reviewing your request and will get back to you as soon as possible.</div>
            <br>
            <div>Best Regards</div>
            <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    public function messageForAutoCloseTicket($ticket_info_data,$emailData) {
        extract($ticket_info_data);
        $to = ""; $cc = "";
        if(is_array($emailData)) {
            $to = $emailData['to'];
            $cc = $emailData['cc']; 
        } else {
            $to = $emailData;
            $cc = null;
        }
        $mailSubject = "Ticket Has Been Closed Due to Inactivity - $ticketQniqueId";
        $mailBody = "<p>Dear $raisedByPersonName,</p>
            <p>We wanted to let you know that your ticket (Reference ID: $ticketQniqueId) has been automatically closed because there was no response from you in the last 2 days while it was in review.</p>
            <div>If you still need assistance, feel free to contact or create a new one at any time.</div>
            <br>
            <div>Best Regards</div>
            <div>EDTECH Innovate Pvt. Ltd.</div>
        ";
        return $this->setData($mailBody,$mailSubject,$to,$cc);
    }
    
    public function createBaseURL() : string {
        $serverName = $_SERVER['SERVER_NAME'];
        $httpRequest = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
        return $httpRequest.$serverName;
    }
}

?>
