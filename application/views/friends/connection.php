<?php if (!$this) { exit(header('HTTP/1.0 403 Forbidden')); }

if ($connected) {
	$containerWidth = 300 * $graphData->Max_Branch;
	if ($containerWidth > 992) {
		$containerWidth = 992;
	}

	$containerHeight = 100 * ($graphData->Max_Degree + 1);
}
?>

<div class="container">
	<?php if ($connected) { ?>
		<div id="connectionContainer" style="width:<?php echo $containerWidth; ?>px; height:<?php echo $containerHeight; ?>px; margin:0 auto;"></div>
	<?php } else { ?>
		<div class="alert alert-info" role="alert">
			This user and you are not connected within five degrees of separation.
		</div>
	<?php } ?>
</div>

<?php if ($connected) { ?>
	<script>
		$(document).ready(function(){
			var cy = cytoscape({
				container: $('#connectionContainer'),
				userZoomingEnabled: false,
				elements: [
					{
						data: { id: '<?php echo $userID; ?>', name:'Me' }
					}
				<?php foreach ($graphData->Nodes as $connectionID => $connectionName) {
					if ($connectionID != $userID) { ?>
					,{
						data: { id: '<?php echo $connectionID; ?>', name:'<?php echo $connectionName; ?>' }
					}
				<?php }}
				foreach ($graphData->Edges as $edge) { ?>
					,{
						data: { source: '<?php echo $edge->From; ?>', target: '<?php echo $edge->To; ?>' }
					}
				<?php } ?>
				],
				style: [
					{
						selector: 'node',
						style: {
							'content': 'data(name)',
							'background-color': '#009aad',
							'color': '#fff',
							'text-outline-width': 4,
							'text-outline-color': '#009aad',
							'text-valign': 'center',
							'font-family': '"Roboto","Helvetica Neue",Helvetica,Arial,sans-serif'
						}
					},
					{
						selector: 'edge',
						style: {
							'width': 2,
							'line-color': '#009aad'
						}
					}
				],
				layout: {
					name: 'breadthfirst',
					padding: 10,
					spacingFactor: 0.75,
					directed: true,
					roots: ['<?php echo $userID; ?>']
				}
			});
		});
	</script>
<?php } ?>