<?php
function footer_site($view = NULL){
?>

<div class="row footer main_row">
<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
	<div class="col-xl-8 col-lg-10">
		<div class="footer_sub">
		    <div class="copyright">© 2024 Комитет ветеринарии города Москвы</div>
		</div>
	</div>
	<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
</div>
<?php if ($view) $view->endBody(); ?>
</body>
</html>

<?php
}
?>