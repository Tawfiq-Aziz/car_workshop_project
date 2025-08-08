<?php 
require_once('dbConnect.php');

if(isset($_POST['fname']) && isset($_POST['pass'])) {

    $u = $_POST['fname'];
    $p = $_POST['pass'];
    $role = $_POST['role'];
    $sql = "SELECT * FROM users WHERE username = '$u' AND password = '$p' AND role = '$role' ";
    
    $result = mysqli_query($conn,$sql);

    if (mysqli_num_rows($result) !=0) {

        if ($role == "admin"){
            header("Location: adminHome.php");
        }else {
            header("Location: userHome.php");
        }
        
    }else {
        //echo"Username or Password is wrong";
        header("Location: index.php");
    }
}

?>