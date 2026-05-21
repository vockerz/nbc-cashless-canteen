<?php
    ini_set('session.gc_maxlifetime', 86400);
    session_start();
    include 'config/conf.php';
    include 'config/title.php';
    include 'config/header.php';
    if(isset($_SESSION['insta_acc'])){
      $access = "SELECT * FROM user where account_id = '$_SESSION[insta_acc]'";
      $settings = "SELECT * FROM settings";
      if($conn->query($access)->num_rows <= 0){
        $_GET['module'] = 'logout';
      }
      $access = $conn->query($access)->fetch_object();
      $settings = $conn->query($settings)->fetch_object();
	  $maxCredit = $settings->max_credit ?? 1000;
      if(!isset($_GET['print'])){
        include('config/menu.php');
      }
      if(!isset($_GET['module'])){
        $_GET['module'] = 'main';
        if(isset($_SESSION['insta_acc']) && $access->level >= 0){
          $_GET['module'] = 'admin';
        }
      }
?>
<div class="modal fade" id="changepass" tabindex="-1" role="dialog">
  <div class="modal-dialog">    
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header" style="padding:35px 50px;">
        <h5 class="modal-title" id="modalLabel" align="center">Update Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding:40px 50px;">
        <form role="form" action = "" method = "post">
          <div class="form-group">
            <label for="usrname"><span class="icon-eye-blocked"></span> Old Password <font color = "red"> * </font></label>
            <input type="password" class="input-sm form-control-sm form-control" required id="oldpsw" name = "oldpword" autocomplete="off"placeholder="Enter password">
          </div>
          <div class="form-group">
            <label for="usrname"><span class="icon-eye"></span> New Password <font color = "red"> * </font></label>
            <input type="password" class="input-sm form-control-sm form-control" required id="psw" name = "pword" autocomplete="off"placeholder="Enter password">
          </div>
          <div class="form-group">
            <label for="psw"><span class="icon-eye"></span> Confirm New Password <font color = "red"> * </font></label>
            <input type="password" class="input-sm form-control-sm form-control" required id="psw1" name = "pword2" autocomplete="off"placeholder="Enter password">
          </div>
          <button type="submit" id = "submitss" name = "submitpw" class="btn btn-success btn-block"><span class="icon-switch"></span> Update</button>
        </form>
      </div>
    </div>
  </div>
</div>
  <?php 
    if(isset($_POST['submitpw'])){
      if(!empty($_POST['pword']) && !empty($_POST['pword2'])){
        $oldpass = mysqli_real_escape_string($conn, $_POST['oldpword']);
        $pword = mysqli_real_escape_string($conn, $_POST['pword']);
        $pword2 = mysqli_real_escape_string($conn, $_POST['pword2']);
        if($pword != $pword2){
          echo '<script type="text/javascript">alert("New password doesn not match."); window.location.href = "'. $_SESSION["REQUEST_URI"]  . '";</script>';
          exit;
        }
        $pass = "SELECT * FROM user where account_id = '" . $_SESSION['insta_acc'] . "' and pword = '" . $oldpass . "' limit 1";
        $pass = $conn->query($pass);
        if($pass->num_rows > 0){
          $uppass ="UPDATE user set pword = '$pword' where account_id = '" . $_SESSION['insta_acc'] . "' and pword = '" . $oldpass . "'";
          if($conn->query($uppass) === TRUE){
            echo '<script type="text/javascript">alert("Change password successful."); window.location.href = "'. $_SESSION["REQUEST_URI"]  . '";</script>';
          }else{
            $conn->error();
          }
        }else{
          echo '<script type="text/javascript">alert("Old password does not match."); window.location.href = "'. $_SESSION["REQUEST_URI"]  . '";</script>';
        }
      }else{
        echo '<script type="text/javascript">alert("Empty password"); window.location.href = "'. $_SESSION["REQUEST_URI"]  . '";</script>';
      }
    }
  ?>
  <!-- Page Content -->
      <div id="right-panel" class="right-panel">
      <?php 
        include 'config/head.php';
        include 'ajax/func.php';
        if(!isset($_GET['action'])){
            $acc = 'index.php';
        }else{
            $acc = $_GET['action'].'.php';
        }
        if(!isset($_GET['module'])){
          include 'modules/main/index.php';
        }elseif($_GET['module'] == 'logout'){
            include 'modules/logout.php';
        }elseif(!file_exists('modules/'.$_GET['module'].'/'.$acc)){
            include 'config/404.php';
        }else{
            include 'modules/'.$_GET['module'].'/'.$acc;
        }
    ?>
    </div>
    <?php
     }elseif((isset($_GET['module']) && $_GET['module'] == 'login' && !isset($_SESSION['insta_acc'])) || (!isset($_SESSION['insta_acc']))){
      $_SESSION["REQUEST_URI"] = "$_SERVER[REQUEST_URI]";
    ?>
    
<div class="sufee-login d-flex align-content-center flex-wrap" style="width:100%;">
  <div class="container" >
    <div class="login-content">
      <div class="login-form">
        <div class="login-logo">
          <a href="/building">
            <img class="align-content" src="images/logo.jpg" alt=""><br><br>
            <h3> CASHLESS SYSTEM</h3>
          </a>
        </div>
        <div class="sufee-alert alert with-close alert-success alert-dismissible fade show" id = "alertss" style="display: none;">
            <span class="badge badge-pill badge-success">Success</span>&nbsp;
            You successfully logged in.
        </div>
        <hr>
        <form method="post" action="">
          <div class="form-group">
            <label>Username</label>
            <div class="input-group">
              <input autofocus type="text" class="form-control" required autocomplete = "off" placeholder="Username" name = "uname"><div class="input-group-addon"><i class="fa fa-user"></i></div>
            </div>
          </div>
          <div class="form-group">
            <label>Password</label>
            <div class="input-group">
              <input type="password" class="form-control" required placeholder="Password" name = "password"><div class="input-group-addon"><i class="fa fa-asterisk"></i></div>
            </div>
          </div>
        <div class="checkbox">
          <label>
            <input type="checkbox"> Remember Me
          </label>
        </div>
        <button type="submit" class="btn btn-success btn-flat m-b-30 m-t-30" name = "submit"><i class="fa fa-dot-circle-o"></i> Sign in</button>
      </form>
      </div>
    </div>
  </div>
</div>
<br>

<?php
  if(isset($_SESSION['logout']) && $_SESSION['logout'] != null){
    echo  '<div class="alert alert-warning" align = "center"><strong>You\'ve been logged out.</strong></div>';
    $_SESSION['logout'] = null;
  }
  if(isset($_GET['module']) && $_GET['module'] == 'logout'){
    $_SESSION["REQUEST_URI"] = "/" . $pagename;
  }
?>
<?php
  if(isset($_POST['submit'])){
    $uname = mysqli_real_escape_string($conn, $_POST['uname']);
    $password =  mysqli_real_escape_string($conn, $_POST['password']);
    
    $sql = "SELECT * FROM `user` where uname = '$uname' and pword = '$password'";
    $result = $conn->query($sql);   
    if($result->num_rows > 0){
      while($row = $result->fetch_assoc()){               
        $_SESSION['insta_acc'] = $row['account_id'];
        $_SESSION['nameinsta'] = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'];
        $_SESSION['usernameinsta'] = $row['uname'];
        $stmt = $conn->prepare("UPDATE transactions SET active = 0 where user = ? and active <> 2");
        if($stmt){
          $stmt->bind_param("s", $_SESSION['nameinsta']);
          $stmt->execute();
        }
        $conn->close();
        echo '<script type="text/javascript">window.location.replace("'. $_SESSION["REQUEST_URI"]  . '");</script>';
        exit;
      }       
    }else{
      echo  '<div class="alert alert-warning" align = "center"><strong>Warning!</strong> Incorrect Login. </div>';
    }
  }
}
include('config/footer.php');
?>