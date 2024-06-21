<?php
session_start();
include('includes/config.php');
if(isset($_POST['login']))
{
$uname=$_POST['username'];
$password=md5($_POST['password']);
$sql ="SELECT UserName,Password FROM admin WHERE UserName=:uname and Password=:password";
$query= $dbh -> prepare($sql);
$query-> bindParam(':uname', $uname, PDO::PARAM_STR);
$query-> bindParam(':password', $password, PDO::PARAM_STR);
$query-> execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
if($query->rowCount() > 0)
{
$_SESSION['alogin']=$_POST['username'];
echo "<script type='text/javascript'> document.location = 'dashboard.php'; </script>";
} else{
	
	echo "<script>alert('Invalid Details');</script>";

}

}

?>

<!DOCTYPE HTML>
<html>
<head>
<title>BJJ | Admin Sign in</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<link rel="stylesheet" type="text/css" href="css/mycss.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<!-- jQuery -->
<script src="js/jquery-2.1.4.min.js"></script>
<!-- //jQuery -->
<link href='//fonts.googleapis.com/css?family=Roboto:700,500,300,100italic,100,400' rel='stylesheet' type='text/css'/>
<link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>

</head> 
<body>
	<div class="heading">
        <div class="login-form">
            <h2>ADMIN LOGIN</h2>
            <form method="post">
                <div class="input-field">
                    <i class="bi bi-person-circle"></i>
                    <input type="text" name="username" placeholder="Username" required="">
                </div>
                <div class="input-field">
                    <i class="bi bi-shield-lock"></i>
                    <input type="password" name="password" placeholder="Password" required="">
                </div>
        
				<input type="submit" class="login" name="login" value="Sign In">
				
                <!-- <button type="submit" name="login">Sign In</button> -->
            </form>
        </div>
    </div>


	
</body>
</html>