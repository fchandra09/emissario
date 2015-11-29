<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); } ?>

<style>
	html, body {
		height: 100%;
	}
	#forgetContainer {
		height: 80%;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	#forgetPanel {
		width: 375px;
	}
	h3 {
		margin-top: 0;
		margin-bottom: 0;
	}
	.col-sm-3 {
		padding-right: 0;
	}
	table {
		margin-top: 35px;
		width: 100%;"
	}
</style>

<div id="forgetContainer" class="container">
	<div id="forgetPanel" class="panel panel-default">
		<div class="panel-heading">
			<h3>Forget Password</h3>
		</div>
		<div class="panel-body">

			<?php echo $GLOBALS["beans"]->siteHelper->getAlertHTML(); ?>

			<form id="form" method="post" action="<?php echo URL_WITH_INDEX_FILE; ?>user/sendForgetEmail" class="form-horizontal">
				<div class="form-group">
					<label for="email" class="col-sm-3 control-label">Email</label>
					<div class="col-sm-9">
						<input type="email" id="email" name="email" class="form-control" required aria-required="true" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						<button type="button" id="cancel" class="btn btn-default">Cancel</button>
						<button type="submit" class="btn btn-default">Reset</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('#cancel').click(function(){
			window.location.href = '<?php echo URL_WITH_INDEX_FILE; ?>';
		});

		$('#form').validate({
			rules: {
				email: {
					email: true
				}
			}
		});
	});
</script>