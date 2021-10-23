<?php 

session_start();

include 'init.php';

// Found Session

if (isset($_SESSION['Admin'])) {

  include 'includes/templates/navbar.php';

  // لو فيه صفحه ادكيت عليها ودينى على الصفحه دى غير كده خليك فى الصفحه اللى اسمها All

  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    $page = 'All';
  }

  // Query to display all users
  $stmt = $connect->prepare("SELECT * FROM users WHERE id != ?"); // return all users rows
  $stmt -> execute(array($_SESSION['AdminID'])); // execute the query
  $countUsers = $stmt-> rowCount(); // return number of rows
  $allUsers = $stmt -> fetchAll(); // to fetch all data

  ?>

  <div id="user-management">
    <div class="container">

      <!-- Start Page All -->

      <?php if ($page == 'All') { ?>

        <h4 class="text-center mb-4"> User Management </h4>

        <a href="?page=AddUser" class="btn btn-success mb-3"> Add New User </a>

        <div class="card">

          <h5 class="card-header"> Users <span class="badge bg-primary"> <?php  echo $countUsers ?> </span> </h5>

          <div class="card-body">

            <div class="table-responsive">
              
              <table class="table table-striped">

                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col">Controls</th>
                  </tr>
                </thead>

                <tbody>

                  <?php
                    if ($countUsers > 0) {
                        foreach ($allUsers as $user) {
                  ?>

                        <tr>
                          <th scope="row"> <?php echo $user['id']; ?> </th>
                          <td><?php echo $user['username']; ?></td>
                          <td><?php echo $user['email']; ?></td>
                          <td><?php echo $user['role']; ?></td>
                          <td> 
                            <?php 
                              if ($user['status'] == '0') {
                                echo '<span class="badge bg-danger"> Pending </span>';
                              } else{
                                echo '<span class="badge bg-info"> Approved </span>';
                              }
                            ?> 
                          </td>
                          <td>
                            <a title="edit" class="btn btn-outline-primary" href="users.php?page=showUser&userid=<?php echo $user['id']?>">
                              <i class="far fa-eye"></i>
                            </a>
                            <a title="delete" class="btn btn-outline-danger" href="users.php?page=Delete&userid=<?php echo $user['id']?>">
                              <i class="far fa-trash-alt"></i>
                            </a>
                          </td>
                        </tr>

                  <?php
                        }
                    }
                  ?>

                </tbody>

              </table>

            </div>

          </div>

        </div>

      <!-- End Page All -->

      <!-- Start Page Delete -->

      <?php } elseif ($page == 'Delete') {

        // User ID
        if (isset($_GET['userid']) && !empty($_GET['userid']) && is_numeric($_GET['userid']) ) {
          $userid = intval($_GET['userid']); // integer value
        } else {
          $userid = '';
        }

        // Query to check if userid exists in database
        $check = $connect->prepare("SELECT * FROM users WHERE id = ?"); // return all users rows
        $check -> execute(array($userid)); // execute the query
        $rows = $check-> rowCount(); // return number of rows

        if($rows > 0){
          // Found userid in database , I will delete it
          $delStmt = $connect->prepare("DELETE FROM users WHERE id = ?"); // Delete the user
          $delStmt -> execute(array($userid)); // execute the query
          $delRow = $delStmt-> rowCount(); // return number of rows
          if ($delRow > 0) {
          // The user has been deleted
          header('Location:users.php'); // Redirect to the page after query executed
          exit();
          }
        } else { echo 'Can\'t Delete This ID'; }

      // End Page Delete

      // Start Page AddUser

      } elseif ($page == 'AddUser') { ?>

        <h2> Add New User </h2>

        <form style="width:450px; border:1px solid #fff; padding:20px;" action="?page=SaveUser" method="POST">
          <label>User Name</label>
          <input name="username" type="text" class="form-control" placeholder="Enter Valid Username ...">
          <br/>
          <label>Email</label>
          <input name="email" type="email" class="form-control" placeholder="Enter Valid Email ...">
          <br/>
          <label>Password</label>
          <input name="password" type="password" class="form-control" placeholder="Enter Valid Password ...">
          <br/>
          <label>Role</label>
          <select name="role" class="form-control">
            <option readonly>-- Choose Role</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
          </select>
          <br/>
          <input type="submit" class="btn btn-primary" name="save-user" value="Save">
        </form>

      
      <?php 
      
      // End Page AddUser

      // Start Page SaveUser

      } elseif ($page == 'SaveUser') {

          // echo 'Saving New User';

          if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['save-user'])) {

              $usernameErr = $emailErr = $passwordErr = $roleErr = '';

              $username = $_POST['username'];
              $email = $_POST['email'];
              $password = $_POST['password'];
              $hashedPassword = sha1($password);
              $role = $_POST['role'];

              // Validation Data

              if (!empty($username)) {
                $username = filter_var($username, FILTER_SANITIZE_STRING);
              } else {
                $usernameErr = 'Username is required';
              }

              if (!empty($email)) {
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
              } else {
                $emailErr = 'Email is required';
              }

              if (!empty($password)) {
                $password = filter_var($password, FILTER_SANITIZE_STRING);
              } else {
                $passwordErr = 'Password is required';
              }

              if (!empty($role)) {
                $role = filter_var($role, FILTER_SANITIZE_STRING);
              } else {
                $roleErr = 'Role is required';
              }

              // Check if there is no error
              if (empty($usernameErr) && empty($emailErr) && empty($passwordErr) && empty($roleErr)) {
                // Query to insert new user
                $stmt = $connect->prepare("INSERT INTO `users`(`username`, `email`, `password`, `status`, `role`, `created_at`) VALUES (:zusername,:zemail,:zpassword,:zstatus,:zrole, Now())");
                // execute the query
                $stmt -> execute(array('zusername' => $username, 'zemail' => $email, 'zpassword' => $hashedPassword, 'zstatus' => '0', 'zrole' => $role));
                // return number of rows
                if ($stmt-> rowCount() > 0) {
                  echo 'User has been created successfully';
                  // Redirect to the users page after query executed
                  // header('Location:users.php');
                  header('refresh:3; url=users.php');
                  exit();
                } else {
                  echo 'There are errors';
                }
              }

            }
          }

      // End Page SaveUser

      // Start Page ShowUser

      }  elseif ($page == 'showUser') { 

        // User ID
        if (isset($_GET['userid']) && !empty($_GET['userid']) && is_numeric($_GET['userid']) ) {
          $userid = intval($_GET['userid']); // integer value
        } else {
          $userid = '';
        }

        // Query to check if userid exists in database
        $check = $connect->prepare("SELECT * FROM users WHERE id = ?"); // return all users rows
        $check -> execute(array($userid)); // execute the query
        $rows = $check-> rowCount(); // return number of rows

        if($rows > 0){

          $userInfo = $check -> fetch(); // to fetch the data

          ?> 
          
          <!-- <div class="card" style="width: 18rem;">
            <div class="card-body">
              <h5 class="card-title"> <?php echo $userInfo['username'] ;?> </h5>
              <h6 class="card-subtitle mb-2 text-muted"> <?php echo $userInfo['email'] ;?> </h6>
              <p class="card-text"> Role: <?php echo $userInfo['role'] ;?> </p>
              <a href="#" class="card-link"> Update </a>
              <a href="#" class="card-link"> Delete </a>
            </div>
          </div> -->


          <h2> Edit User </h2>

          <form style="width:450px; border:1px solid #fff; padding:20px;" action="?page=UpdateUser" method="POST">
            <label>User Name</label>
            <input value="<?php echo $userInfo['username'] ;?>" name="username" type="text" class="form-control" placeholder="Enter Valid Username ...">
            <br/>
            <label>Email</label>
            <input value="<?php echo $userInfo['email'] ;?>" name="email" type="email" class="form-control" placeholder="Enter Valid Email ...">
            <br/>
            <input name="userid" type="hidden" value="<?php echo $userInfo['id'] ;?>">
            <label>Role</label>
            <select name="role" class="form-control">
              <option readonly>-- Choose Role</option>
              <option <?php if ( $userInfo['role'] === 'admin' ) { echo 'selected'; } else { echo ' ' ; }?> value="admin">Admin</option>
              <option <?php if ( $userInfo['role'] === 'user' ) { echo 'selected'; } else { echo ' ' ; }?> value="user">User</option>
            </select>
            <br/>
            <label>Status</label>
            <input <?php if ( $userInfo['status'] === '0' ) { echo 'checked'; } else { echo ' ' ; }?> type="radio" name="status" value="0"> Pending
            <input <?php if ( $userInfo['status'] === '1' ) { echo 'checked'; } else { echo ' ' ; }?> type="radio" name="status" value="1"> Approved
            <br/>
            <input type="submit" class="btn btn-primary mt-3" name="save-user" value="Save">
          </form>

          <?php 

        }

      }
      
      // End Page ShowUser

      // Start Page UpdateUser

      elseif ($page == 'UpdateUser') {

        // Update Query

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){

          $username = $_POST['username'];
          $email = $_POST['email'];
          $role = $_POST['role'];
          $status = $_POST['status'];
          $USERID = $_POST['userid'];
          
          // Query to Update user
          $updateStmt = $connect->prepare("UPDATE `users` SET `username`=?, `email`=?, `role`=?, `status`=?, `updated_at`=Now() WHERE id=?");
          // execute the query
          $updateStmt -> execute(array($username, $email, $role, $status, $USERID));
          // return number of rows
          $updateRow = $updateStmt -> rowCount();
          if ($updateRow > 0) {
            echo '<div class="alert alert-success m-5"> User has been updated successfully </div>';
            header('refresh:3; url=users.php');
            exit();
          }
          
        }

      } 
      
      // End Page UpdateUser

      ?> 

    </div>
  </div>


  <?php 

  include 'includes/templates/footer.php';

}

// No Found Session

else {
  echo ' <div class="alert alert-warning" role="alert"> You are not authenticated </div> ';
  header('refresh: 3; url = login.php');
  exit();
}

?>