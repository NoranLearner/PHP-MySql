<?php 

session_start();

// I am admin and login before

if (isset($_SESSION['Admin'])) {
  header('Location: http://localhost/BLOGX/admin/dashboard.php');
  exit();
}

include 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['admin-login'])) {

      $email = $_POST['email'];
      $password = $_POST['password'];
      $hashedpassword = sha1($password);

      // $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

      // Query to display users
      $check = $connect->prepare("SELECT * FROM users WHERE email =? and password = ?"); // return users rows
      $check -> execute(array($email, $hashedpassword)); // execute the query
      $checkRow = $check-> rowCount(); // return number of rows

      // check if there is user has data like that in database

      if ($checkRow > 0) {

        // echo 'This user exists in database';

        // Check if admin

        $fetchData = $check -> fetch(); // to fetch the data

        // print_r($fetchData);

        if ($fetchData['role'] == 'admin') {

          $_SESSION['Admin'] = $fetchData['username'];

          $_SESSION['AdminID'] = $fetchData['id'];
          
          // echo 'You are admin, You will redirect to dashboard now';
          // header('refresh: 3; url= dashboard.php' );
          header('Location: http://localhost/BLOGX/admin/dashboard.php');
          exit();

        } else {

          echo 'Sorry, You are not admin';

        }

      } else {
        echo 'This user doesn\'t exist in database';
      }

    }

}

?>

<div class="admin-login-page">
  <div class="container">
    <h1 class="text-center">Admin Login</h1>
    <div class="row">
      <form class="col-md-6 col-md-offset-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        <label>Email</label>
        <input name="email" type="email" class="form-control" placeholder="Enter Valid Email ...">
        <br/>
        <label>Password</label>
        <input name="password" type="password" class="form-control" placeholder="Enter Valid Password ...">
        <br/>
        <input type="submit" class="btn btn-primary" name="admin-login" value="Login">
      </form>
    </div>
  </div>
</div>

<?php 

include 'includes/templates/footer.php';

?>