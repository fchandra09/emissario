<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); } ?>

<style>
	html, body {
		height: 100%;
	}
	#loginContainer {
		height: 80%;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	#loginPanel {
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

<?php if ($GLOBALS["beans"]->siteHelper->getSession("forgetEmailSent") != "") {
	$_SESSION["forgetEmailSent"] = ""; ?>
	<div class="container">
		<div class="alert alert-warning" role="alert">
			Please check your email for the instruction to reset your password.
		</div>
	</div>
<?php } elseif ($GLOBALS["beans"]->siteHelper->getSession("passwordChanged") != "") {
	$_SESSION["passwordChanged"] = ""; ?>
	<div class="container">
		<div class="alert alert-success" role="alert">
			Password reset successful. Please login using the form below.
		</div>
	</div>
<?php } ?>

<div id="loginContainer" class="container">
	<div id="loginPanel" class="panel panel-default">
		<div class="panel-heading">
			<h3>Login</h3>
		</div>
		<div class="panel-body">

			<?php echo $GLOBALS["beans"]->siteHelper->getAlertHTML(); ?>

			<form id="form" method="post" action="<?php echo URL_WITH_INDEX_FILE; ?>user/login" class="form-horizontal">
				<div class="form-group">
					<label for="email" class="col-sm-3 control-label">Email</label>
					<div class="col-sm-9">
						<input type="email" id="email" name="email" class="form-control" required aria-required="true" />
					</div>
				</div>
				<div class="form-group">
					<label for="password" class="col-sm-3 control-label">Password</label>
					<div class="col-sm-9">
						<input type="password" id="password" name="password" class="form-control" required aria-required="true" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						<button type="submit" class="btn btn-default">Login</button>
					</div>
				</div>
			</form>
	
			<table>
				<tr>
					<td class="text-center">
						<a href="<?php echo URL_WITH_INDEX_FILE; ?>user/forget">Forget Password</a>
					</td>
					<td class="text-center">
						<a href="<?php echo URL_WITH_INDEX_FILE; ?>user/signUp">Create an Account</a>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('#form').validate({
			rules: {
				email: {
					email: true
				}
			}
		});
	});
</script>