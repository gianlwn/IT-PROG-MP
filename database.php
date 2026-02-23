<?php
    $db_server = "localhost";
    $db_user = "root"; // change details based on your credentials
    $db_password = "1234"; // change details based on your credentials
    $db_name = "itprogmp_db";
    $conn = "";
    
    try{
        $conn = mysqli_connect($db_server, 
                        $db_user, 
                        $db_password, 
                        $db_name);
    }
    catch(mysqli_sql_exception){
        echo "Could not connect!";
    }  
?>