<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>CAR WORKSHOP</title>
</head>

<style>
    .signup {
        margin-top: 15px;
        text-align: center;
    }

    .signup a {
        color: white;
        background-color: green;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        display: inline-block;
        box-shadow: 0px 4px 0px 0px #182c25;
    }
    .signup a:active {
        box-shadow: 0px 6px 0px 0px #182c25;

    }

    .signup a:hover {
        background-color: darkgreen;
    }
</style>


<body>
    <section id="section1">
        <div class="title">Sign in</div>

        <form action="signin.php" class="form_design" method="post">

            <label class="form_label">Username</label>
            <input type="text" name="fname"> <br/>

            <label class="form_label">Password</label>
            <input type="password" name="pass"> <br/>

            <select name="role" class="form_role">
                <option value="user">user</option>
                <option value="admin">admin</option>
            </select> <br />

            <input type="submit" value="Sign In">

            <div class="signup">
                <a href="signup.php">Create an account</a>
            </div>

        </form>
    </section>
    
</body>
</html>