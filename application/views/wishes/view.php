<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); } ?>

<div class="container">
	<h2 class="page-header">Wish Details</h2>
	<div class="section form-horizontal">
		<div class="form-group">
			<label class="col-sm-2 control-label">Description</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Description ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Origin City</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Origin_City ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Origin Country</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Origin_Country_Name ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Destination City</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Destination_City ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Destination Country</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Destination_Country_Name ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Status</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Status ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Weight</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Weight ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Compensation</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?php echo $wish->Compensation ?></p>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="button" id="back" class="btn btn-default">Back</button>
				<?php if (strcasecmp("Open", $wish->Status) == 0) { ?>
					<button type="button" id="edit" class="btn btn-default">Edit</button>
					<button type="button" id="delete" class="btn btn-default">Delete</button>
				<?php } elseif (strcasecmp("Helped", $wish->Status) == 0) { ?>
					<button type="button" id="close" class="btn btn-default">Close</button>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="section">
		<h3 class="page-header">Helps</h3>

		<?php if (strcasecmp("Open", $wish->Status) == 0) { ?>
			<div class="clearfix table-action">
				<button type="button" id="request" class="btn btn-default">Request for Help</button>
			</div>
		<?php } ?>

		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%">&nbsp;</th>
						<th width="1%">&nbsp;</th>
						<th>Helper</th>
						<th>
							Status
							<a id="statusInfo" tabindex="0" role="button" data-toggle="popover" class="info-button">
								<i class="glyphicon glyphicon-info-sign"></i>
							</a>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($helps as $help) { ?>
						<tr>
							<td width="1%" class="column-action">
								<span title="Send a Message" data-userID="<?php echo $help->User_ID; ?>" data-wishID="<?php echo $help->Wish_ID; ?>">
									<i class="glyphicon glyphicon-envelope"></i>
								</span>
								<?php if ((strcasecmp("Closed", $wish->Status) == 0) && ($help->Requested == 1) && ($help->Offered == 1) && (!is_numeric($help->Review_ID))) { ?>
									<span title="Write a Review">
										<i class="glyphicon glyphicon-pencil"></i>
									</span>
								<?php }
								else if ((strcasecmp("Open", $wish->Status) == 0) && ($help->Requested == 0) && ($help->Offered == 1)) { ?>
									<span title="Accept Help Offer" data-id="<?php echo $help->ID; ?>">
										<i class="glyphicon glyphicon-ok"></i>
									</span>
								<?php } ?>
							</td>
							<td width="1%" class="column-action">
								<span title="View Connection" data-id="<?php echo $help->User_ID; ?>">
									<i class="glyphicon glyphicon-user"></i>
								</span>
							</td>
							<td><?php echo $help->Helper_First_Name . " " . $help->Helper_Last_Name ?></td>
							<td>
								<?php if ($help->Requested == 1 && $help->Offered == 1) {
									echo "Accepted";
								}
								else if ($help->Requested == 1) {
									echo "Requested";
								}
								else if ($help->Offered == 1) {
									echo "Offered";
								} ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="section">
		<h3 class="page-header">Messages</h3>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Message Date</th>
						<th>Sender</th>
						<th>Recipient</th>
						<th>Title</th>
						<th>Content</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($messages as $message) { ?>
						<tr>
							<td>
								<a href="<?php echo URL_WITH_INDEX_FILE . "messages/view/" . $message->ID; ?>">
									<?php echo $message->Formatted_Created_On ?>
								</a>
							</td>
							<td><?php echo $message->Sender_First_Name . " " . $message->Sender_Last_Name ?></td>
							<td><?php echo $message->Recipient_First_Name . " " . $message->Recipient_Last_Name ?></td>
							<td><?php echo $message->Title ?></td>
							<td class="truncate"><?php echo $message->Content ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('#back').click(function(){
			window.location.href = '<?php echo URL_WITH_INDEX_FILE; ?>wishes';
		});

		<?php if (strcasecmp("Open", $wish->Status) == 0) { ?>
			$('#edit').click(function(){
				window.location.href = '<?php echo URL_WITH_INDEX_FILE . "wishes/edit/" . $wishID; ?>';
			});

			$('#delete').click(function(){
				if (confirm('Are you sure you want to delete this wish?'))
				{
					window.location.href = '<?php echo URL_WITH_INDEX_FILE . "wishes/delete/" . $wishID; ?>';
				}
			});

			$('#request').click(function(){
				window.location.href = '<?php echo URL_WITH_INDEX_FILE . "wishes/request/" . $wishID; ?>';
			});

			$('td.column-action').find('i.glyphicon-ok').closest('span').click(function(){
				window.location.href = '<?php echo URL_WITH_INDEX_FILE; ?>wishes/acceptHelpOffer/' + $(this).attr('data-id');
			});

		<?php } elseif (strcasecmp("Helped", $wish->Status) == 0) { ?>
			$('#close').click(function(){
				if (confirm('Are you sure you want to close this wish?'))
				{
					window.location.href = '<?php echo URL_WITH_INDEX_FILE . "wishes/close/" . $wishID; ?>';
				}
			});
		<?php } elseif (strcasecmp("Closed", $wish->Status) == 0) { ?>
			$('td.column-action').find('i.glyphicon-pencil').closest('span').click(function(){
				window.location.href = '<?php echo URL_WITH_INDEX_FILE . "reviews/add/" . $wishID; ?>';
			});
		<?php } ?>

		$('td.column-action').find('i.glyphicon-user').closest('span').click(function(){
			window.open('<?php echo URL_WITH_INDEX_FILE; ?>friends/viewConnection/' + $(this).attr('data-id'), 'connection', 'width=600, height=600, scrollbars, resizable');
		});

		$('td.column-action').find('i.glyphicon-envelope').closest('span').click(function(){
			window.location.href = '<?php echo URL_WITH_INDEX_FILE; ?>messages/add/0/' + $(this).attr('data-userID') + '/' + $(this).attr('data-wishID');
		});

		$('#statusInfo').popover({
			container: 'body',
			html: true,
			placement: 'auto right',
			title: 'Status Info',
			trigger: 'focus',
			content: '<b>Requested:</b> I have requested for help, but the helper has not accepted the request.<br/>' +
					'<b>Offered:</b> The helper has offered to help, but I have not accepted the offer.<br/>' +
					'<b>Accepted:</b> Both the helper and I have agreed on the help.'
		});

	});
</script>