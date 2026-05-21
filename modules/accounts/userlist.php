<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Inventory</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="./">Accounts</a></li>
                    <li class="active">User List</li>
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
                  <strong class="card-title">User List</strong>
                  <a class="btn btn-primary btn-sm pull-right" href = "accounts/adduser"><span class="fa fa-plus"></span></a> 
              </div>
              <div class="card-body">
                  <table class="table">
                    <thead class="thead-dark"  align="center">
                      <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>User Level</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $counter = "SELECT count(*) as total FROM user";
                        $counter2 = $conn->query($counter)->fetch_assoc();
                        $perpage = 15;
                        $totalPages = ceil($counter2['total'] / $perpage);
                        if(!isset($_GET['view'])){
                            $_GET['view'] = 0;
                        }else{
                            $_GET['view'] = (int)$_GET['view'];
                        }
                        if($_GET['view'] < 1){
                            $_GET['view'] = 1;
                        }else if($_GET['view'] > $totalPages){
                            $_GET['view'] = $totalPages;
                        }
                        $startArticle = ($_GET['view'] - 1) * $perpage;
                        $list = "SELECT * FROM user ORDER BY account_id LIMIT " . $startArticle . ', ' . $perpage;
                        $res = $conn->query($list);
                        if($res->num_rows > 0){
                          $num = 0;
                          while ($row = $res->fetch_object()) {
                            if($row->level == 0){ $level = 'Cashier'; } elseif($row->level == 99){ $level = 'Administrator'; };
                            $num += 1;
                            echo  '<tr>';
                            echo    '<td>' . $num . '</td>';
                            echo    '<td>' . $row->fname . ' ' . $row->mname . ' ' . $row->lname . '</td>';
                            echo    '<td>' . $level . '</td>';
                            echo    '<td><a href = "accounts/adduser/' . $row->account_id . '" class = "btn btn-warning btn-sm" data-toggle="tooltip" title="Edit User"><span class = "fa fa-edit"><span></a></td>'; 
                            echo  '</tr>';
                          }
                        }

                      ?>
                    </tbody>
                  </table>
              </div>
          </div>
        </div>
      </div>
  </div>
</div>
<?php
  if(isset($_POST['add'])){
    if(!empty($_POST['d_date']) && !empty($_POST['qty'])){
      if(isset($_POST['checkbox'])){
        $sql = "SELECT * FROM raw WHERE raw_desc = '" . mysqli_real_escape_string($conn, $_POST['new_raw']) . "'";
        if($conn->query($sql)->num_rows > 0){
          alert("admin/raw", "Already exist.");
          exit;
        }
        $stmt = $conn->prepare("INSERT INTO raw (raw_desc) VALUES (?)");
        $stmt->bind_param("s", $_POST['new_raw']);
        if($stmt->execute() === TRUE){
          $_POST['raw'] = $conn->insert_id;
        }
      }
      $stmt = $conn->prepare("INSERT INTO r_stocks (raw_id, d_date, qty) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $_POST['raw'], $_POST['d_date'], $_POST['qty']);
      if($stmt->execute() === TRUE){
        $stmt2 = $conn->prepare("UPDATE raw SET qty = (qty + ?) WHERE raw_id = ?");
        $stmt2->bind_param("di", $_POST['qty'], $_POST['raw']);
        $stmt2->execute();
        alert("admin/raw", "Adding successfull.");
      }
    }else{
      alert("admin/raw", "Check your details");
    }
  }
?>