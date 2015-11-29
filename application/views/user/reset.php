<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); } ?>

<style>
	html, body {
		height: 100%;
	}
	#resetContainer {
		height: 80%;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	#resetPanel {
		width: 575px;
	}
	h3 {
		margin-top: 0;
		margin-bottom: 0;
	}
	.col-sm-4 {
		padding-right: 0;
	}
</style>

<div id="resetContainer" class="container">
	<div id="resetPanel" class="panel panel-default">
		<div class="panel-heading">
			<h3>Reset Password</h3>
		</div>
		<div class="panel-body">
			<?php if ($resetInfo->Valid) { ?>
				<form id="form" method="post" action="<?php echo URL_WITH_INDEX_FILE; ?>user/resetPassword" class="form-horizontal">
					<input type="hidden" id="resetID" name="resetID" value="<?php echo $resetID; ?>" />
					<input type="hidden" id="resetKey" name="resetKey" value="<?php echo $resetKey; ?>" />

					<div class="form-group">
						<label for="email" class="col-sm-4 control-label">Email</label>
						<div class="col-sm-8">
							<p class="form-control-static"><?php echo $resetInfo->Email ?></p>
						</div>
					</div>
					<div class="form-group">
						<label for="password" class="col-sm-4 control-label">New Password</label>
						<div class="col-sm-8">
							<input type="password" id="password" name="password" class="form-control" required aria-required="true" />
						</div>
					</div>
					<div class="form-group">
						<label for="confirmPassword" class="col-sm-4 control-label">Confirm New Password</label>
						<div class="col-sm-8">
							<input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required aria-required="true" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-4 col-sm-8">
							<button type="button" id="cancel" class="btn btn-default">Cancel</button>
							<button type="submit" class="btn btn-default">Submit</button>
						</div>
					</div>
				</form>
			<?php } else { ?>
				<div class="alert alert-danger" role="alert">
					This reset link is invalid.
				</div>
			<?php } ?>
		</div>
	</div>
</div>

<?php if ($resetInfo->Valid) { ?>
	<script>
		$(document).ready(function(){
			$('#cancel').click(function(){
				window.location.href = '<?php echo URL_WITH_INDEX_FILE; ?>';
			});
	
			$('#form').validate({
				rules: {
					confirmPassword: {
						equalTo: '#password'
					}
				},
				messages: {
					confirmPassword: {
						equalTo: 'Confirm password should match password.'
					}
				}
			});
		});
	</script>
<?php } ?>