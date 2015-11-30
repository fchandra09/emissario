<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); } ?>

<div class="container">
	<h2 class="page-header">Offer To Help</h2>

	<form id="form" method="post" action="<?php echo URL_WITH_INDEX_FILE; ?>helps/saveHelpOffers" class="form-horizontal">
		<input type="hidden" id="wishIDs" name="wishIDs" />

		<div style="margin-bottom:30px;">
			<div class="table-responsive" style="overflow-y:auto; max-height:500px;">
				<table id="potentialWishes" class="table table-striped">
					<thead>
						<tr>
							<th width="1%">&nbsp;</th>
							<th>Description</th>
							<th>Destination</th>
							<th>Owner</th>
							<th>Location</th>
							<th>Friends</th>
							<th width="1%">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($potentialWishes as $wish) { ?>
							<tr>
								<td width="1%">
									<input type="checkbox" class="wish-checkbox" value="<?php echo $wish->ID; ?>" />
								</td>
								<td class="truncate"><?php echo $wish->Description; ?></td>
								<td>
									<?php $destination = $wish->Destination_City;
									if ($wish->Destination_Country_Name != "" && $destination != "") {
										$destination .= ", ";
									}
									$destination .= $wish->Destination_Country_Name;
									echo $destination; ?>
								</td>
								<td><?php echo $wish->Owner_First_Name . " " . $wish->Owner_Last_Name ; ?></td>
								<td>
									<?php $location = $wish->Owner_City;
									if (strcasecmp("United States", $wish->Owner_Country_Name) == 0) {
										if ($wish->Owner_State_Name != "" && $location != "") {
											$location .= ", ";
										}
										$location .= $wish->Owner_State_Name;
									}
									else {
										if ($wish->Owner_Country_Name != "" && $location != "") {
											$location .= ", ";
										}
										$location .= $wish->Owner_Country_Name;
									}
									echo $location; ?>
								</td>
								<td>
									<?php $friendStatusNumber = $GLOBALS["beans"]->stringHelper->left($wish->Friend_Status, 1);
									if ($friendStatusNumber == "1") {
										echo "Yes";
									}
									else if ($friendStatusNumber == "2") {
										echo "Pending Friend's Approval";
									}
									else if ($friendStatusNumber == "3") {
										echo "Pending My Approval";
									}
									else {
										echo "No";
									} ?>
								</td>
								<td width="1%" class="column-action">
									<span title="View Connection" data-id="<?php echo $wish->Owner_ID; ?>">
										<i class="glyphicon glyphicon-user"></i>
									</span>
								</td>
							</tr>
						<?php } ?>
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
			window.location.href = '<?php echo URL_WITH_INDEX_FILE; ?>helps';
		});

		$('td.column-action').find('i.glyphicon-user').closest('span').click(function(){
			window.open('<?php echo URL_WITH_INDEX_FILE; ?>friends/viewConnection/' + $(this).attr('data-id'), 'connection', 'width=600, height=600, scrollbars, resizable');
		});

		$.validator.addMethod('atLeastOne', function() {
			  return $('input.wish-checkbox:checked').length > 0 ? true : false;
		}, 'Please select at least one wish.');

		$('#form').validate({
			ignore: '',
			rules: {
				wishIDs: {
					atLeastOne: true
				}
			},
			errorPlacement: function (error, element) {
				error.insertAfter($('#potentialWishes').closest('.table-responsive'));
			},
			highlight: function(element) {
				$('#potentialWishes').closest('.table-responsive').parent().removeClass('has-success').addClass('has-error');
			},
			unhighlight: function(element) {
				$('#potentialWishes').closest('.table-responsive').parent().removeClass('has-error').addClass('has-success');
			}
		});

		$('#form').submit(function() {
			var wishIDs = '';
			$('input.wish-checkbox:checked').each(function() {
				wishIDs = wishIDs + ',' + $(this).val();
			});
			if (wishIDs.substr(0,1) == ',') {
				wishIDs = wishIDs.substr(1);
			}

			$('#wishIDs').val(wishIDs);
		});
	});
</script>