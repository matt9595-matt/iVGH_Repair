<?php

class VGHMailer
{
    private $to = "";
    private $subject;
    private $message;

    public function VGHAuthError()
    {
        $this->subject = "Apple Repair Tracker Error: 403 or 401";
        $this->message = "Apple repair tracker reported a bad authorization key. Please Grab a valid authorization key from: login-partner-connect.apple.com. Then run the REDB program located in var/www/ivgh/wp-content/plugins/iVGHRepair using line php ./REDB.ini.php.";
        $sent = wp_mail($this->to, $this->subject, $this->message);
    }

    public function VGHOtherError($httpcode)
    {
        $this->subject = "Apple Repair Tracker Error: ".$httpcode;
        $this->message = "Apple repair tracker reported an HTTP Error Code Please see code in subject and diagnose appropriately";
        $sent = wp_mail($this->to, $this->subject, $this->message);
    }


}
?>
