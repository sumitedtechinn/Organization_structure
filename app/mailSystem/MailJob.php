<?php
class MailJob {

    public $to;
    public $cc = null;
    public $subject;
    public $body;

    public function setData($to,$cc,$subject,$body) {
        $this->to = $to;
        $this->cc = $cc;
        $this->subject = $subject;
        $this->body = $body;
        
        return $this;
    }

    public function toArray() {
        return [
            'to' => $this->to,
            'cc' => $this->cc,
            'subject' => $this->subject,
            'body' => $this->body
        ];
    }
}
