		</div>
		<button type="button" class="btn btn-secondary mb-1" id = "modaltrig" data-toggle="modal" data-target="#modalx" style="display: none;"></button>
		<div class="modal fade" id="modalx" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
			<div class="modal-dialog modal-md" role="document" id = "ajax">
				
			</div>
		</div>
		<script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/plugins.js"></script>
		<script src="assets/js/main.js"></script>
		<script src="js/chosen.jquery.min.js"></script>
		<script>
			function getRndInteger(min, max) {
				string = Math.floor(Math.random() * (max - min)) + min;
				return string.toString().padStart(10, 0);
			}
			function loadReportPage(url, pushState) {
				$.get(url, function(response) {
					var html = $.parseHTML(response, document, true);
					var nextBreadcrumbs = $(html).find('.breadcrumbs').first();
					var nextContent = $(html).find('.content.mt-3').first();
					if (!nextContent.length) {
						window.location.href = url;
						return;
					}
					if (nextBreadcrumbs.length) {
						$('.breadcrumbs').first().replaceWith(nextBreadcrumbs);
					}
					$('.content.mt-3').first().replaceWith(nextContent);
					if (pushState) {
						window.history.pushState({ reportAjax: true }, '', url);
					}
				}).fail(function() {
					window.location.href = url;
				});
			}
			$(document).on('click', '.report-pagination a.page-link', function(e) {
				var url = this.href;
				if (url.indexOf('/reports/') === -1) {
					return;
				}
				e.preventDefault();
				loadReportPage(url, true);
			});
			$(window).on('popstate', function() {
				if (window.location.href.indexOf('/reports/') !== -1) {
					loadReportPage(window.location.href, false);
				}
			});
		</script>
	</body>
</html>
<?php
	if( isset($conn) && is_resource($conn) ){
		$conn->close(); 
	}
?>
