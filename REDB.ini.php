<?php

    $AppleID = readline ('Enter Your AppleID: ');
    $AuthKey = readline ('Enter The Authorization Key: ');
    $CertKey = readline ('Enter The Apple Certification Key: ');

    $servername = "";
    $username = "";
    $password = "";
    $dbname = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "UPDATE iVGHCredentials SET AppleID=?, AuthKey=?, CertKey=? Where ID=1";
    $stmt= $conn->prepare($sql);
    $stmt->bind_param("sss", $AppleID, $AuthKey, $CertKey);
    $stmt->execute();


    $conn->close();
    echo "Received New Credentials\n";
?>