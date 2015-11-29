<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); }

if (is_numeric($wishID))
{
	$cancelURL = URL_WITH_INDEX_FILE . "wishes/view/" . $wishID;
}
else
{
	$cancelURL = URL_WITH_INDEX_FILE . "reviews";
}

$wishDescription = "";
$helpID = "";
$helperID = "";
$helperName = "";
?>

<div class="container">

	<?php if (count($unreviewedWishes) == 0) { ?>
		<div class="alert alert-info" role="alert">
			You have no unreviewed helpers.
			You can only write a review for an accepted helper from one of your closed wishes.
		</div>
	<?php } ?>

	<h2 class="page-header">New Reviews</h2>
	<form id="form" method="post" action="<?php echo URL_WITH_INDEX_FILE; ?>reviews/save" class="form-horizontal">
		<div class="form-group">
			<label for="recipientID" class="col-sm-2 control-label">Wish</label>
			<div class="col-sm-10">
				<select id="wishID" name="wishID" class="form-control" onchange="repopulateStaticInfo();">
					<option value="">- Wish -</option>
					<?php foreach ($unreviewedWishes as $wish) { ?>
						<option value="<?php echo $wish->ID; ?>" <?php if (is_numeric($wishID) && ($wish->ID == $wishID)) { ?>selected<?php } ?>>
							<?php if (strlen($wish->Description) > 125) {
								echo $GLOBALS["beans"]->stringHelper->left($wish->Description, 125) . "...";
							}
							else {
								echo $wish->Description;
							} ?>
						</option>
					<?php
						if (is_numeric($wishID) && ($wish->ID == $wishID)) {
							$wishDescription = $wish->Description;
							$helpID = $wish->Help_ID;
							$helperID = $wish->Helper_ID;
							$helperName = $wish->Helper_First_Name . " " . $wish->Helper_Last_Name;
						}
					} ?>
				</select>
				<input type="hidden" id="helpID" name="helpID" value="<?php echo $helpID; ?>" required aria-required="true" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<p id="wishLink" class="form-control-static">
					<?php if ($wishDescription != "") { ?>
						<a href="<?php echo URL_WITH_INDEX_FILE . "wishes/view/" . $wishID; ?>" target="_new">
							<?php echo $wishDescription; ?>
						</a>
					<?php } ?>
				</p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Helper</label>
			<div class="col-sm-10">
				<p id="helperName" class="form-control-static"><?php echo $helperName; ?></p>
				<input type="hidden" id="helperID" name="helperID" value="<?php echo $helperID; ?>" required aria-required="true" />
			</div>
		</div>
		<div class="form-group required">
			<label for="title" class="col-sm-2 control-label">Recommended</label>
			<div class="col-sm-10">
				<div class="radio radio-inline" style="padding-left:0;">
					<label>
						<input type="radio" id="recommendedYes" name="recommended" value="1" />
						Yes
					</label>
				</div>
				<div class="radio radio-inline">
					<label>
						<input type="radio" id="recommendedNo" name="recommended" value="0" />
						No
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="col-sm-2 control-label">Comments</label>
			<div class="col-sm-10">
				<textarea id="comments" name="comments" class="form-control" rows="5"></textarea>
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
			window.location.href = '<?php echo $cancelURL; ?>';
		});

		$('#form').validate({
			ignore: ':hidden:not(#helpID,#helperID),#wishID',
			rules: {
				recommended: {
					required: true
				}
			},
			messages: {
				helpID: 'Please select a valid wish.'
			}
		});

		<?php if ($wishDescription == "") { ?>
			$('#wishLink').closest('.form-group').hide();
		<?php } ?>
	});

	repopulateStaticInfo = function() {
		var wishID = $('#wishID').val();

		if (wishID == '') {
			clearStaticInfo();
		}
		else {
			$.ajax({
				url: '<?php echo URL_WITH_INDEX_FILE; ?>reviews/getValidWishInfo/' + wishID,
				async: false,
				cache: false,
				method: 'POST',
				dataType: 'json',
				success: function(result) {
					if (result.ID != '') {
						$('#wishLink').html('<a href="<?php echo URL_WITH_INDEX_FILE; ?>wishes/view/' + result.ID + '" target="_new">' + result.Description + '</a>');
						$('#helperName').html(result.Helper_First_Name + ' ' + result.Helper_Last_Name);

						$('#helpID').val(result.Help_ID);
						$('#helperID').val(result.Helper_ID);

						$('#wishLink').closest('.form-group').show();
					}
					else {
						clearStaticInfo();
					}
				},
				error: function() {
					clearStaticInfo();
				}
			});
		}

		$('#form').valid();
	}

	clearStaticInfo = function() {
		$('#wishLink').empty();
		$('#helperName').empty();

		$('#helpID').val('');
		$('#helperID').val('');

		$('#wishLink').closest('.form-group').hide();
	}
</script>