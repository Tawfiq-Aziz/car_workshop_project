<?php 
require_once('dbConnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['Phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = mysqli_real_escape_string($conn,$_POST['pass']);

   $sql = "INSERT INTO users (username, address, phone, email, role, password) 
        VALUES ('$username', '$address', '$phone', '$email', '$role', '$password')";

    

    echo "QUERY: $sql <br>";
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error wihfo: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="signup.css">
    <title>Sign Up</title>
</head>
<body>
    <div class="title"> Sign Up</div>
    <form action="signup.php" class="signup-form" method="post">

        <label class="signup-label">Your Username</label>
        <input type="text" name="name"> <br />

        <label class="signup-label">Address</label>
        <input type="text" name="address"> <br />

        <label class="signup-label">Phone</label>
        <input type="text" name="Phone"> <br />

        <label class="signup-label">Email</label>
        <input type="text" name="email"> <br />

        <label class="signup-label">Password</label>
        <input type="password" name="pass"> <br/>

        <label for="role" class="signup-label"> Sign Up As</label>
        <select name="role" class="form-role">
            <option value="user">user</option>
            <option value="admin">admin</option>
        </select> <br />

        <input type="submit" value="Sign Up">

    </form>


    
</body>
</html>