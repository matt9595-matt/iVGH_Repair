<?php

class RepairTracker
 {
    private $AppleID;
    private $AuthKey;
    private $RepairStatus;
    private $CustomerName;
    private $CertKey;
    private $CertPath = "/path/to/cert";
    private $KeyPath = "/path/to/key";
    private $id;
    private $servername = "";
    private $username = "";
    private $password = "";
    private $dbname = "";
    
    //function to print form for repairID
   public function Repair_Status_Form() 
    {
	    echo '<form action="" method="post">';
	    echo '<p>';
	    echo 'Your Apple Repair ID <br/>';
	    echo '<input type="text" name="cf-id" />';
	    echo '</p>';
	    echo '<p><input type="submit" name="cf-submitted"</p>';
	    echo '</form>';
    }

    //checks if user hit submit button to begin API Process
    public function GSX_ID() 
    {
	    if ( isset( $_POST['cf-submitted'] ) ) 
        {
            $this->GSX_Fetch();
	    }
    }
    //Assign and Trim RepairID, Handler for other function calls 
    private function GSX_Fetch()
    {
        $this->id = htmlspecialchars($_POST['cf-id']);
        $this->id = trim($this->id);
        $this -> get_auth();
        $this -> GSXLogin();
        $this -> RepairCheck();
        $this -> GSXLogout();
    }
    //Re-Authorize Apple API Token
    private function GSXLogin()
    {
        //Initialize Curl
        $curl = curl_init();
        // Create JSON Request Body
        $postData=[
            "userAppleId" => $this->AppleID,
            "authToken" => $this->AuthKey
        ];

        //Set Curl Parameters 
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api-partner-connect.apple.com/api/authenticate/token',
          CURLOPT_SSLCERT => $this->CertPath,
          CURLOPT_SSLKEY =>  $this->KeyPath,
          CURLOPT_KEYPASSWD => $this->CertKey,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($postData),
          CURLOPT_HTTPHEADER => array(
            'X-Apple-SoldTo: ',
            'X-Apple-ShipTo: ',
            'X-Operator-User-ID:'.$this->AppleID,
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Language: en-US'
          ),
        ));
        //Save Response
        $response = curl_exec($curl);
        curl_close($curl);
        //Create Response Body
        $obj= json_decode($response,JSON_PRETTY_PRINT);
        //Grab 'authToken from Response Body and re-assign authkey
        $this->AuthKey = $obj['authToken'];
        
        
        //Open DB Connection
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        //Update AuthKey in DB
        $sql = "UPDATE iVGHCredentials SET AuthKey= ? WHERE ID=1";
        $stmt= $conn->prepare($sql);
        $stmt->bind_param("s", $this->AuthKey);
        $stmt->execute();
        //Close SQL Connection
        $conn->close();
    }
    // Get GSX Repair Stats
    private function RepairCheck()
    {
        //Intialize CURL
        $curl = curl_init();
        //Set Curl Parameters
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-partner-connect.apple.com/gsx/api/repair/details?repairId='.$this->id,
        CURLOPT_SSLCERT => $this->CertPath,
        CURLOPT_SSLKEY => $this->KeyPath,
        CURLOPT_KEYPASSWD => $this->CertKey,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        'Accept: application/json',
        'Accept-Language: en-US',
        'X-Apple-SoldTo: ',
        'X-Apple-ShipTo: ',
        'X-Apple-Service-Version: v2',
        'Content-Type: application/json',
        'X-Apple-Auth-Token:'.$this->AuthKey
    ),
    ));
        //save response
        $response = curl_exec($curl);
        //save httpcode for error response
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        //create response body
        $json = json_decode($response,JSON_PRETTY_PRINT);
        //Assign Repair Status Code and Customer Name
        $this->RepairStatus = $json['repairStatus'];
        $this->CustomerName = $json['customer']['firstName'];

        //Switch Statement for API HTTP Response Handling
        switch($httpcode)
        {
        case 200:
            //Success, Proceed Printing Repair
            $this->PrintRepair();
            break;
        
        case 400:
            //Bad Input
            echo "<html><h4>Incorrect Repair Number, Please Call (208) 883-8372</h4><html>";
            break;
        case 401:
            //Unathorized
            echo "<html><h4>Communication Error, For Repair Details Please Call (208) 883-8372</h4><html>";
            require_once('VGHMailer.php');
            $VGHPostman = new VGHMailer();
            $VGHPostman->VGHAuthError();
            break;
        case 403:
            //Forbidden
            echo "<html><h4>Communication Error, For Repair Details Please Call (208) 883-8372</h4><html>";
            require_once('VGHMailer.php');
            $VGHPostman = new VGHMailer();
            $VGHPostman->VGHAuthError();
            break;
        case 500: 
            //Server Error
            echo "<html><h4>Server Error, Please Try Again Later Or Call (208) 883-8372</h4><html>";
            break;
        default:
            //Unexpected Error, Mail HTTP code and troubleshoot
            echo "<html><h4>Unexpected Error, For Repair Details Please Call (208) 883-8372</h4><html>";
            require_once('VGHMailer.php');
            $VGHPostman = new VGHMailer();
            $VGHPostman->VGHOtherError($httpcode);
            break;
        }
    

    }
    //Printing server response (Customer Facing)
    private function PrintRepair()
    {
        echo"<html><h3>$this->CustomerName, Thank You For Choosing VGH</h3><html>";
        echo "<html><h4>Your Repair Status Is: </h4><html>";
        //Switch Statment for Repair code Processing
        switch($this->RepairStatus){
        case "AWTP":
            //Awaiting parts
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Awaiting Parts</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        case "SPCM":
            //Repair Marked Complete
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Repair Marked As Completed</span>";
            echo "<p>";
            echo "<html><h4>Please Call (208) 883-8372, To Confirm Repair Status</h4><html>";
            break;
        case "SCOM":
            //Repair Closed and Completed
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Repair Closed And Completed</span>";
            echo "<p>";
            echo "<html><h4>Please Call (208) 883-8372, To Confirm Repair Status</h4><html>";
            break;
        case "RFPU":
            //Ready for Pickup
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Ready For Pickup</span>";
            echo "<p>";
            echo "<html><h4>Please Call (208) 883-8372, To Confirm Repair Status</h4><html>";
            break;
        case "RLSD":
            //Released from Processing
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Repair Starting Soon</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        case "AWTR":
            //Parts Allocated
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Parts Allocated, Repair Starting Soon</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        case "BEGR":
            //In Repair
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>In Repair</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        case "REQA":
            //Mail-In Repair center awaiting unit
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Your Device is Shipping To A Repair Depot</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        case "URCD":
            //Mail-In Repair center received unit
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Your Device Was Received By The Repair Depot Repair Should Begin Soon</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please call (208) 883-8372</h4><html>";
            break;
        case "RRPL":
            //Mail-In Repair unit returned replaced
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Your Device Was Replaced And Is Currently Being Shipped To VGH</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        case "RRPR":
            //Mail-in Repair unit returned repair
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>Your Device Was Repair And Is Currently Being Shipped To VGH</span>";
            echo "<p>";
            echo "<html><h4>If You Have Any Questions Please Call (208) 883-8372</h4><html>";
            break;
        default:
            //Any other Repair codes encountered
            echo "<span style='color:blue;font-size: xx-large;font-weight:bold'>In Progress</span>";
            echo "<p>";
            echo "<html><h4>Please Call (208) 883-8372 To Retrieve Full Details</h4><html>";
        }


    }   
    //De-Authorize AuthKey (Required under API Docs)
    private function GSXLogout()
    {
        //initialize curl
        $curl = curl_init();
        //Prepare JSON Response Body
        $postData=[
            "userAppleId" => $this->AppleID,
            "authToken" => $this->AuthKey
        ];
        //Set Curl Parameters
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-partner-connect.apple.com/api/authenticate/end-session',
        CURLOPT_SSLCERT => $this->CertPath,
        CURLOPT_SSLKEY => $this->KeyPath,
        CURLOPT_KEYPASSWD => $this->CertKey,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => array(
        'X-Apple-SoldTo: ',
        'X-Apple-ShipTo: ',
        'Content-Type: application/json',
        'Accept: application/json',
        'Accept-Language: en-US',
        'X-Apple-Auth-Token:'.$this->AuthKey
    ),
    ));

    $response = curl_exec($curl);
    //Save Response (For Debugging);
    curl_close($curl);
    }

    //Assign Variables from SQL DB
    private function get_auth()
    {
        //open connection to DB
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        //Grab SQL items
        $sql = "SELECT AppleID, AuthKey, CertKey From iVGHCredentials WHERE ID=1";
        $stmt= $conn->prepare($sql);
        $result= $conn->query($sql);
        //Assign SQL items to Class Variables
        while ($obj = $result->fetch_object()) {

            $this->AppleID = $obj->AppleID;

            $this->AuthKey = $obj->AuthKey;

            $this->CertKey = $obj->CertKey;
        }
        //Close DB Connection
        $conn->close();
    }
 }

?>
