<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); }

$wishOrigin = $wish->Origin_City;
if ($wish->Origin_Country_Name != "" && $wishOrigin != "") {
	$wishOrigin .= ", ";
}
$wishOrigin .= $wish->Origin_Country_Name;

$wishDestination = $wish->Destination_City;
if ($wish->Destination_Country_Name != "" && $wishDestination != "") {
	$wishDestination .= ", ";
}
$wishDestination .= $wish->Destination_Country_Name;
?>

<div class="container">
	<h2 class="page-header">Request for Help</h2>

	<div class="section form-horizontal">
		<div class="form-group">
			<label class="col-sm-2 control-label">Wish Description</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Description ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Origin</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wishOrigin; ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Destination</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wishDestination; ?></p>
			</div>
		</div>
	</div>

	<form id="form" method="post" action="<?php echo URL_WITH_INDEX_FILE; ?>wishes/saveHelpRequests" class="form-horizontal">
		<input type="hidden" id="wishID" name="wishID" value="<?php echo $wishID ?>" />
		<input type="hidden" id="helperIDs" name="helperIDs" />

		<div style="margin-bottom:30px;">
			<div class="table-responsive" style="overflow-y:auto; max-height:500px;">
				<table id="potentialHelpers" class="table table-striped">
					<thead>
						<tr>
							<th width="1%">&nbsp;</th>
							<th width="1%">&nbsp;</th>
							<th>Name</th>
							<th>Location</th>
							<th>Friends</th>
							<th>Recommended</th>
							<th>Travel Plan</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($valid) {
							foreach ($potentialHelpers as $helper) { ?>
								<tr>
									<td width="1%">
										<input type="checkbox" class="helper-checkbox" value="<?php echo $helper->ID; ?>" />
									</td>
									<td width="1%" class="column-action">
										<span title="View Connection" data-id="<?php echo $helper->ID; ?>">
											<i class="glyphicon glyphicon-user"></i>
										</span>
									</td>
									<td><?php echo $helper->First_Name . " " . $helper->Last_Name ; ?></td>
									<td>
										<?php $location = $helper->City;
										if (strcasecmp("United States", $helper->Country_Name) == 0) {
											if ($helper->State_Name != "" && $location != "") {
												$location .= ", ";
											}
											$location .= $helper->State_Name;
										}
										else {
											if ($helper->Country_Name != "" && $location != "") {
												$location .= ", ";
											}
											$location .= $helper->Country_Name;
										}
										echo $location; ?>
									</td>
									<td>
										<?php $friendStatusNumber = $GLOBALS["beans"]->stringHelper->left($helper->Friend_Status, 1);
										if ($friendStatusNumber == "1") {
											echo "Yes";
										}
										else {
											echo "No";
										} ?>
									</td>
									<td><?php echo $helper->Recommendation_Score; ?> %</td>
									<td>
										<?php if ($helper->Formatted_Travel_Date != "") {
											echo $helper->Origin_City . ", " . $helper->Origin_Country_Name . " &mdash; " . $helper->Destination_City . ", " . $helper->Destination_Country_Name . " on " . $helper->Formatted_Travel_Date;
										} ?>
									</td>
								</tr>
							<?php }
						} ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="button" id="cancel" class="btn btn-default">Cancel</button>
				<button type="submit" class="btn btn-default">Save</button>
			</div>
		</div>
	</form>
</div>

<script>
	$(document).ready(function(){
		$('#cancel').click(function(){
			window.location.href = '<?php echo URL_WITH_INDEX_FILE . "wishes/view/" . $wishID; ?>';
		});

		$('td.column-action').find('i.glyphicon-user').closest('span').click(function(){
			window.open('<?php echo URL_WITH_INDEX_FILE; ?>friends/viewConnection/' + $(this).attr('data-id'), 'connection', 'width=600, height=600, scrollbars, resizable');
		});

		$.validator.addMethod('atLeastOne', function() {
			  return $('input.helper-checkbox:checked').length > 0 ? true : false;
		}, 'Please select at least one user.');

		$('#form').validate({
			ignore: '',
			rules: {
				helperIDs: {
					atLeastOne: true
				}
			},
			errorPlacement: function (error, element) {
				error.insertAfter($('#potentialHelpers').closest('.table-responsive'));
			},
			highlight: function(element) {
				$('#potentialHelpers').closest('.table-responsive').parent().removeClass('has-success').addClass('has-error');
			},
			unhighlight: function(element) {
				$('#potentialHelpers').closest('.table-responsive').parent().removeClass('has-error').addClass('has-success');
			}
		});

		$('#form').submit(function() {
			var helperIDs = '';
			$('input.helper-checkbox:checked').each(function() {
				helperIDs = helperIDs + ',' + $(this).val();
			});
			if (helperIDs.substr(0,1) == ',') {
				helperIDs = helperIDs.substr(1);
			}

			$('#helperIDs').val(helperIDs);
		});
	});

</script>