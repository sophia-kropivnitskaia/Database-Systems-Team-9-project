<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "WNK";

    // check if connection exists

    try{

        $conn = new mysqli($servername, $username, $password, $dbname);

    }
    catch(mysqli_sql_exception){
        echo "ERROR: Could not connect <br>";
    }


?>