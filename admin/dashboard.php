<?php 

session_start();

include 'init.php';

if (isset($_SESSION['Admin'])) {

include 'includes/templates/navbar.php';

$q1 = $connect->prepare("SELECT * FROM 	users"); // return all users rows
$q1 -> execute(); // execute the query
$users = $q1-> rowCount(); // return number of rows

$q2 = $connect->prepare("SELECT * FROM 	categories"); // return all categories rows
$q2 -> execute(); // execute the query
$categories = $q2-> rowCount(); // return number of rows

$q3 = $connect->prepare("SELECT * FROM 	posts"); // return all posts rows
$q3 -> execute(); // execute the query
$posts = $q3-> rowCount(); // return number of rows

$q4 = $connect->prepare("SELECT * FROM 	comments"); // return all comments rows
$q4 -> execute(); // execute the query
$comments = $q4-> rowCount(); // return number of rows
?>

<div id="main-dashboard">

  <div class="container-fluid">

    <h4 class="text-center mb-4">Dashboard</h4>

    <div class="stats">
      <div class="row">

        <div class="col-md-3">
          <div class="box">
            <div>
              <h5><?php echo $users; ?></h5>
              <span>Users</span>
            </div>
            <i class="fas fa-users"></i>
          </div>
        </div>

        <div class="col-md-3">
          <div class="box">
            <div>
              <h5><?php echo $categories; ?></h5>
              <span>Categories</span>
            </div>
            <i class="fas fa-shapes"></i>
          </div>
        </div>

        <div class="col-md-3">
          <div class="box">
            <div>
              <h5><?php echo $posts; ?></h5>
              <span>Posts</span>
            </div>
            <i class="fas fa-clipboard"></i>
          </div>
        </div>

        <div class="col-md-3">
          <div class="box">
            <div>
              <h5><?php echo $comments; ?></h5>
              <span>Comments</span>
            </div>
            <i class="fas fa-comments"></i>
          </div>
        </div>

      </div>
    </div>

  </div>

</div>




<?php 

include 'includes/templates/footer.php';

} else {
  echo ' <div class="alert alert-warning" role="alert"> You are not authenticated </div> ';
  header('refresh: 3; url = login.php');
  exit();
}

?>