<?php
  if($access->level <= 1){
    echo '<script type = "text/javascript">alert("Restricted."); window.location.replace("/'.$pagename.'");</script>';
  }
  if(isset($_GET['view'])){
    $account_id = mysqli_real_escape_string($conn, $_GET['view']);
    $user = "SELECT * FROM user where account_id = '$account_id'";
    $resx = $conn->query($user)->fetch_object();
    $user = $conn->query($user);
    if($user->num_rows <= 0){
      echo '<script type = "text/javascript">alert("No record found."); window.location.replace("sys/userlist");</script>';
      exit;
    }   
  }
?>
<div class="breadcrumbs">
  <div class="col-sm-4">
    <div class="page-header float-left">
      <div class="page-title">
        <h1><?php echo $xx; ?></h1>
      </div>
    </div>
  </div>
  <div class="col-sm-8">
    <div class="page-header float-right">
      <div class="page-title">
        <ol class="breadcrumb text-right">
          <li><a href="./">Dashboard</a></li>
          <li><a href="<?php echo $_GET['module'];?>"><?php echo $xx; ?></a></li>
          <li class="active"><?php echo $title;?></li>
        </ol>
      </div>
    </div>
  </div>
</div>
<div class="content mt-3">
  <div class="animated fadeIn">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
              <div class="card-header">
                    <?php 
                      if(!isset($_GET['view'])){
                        echo '<strong class="card-title">Enroll User</strong>';
                      }else{
                        echo '<strong class="card-title">Update User</strong>';
                      }
                    ?>
                  <a style = "margin-left: 2px; margin-right: 2px;" href = "javascript:javascript:history.go(-1)" class="btn btn-danger btn-sm  pull-right"><span class="fa fa-arrow-left"></span></a>&nbsp;
              </div>
              <div class="card-body">
                <form action = "" method="post">
                  <table class="table table-bordered table-hover" width="100%">
                    <?php if(!isset($_GET['view'])){ ?>
                    <tr>
                      <td><label>Username<font color = "red"> * </font></label></td>
                      <td><input type = "text" name = "uname" class = "form-control input-sm" placeholder = "Username" required></td>
                    </tr>                    
                    <?php } ?>  
                    <tr>
                      <td><label>Password<font color = "red"> * </font></label></td>
                      <td><input <?php if(isset($resx->pword)){ echo ' value = "' . $resx->pword . '"'; } ?> type = "password" name = "pword" class = "form-control input-sm" placeholder = "Password" id = "pswx" required></td>
                    </tr>
                    <tr>
                      <td><label>Confirm Password<font color = "red"> * </font></label></td>
                      <td><input <?php if(isset($resx->pword)){ echo ' value = "' . $resx->pword . '"'; } ?> type = "password" name = "cpword" class = "form-control input-sm" placeholder = "Confirm Password" id = "psw1x" required></td>
                    </tr>
                    <tr>
                      <td><label>First Name<font color = "red"> * </font></label></td>
                      <td><input <?php if(isset($resx->fname)){ echo ' value = "' . $resx->fname . '"'; } ?> type = "text" name = "fname" class = "form-control input-sm" placeholder = "First Name" required></td>
                    </tr>
                    <tr>
                      <td><label>Middle Name<font color = "red"> * </font></label></td>
                      <td><input <?php if(isset($resx->mname)){ echo ' value = "' . $resx->mname . '"'; } ?> type = "text" name = "mname" class = "form-control input-sm" placeholder = "Middle Name" required></td>
                    </tr>
                    <tr>
                      <td><label>Last Name<font color = "red"> * </font></label></td>
                      <td><input <?php if(isset($resx->lname)){ echo ' value = "' . $resx->lname . '"'; } ?> type = "text" name = "lname" class = "form-control input-sm" placeholder = "Last Name" required></td>
                    </tr>
                    <tr>
                      <td><label>Level<font color = "red"> * </font></label></td>
                      <td>
                        <select class="form-control input-sm" name = "level" required>
                          <option value=""> - - - </option>
                          <option <?php if(isset($resx->level) && $resx->level == 0){ echo ' selected '; }?> value="0"> Agency </option>
                          <option <?php if(isset($resx->level) && $resx->level == 1){ echo ' selected '; }?> value="1"> H.R. </option>
                          <option <?php if(isset($resx->level) && $resx->level == 2){ echo ' selected '; }?> value="2"> Accounting </option>
                          <option <?php if(isset($resx->level) && $resx->level == 3){ echo ' selected '; }?> value="3"> Cashier </option>
                          <option <?php if(isset($resx->level) && $resx->level == 99){ echo ' selected '; }?> value="99"> Admin </option>
                        </select>
                      </td>
                    </tr>
                    </tr>
                  </table>
                  <hr>
                  <div align = "center">
                      <?php 
                        if(!isset($_GET['view'])){
                          echo '<button class = "btn btn-sm btn-primary center-block" name = "user_sub" id = "submitssx"> Register </button>';
                        }else{
                          echo '<button class = "btn btn-sm btn-success center-block" name = "upuser_sub" id = "submitssx"> Update </button>';
                        }
                      ?>
                  </div>
                </form>
              </div>
          </div>
        </div>
      </div>
  </div>
</div>

<?php
  if(isset($_POST['user_sub'])){
    if(empty($_POST['uname']) && empty($_POST['pword']) && empty($_POST['cpword']) && empty($_POST['fname']) && empty($_POST['mname']) && empty($_POST['lname']) && empty($_POST['level'])){
      echo '<script type = "text/javascript">alert("Check you details."); window.location.replace("accounts/adduser");</script>';
    }else{
      if($_POST['pword'] != $_POST['cpword']){
        echo '<script type = "text/javascript">alert("Password does not match."); window.location.replace("accounts/adduser");</script>';
      }
      $stmt = $conn->prepare("INSERT INTO `user` (fname, mname, lname, uname, pword, level) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssssi", $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['uname'], $_POST['pword'], $_POST['level']);
      if($stmt->execute() === TRUE){
        echo '<script type = "text/javascript">alert("Registration Successfull."); window.location.replace("accounts/adduser");</script>';
        //savelogs("Add User", "First Name -> " . $_POST['fname'] . ", Middle Name -> " . $_POST['mname'] . ", Last Name -> " . $_POST['lname'] . ", Level -> " . $_POST['level']);
      }
    }
  }
  if(isset($_POST['upuser_sub'])){
    if(empty($_POST['fname']) && empty($_POST['mname']) && empty($_POST['lname']) && empty($_POST['level']) && empty($_POST['group'])){
      echo '<script type = "text/javascript">alert("Check you details."); window.location.replace("accounts");</script>';
    }else{
      if($_POST['pword'] != $_POST['cpword']){
        echo '<script type = "text/javascript">alert("Password does not match."); window.location.replace("accounts/adduser/'.$_GET['view'].'");</script>';
      }
      $stmt = $conn->prepare("UPDATE user set fname = ?, mname = ?, lname = ?, level = ?, pword = ? where account_id = ?");
      $stmt->bind_param("sssssi", $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['level'], $_POST['pword'], $account_id);
      if($stmt->execute() === TRUE){
        echo '<script type = "text/javascript">alert("Update User Successfull."); window.location.replace("accounts");</script>';
        //savelogs("Update User", "First Name -> " . $_POST['fname'] . ", Middle Name -> " . $_POST['mname'] . ", Last Name -> " . $_POST['lname'] . ", Level -> " . $_POST['level']);
      }
    }
  }
?>