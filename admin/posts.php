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

  // Query to display all posts
  $stmt = $connect->prepare("SELECT posts.* , users.username AS USERNAME , users.id AS USERID , categories.title AS TITLE , categories.id AS CATEID FROM posts INNER JOIN users ON posts.user_id = users.id INNER JOIN categories ON posts.category_id = categories.id "); // return all posts rows
  $stmt -> execute(); // execute the query
  $countPosts = $stmt-> rowCount(); // return number of rows
  $allPosts = $stmt -> fetchAll(); // to fetch all data

  ?>

  <div id="user-management">
    <div class="container">

      <!-- Start Page All -->

      <?php if ($page == 'All') { ?>

        <h4 class="text-center mb-4"> Post Management </h4>

        <a href="?page=AddPost" class="btn btn-success mb-3"> Add New Post </a>

        <div class="card">

          <h5 class="card-header"> Posts <span class="badge bg-primary"> <?php  echo $countPosts ?> </span> </h5>

          <div class="card-body">

            <div class="table-responsive">
              
              <table class="table table-striped">

                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Category</th>
                    <th scope="col">Publisher</th>
                    <th scope="col">Created_At</th>
                    <th scope="col">Controls</th>
                  </tr>
                </thead>

                <tbody>

                  <?php
                    if ($countPosts > 0) {
                        foreach ($allPosts as $post) {
                  ?>

                        <tr>
                          <th scope="row"> <?php echo $post['id']; ?> </th>
                          <td>
                            <?php  
                              echo '<a target="_blank" href=" uploads/posts/ ' .$post['image']. ' ">';
                              echo ' <img style="width:60px;height:60px;border-radius:4px;" src="uploads/posts/ ' .$post['image']. '" alt=""> '; 
                              echo '</a>';
                            ?>
                          </td>
                          <td><?php echo $post['title']; ?></td>
                          <td><?php echo $post['description']; ?></td>
                          <td> 
                            <?php 
                              if ($post['status'] == '0') {
                                echo '<span class="badge bg-danger"> Hidden </span>';
                              } else{
                                echo '<span class="badge bg-info"> Visible </span>';
                              }
                            ?> 
                          </td>
                          <td>
                            <?php 
                            echo '<a href="categories.php?page=showCategory&CateID= ' . $post['CATEID'] . ' ">';
                            echo $post['TITLE']; 
                            echo '</a>';
                            ?>
                          </td>
                          <td>
                            <?php 
                            echo '<a href="users.php?page=showUser&userid= ' . $post['USERID'] . ' ">';
                            echo $post['USERNAME'];
                            echo '</a>';
                            ?>
                          </td>
                          <td><?php echo $post['created_at']; ?></td>
                          <td>
                            <a title="edit" class="btn btn-outline-primary" href="posts.php?page=showPost&postid=<?php echo $post['id']?>">
                              <i class="far fa-eye"></i>
                            </a>
                            <a title="delete" class="btn btn-outline-danger confirm-delete" href="posts.php?page=Delete&postid=<?php echo $post['id']?>">
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

        // post exist in database , delete it
        if (isset($_GET['postid']) && !empty($_GET['postid']) && is_numeric($_GET['postid']) ) {
          $postid = intval($_GET['postid']); // integer value
        } else {
          $postid = '';
        }

        // Query to check if postid exists in database
        $check = $connect->prepare("SELECT * FROM posts WHERE id = ?"); // return all posts rows
        $check -> execute(array($postid)); // execute the query
        $rows = $check-> rowCount(); // return number of rows

        if($rows > 0){
          // Found postid in database , I will delete it
          $delStmt = $connect->prepare("DELETE FROM posts WHERE id = ?"); // Delete the post
          $delStmt -> execute(array($postid)); // execute the query
          $delRow = $delStmt-> rowCount(); // return number of rows
          if ($delRow > 0) {
          // The post has been deleted
          header('Location:posts.php'); // Redirect to the page after query executed
          exit();
          }
        } else { echo 'Can\'t Delete This ID'; }

      // End Page Delete

      // Start Page AddPost

      } elseif ($page == 'AddPost') { ?>

        <h2> Add New Post </h2>

        <form style="width:450px; border:1px solid #fff; padding:20px;" action="?page=SavePost" method="POST" enctype="multipart/form-data">
          <label>Title</label>
          <input name="title" type="text" class="form-control" placeholder="Enter Post Title ...">
          <br/>
          <label>Description</label>
          <textarea name="description" cols="5" rows="5" class="form-control" placeholder="Enter Post Description ..."></textarea>
          <br/>
          <label>Post Image</label>
          <input name="postImage" type="file" class="form-control">
          <br/>
          <label>Status</label>
          <input type="radio" name="status" value="0"> Hidden
          <input type="radio" name="status" value="1"> Visible
          <br/> <br/>
          <label>Category</label>
          <select name="category_id" class="form-control">
            <option readonly>-- Choose Category</option>
            <?php 
            $selCates = $connect->prepare("SELECT * FROM categories"); // return all categories rows
            $selCates -> execute(); // execute the query
            $allCategories = $selCates-> fetchAll();
            foreach ($allCategories as $category) {
              echo ' <option value=" '. $category['id'] .' "> ' . $category['id'] . ' - ' . $category['title'] . ' </option> ';
            }
            ?>
          </select>
          <br/> <br/>
          <input value=" <?php echo $_SESSION['AdminID']; ?> " name="user_id" type="hidden">
          <br/> <br/>
          <input type="submit" class="btn btn-primary" name="save-post" value="Save">
        </form>

      
      <?php 
      
      // End Page AddPost

      // Start Page SavePost

      } elseif ($page == 'SavePost') {

          if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['save-post'])) {

              $postFormErrors = array();

              $title = $_POST['title'];
              $description = $_POST['description'];
              $status = $_POST['status'];
              $user_id = $_POST['user_id'];
              $category_id = $_POST['category_id'];

              // $testImage = $_FILES['postImage'];
              $imageName = $_FILES['postImage']['name'];
              $imageSize = $_FILES['postImage']['size'];
              $imageTmp = $_FILES['postImage']['tmp_name']; // Temporary Path
              $imageType = $_FILES['postImage']['type'];
              // $imgExtension1 = explode('.', $imageName);   // {name, extension}
              // $imgExtension2 = strtolower(end($imgExtension1));  // extension
              $allowedExtensions = array("jpeg", "jpg", "png", "gif", "svg");

              /* if (!in_array($imgExtension2, $allowedExtensions)) {
                $postFormErrors[] = 'This extension is not allowed to upload';
              } */

              // Validation Data

              if (!empty($title)) {
                $title = filter_var($title, FILTER_SANITIZE_STRING);
              } else {
                $postFormErrors[] = 'Title is required';
              }

              if (!empty($description)) {
                $description = filter_var($description, FILTER_SANITIZE_STRING);
              } else {
                $postFormErrors[] = 'Description is required';
              }

              if (!empty($status)) {
                $status = filter_var($status, FILTER_SANITIZE_NUMBER_INT);
              } else {
                $postFormErrors[] = 'Status is required';
              }

              if (empty($imageName)) {
                $postFormErrors[] = 'Image is required';
              }

              if (!empty($user_id)) {
                $user_id = $user_id ;
              } else {
                $postFormErrors[] = 'userId is required';
              }

              if (!empty($category_id)) {
                $category_id = $category_id ;
              } else {
                $postFormErrors[] = 'categoryId is required';
              }

              if ($imageSize > 1048576) { // 1024 * 1024 = 1 mega
                $postFormErrors[] = 'Your image is greater than 1 MG';
              }

              // Check if there is no error
              if (empty($postFormErrors)) {

                /* echo '<pre>';
                print_r($testImage);
                echo '</pre>'; */

                // change image name to avoid duplicates and conflict
                $finalImage = rand(0,10000) . '_' . $imageName ; // 0132_img1.png 
                // move image from temp path to new path
                move_uploaded_file($imageTmp, "uploads/posts/" . $finalImage);
                // take image name to insert query
                $stmt = $connect->prepare("INSERT INTO `posts`(`title`, `description`, `image`, `status`, `user_id`, `category_id`, `created_at`) VALUES (:ztitle,:zdescription,:zimage,:zstatus,:zuserid,:zcategoryid, Now())");
                // execute the query
                $stmt -> execute(array('ztitle' => $title, 'zdescription' => $description, 'zimage' => $finalImage, 'zstatus' => $status, 'zuserid' => $user_id, 'zcategoryid' => $category_id));
                // return number of rows
                if ($stmt-> rowCount() > 0) {
                  echo '<div class="alert alert-success" role="alert">Post has been created successfully</div>';
                  // Redirect to the users page after query executed
                  // header('Location:posts.php');
                  header('refresh:3; url=posts.php');
                  exit();
                } else {
                  echo 'There are errors';
                }
              }

            }
          }

      // End Page SavePost

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