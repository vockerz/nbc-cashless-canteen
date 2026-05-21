				<div class="row" style="margin-top: 10px;">
					<div class="col-12" align="center">
						<hr>
						<!--<label>Records <?php $startArticlex = $startArticle + 1; $perpagex = $perpage * $_GET['view']; if($perpagex > $counter2['total']){ $perpagex = $counter2['total'];} echo $startArticlex . ' - ' . $perpagex ?> </label><br>-->
						<label> <b>Pages</b> </label><br>
						<nav class ="center-block">
							<ul class="pagination justify-content-center">											    
								<?php
									$prev = intval($_GET['view'])-1;					
									if($prev > 0){ 
										echo '	<li class="page-item">
													<a class="page-link" data-toggle="tooltip" data-placement="top" title="Previous" aria-label="Previous"onclick="indexing(\''.$_GET['module'] . '\',' . $prev . ');"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>'; 
												}
									foreach(range(1, $totalPages) as $page){
										if($page == $_GET['view']){
											echo 	'<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
										}else if($page == 1 || $page == $totalPages || ($page >= $_GET['view'] - 2 && $page <= $_GET['view'] + 2)){
											if($page == 0){
												continue;
											}
											echo 	'<li class="page-item"><a class = "page-link" data-toggle="tooltip" data-placement="top" title="Page ' . $page . '" onclick="indexing(\''.$_GET['module'] . '\',' . $page . ')">' . $page . '</a></li>';
										}
									}
									$nxt = intval($_GET['view'])+1;
									if($nxt <= $totalPages){ 
										echo 	'<li class="page-item"><a class="page-link" data-toggle="tooltip" data-placement="top" title="Next" aria-label="Next" onclick="indexing(\''.$_GET['module'] . '\',' . $nxt . ')"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>'; 
									}
								?>								
							</ul>
						</nav>
					</div>
				</div>
				