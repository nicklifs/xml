<?php
    require("libs.php");

    $conn = new Connection();
    $conn->Connect();
    $filename = $_SERVER['HTTP_X_FILE_NAME'];

    $upload = new Upload($conn, $filename);

    $conn->Disconnect();
?>