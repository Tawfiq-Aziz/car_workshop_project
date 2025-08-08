<?php 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cse391";

$conn = new mysqli($servername, $username,$password);

if ($conn->connect_error){
    die("connection failed:" . $conn->connect_error);
}else {
    //echo "connection successful";
    mysqli_select_db($conn,$dbname);
}

?>