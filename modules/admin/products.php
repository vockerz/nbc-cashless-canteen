<?php
  if($access->level < 3){
    alert("/nbc", "No access");
  }
?>
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><?php echo $title; ?></h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="./">Dashboard</a></li>
                    <li class="active"><?php echo $title; ?></li>
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
                <strong class="card-title"><?php echo $title; ?> List</strong>
              	<button class="btn btn-primary btn-sm pull-right" onclick = "addx('products')"><span class="fa fa-plus"></span></button>

              	<hr>               
      					<form action="" method="get">
      						<div class="form-inline">
      							<label>Search: </label>&nbsp;&nbsp; <input autocomplete="off" name = "search" <?php if(isset($_GET['search'])){ echo " value = '" . $_GET['search'] . "' ";} ?> style="min-width: 350px;" type = "text" class="form-control form-control-sm input-sm" placeholder = "Search product.">
      							&nbsp;
      							<?php
      								if(isset($_GET['view']) && $_GET['view'] != ''){
      									echo '<input type = "hidden" value = "' . $_GET['view'] . '" name = "view">';
      								}
      							?>
      							<button type="submit" class="btn btn-primary btn-sm">
      								<i class="fa fa-search"></i>&nbsp;<span></span>
      							</button>
              		</div>
              	</form>
				      </div>
              <div class="card-body">
                  <table class="table">
                    <thead class="thead-dark" align="center">
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Stocks</th>
                        <th scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody align = "center">
                      <?php
                      	$where = " WHERE 1=1 ";
                        if(isset($_GET['search'])){
                        	$where .= " and name like '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%' ";
                        }
                        $counter = "SELECT count(*) as total FROM products " . $where;
                        $counter2 = $conn->query($counter)->fetch_assoc();
                        $perpage = 10;
                        $totalPages = max(1, (int)ceil($counter2['total'] / $perpage));
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
                        
                        $prod = "SELECT * FROM products ".$where." ORDER BY name asc LIMIT " . $startArticle . ', ' . $perpage;
                        $prod = $conn->query($prod);
                        if($prod->num_rows > 0){
                          $num = 0 + $startArticle;
                          while ($row = $prod->fetch_object()) {
                            $num += 1;
                            echo '<tr>';
                              echo '<th scope="row">' . $num . '</th>';
                              echo '<td>' . $row->name . '</td>';
                              echo '<td>' . str_replace(".00", "", number_format(str_replace(",", "", $row->price),2)) . '</td>';
                              echo '<td>' . str_replace(".00", "", number_format(str_replace(",", "", $row->stock),2)) . '</td>';
                              echo '<td>';
                                echo '<a onclick = "edit('.$row->product_id.',\'products\')" name = "edit" class = "btn btn-sm btn-success"><span class="fa fa-pencil-square-o"></span></a>';
                                echo ' <a href = "admin/print?print&barcode=' . $row->barcode . '" target = "_blank" class = "btn btn-sm btn-primary"> <span class="fa fa-print"></span></a>';
                                //echo ' <a onclick = "return confirm(\'Are you sure?\')" href = "admin/delete?idx=' . $row->engr_id . '" class = "btn btn-sm btn-danger"> Delete </a>';
                              echo '</td>';
                            echo '</tr>';
                          }
                        }
                      ?>                   
                    </tbody>
                  </table>
                  <div class="row" style="margin-top: 10px;">
                  <div class="col-12" align="center">
                    <hr>
                    <!--<label>Records <?php $startArticlex = $startArticle + 1; $perpagex = $perpage * $_GET['view']; if($perpagex > $counter2['total']){ $perpagex = $counter2['total'];} echo $startArticlex . ' - ' . $perpagex ?> </label><br>-->
                    <label> <b>Pages</b> </label><br>
                    <nav class ="center-block">
                      <ul class="pagination report-pagination justify-content-center">                          
                      <?php
                      	$search = "";
                      	if(isset($_GET['search'])){
                      		$search = '&search=' . urlencode($_GET['search']);
                      	}
                        $prev = intval($_GET['view'])-1;          
                        if($prev > 0){ 
                          echo '<li class="page-item"><a class="page-link" href="admin/products?view=1' . $search . '">&laquo;&laquo;</a></li>';
                          echo '<li class="page-item"><a class="page-link" href="admin/products?view='. $prev . $search . '">&laquo;</a></li>'; 
                        }else{
                          echo '<li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>';
                          echo '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
                        }
                        foreach(report_pagination_items($_GET['view'], $totalPages) as $page){
                          if($page === "..."){
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                          }elseif($page == $_GET['view']){
                            echo  '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
                          }else{
                            echo  '<li class="page-item"><a class="page-link" href="admin/products?view='. $page . $search . '">' . $page . '</a></li>';
                          }
                        }
                        $nxt = intval($_GET['view'])+1;
                        if($nxt <= $totalPages){ 
                          echo  '<li class="page-item"><a class="page-link" href="admin/products?view='. $nxt . $search . '">&raquo;</a></li>'; 
                          echo  '<li class="page-item"><a class="page-link" href="admin/products?view='. $totalPages . $search . '">&raquo;&raquo;</a></li>'; 
                        }else{
                          echo '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
                          echo '<li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>';
                        }
                      ?>                
                      </ul>
                    </nav>
                  </div>
                </div>
              </div>
          </div>
        </div>
      </div>
  </div>
</div>

<?php
	if(isset($_POST['add'])){
		if(!empty($_POST['name']) && !empty($_POST['price']) && !empty($_POST['barcode'])){
			$_POST['name'] = strtoupper($_POST['name']);      
			$_POST['price'] = strtoupper($_POST['price']);
			$_POST['barcode'] = strtoupper($_POST['barcode']);
			$prodx = "SELECT * FROM products WHERE barcode = '" . mysqli_real_escape_string($conn, $_POST['barcode']) . "'";
			$prodx = $conn->query($prodx);
			if($prodx->num_rows > 0){
				$_POST['barcode'] = rand(0, 99999999);
				$_POST['barcode'] = str_pad($_POST['barcode'], 10, 0, STR_PAD_LEFT);
			}
			$stmt = $conn->prepare("INSERT INTO products (name, price, barcode) VALUES (?, ?, ?)");
			$stmt->bind_param("sss", $_POST['name'], $_POST['price'], $_POST['barcode']);
			if($stmt->execute() === TRUE){
					alert("admin/print?print&barcode=" . $_POST['barcode'], "Adding successfull.");
				}else{
		    	echo $conn->error;
			}
		}else{
		  alert($_SERVER['REQUEST_URI'], "Check your details");
		}
	}
  if(isset($_POST['update'])){
    if(!empty($_POST['name']) && !empty($_POST['price']) && !empty($_POST['barcode'])){
      $_POST['name'] = strtoupper($_POST['name']);      
      $_POST['price'] = strtoupper($_POST['price']); 
      $_POST['barcode'] = strtoupper($_POST['barcode']);
      $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, barcode = ?  where product_id = ?");
      $stmt->bind_param("sssi", $_POST['name'], $_POST['price'], $_POST['barcode'], $_SESSION['edit_id']);
      if($stmt->execute() === TRUE){
        alert($_SERVER['REQUEST_URI'], "Update successfull.");
      }
    }else{
      alert($_SERVER['REQUEST_URI'], "Check your details");
    }
  }
  unset($_SESSION['edit_id']);
?>
