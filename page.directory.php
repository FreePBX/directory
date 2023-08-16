<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
if (!defined('FREEPBX_IS_AUTH')) {
	die('No direct script access allowed');
}
$heading = _("Directory");

$view      = $_REQUEST['view'] ?? '';
$usagehtml = '';
switch ($view) {
	case 'form':
		if (isset($_REQUEST['id'])) {
			$usagehtml = FreePBX::View()->destinationUsage(directory_getdest($_REQUEST['id']));
		}
		$content = load_view(__DIR__ . '/views/form.php', [ 'request' => $_REQUEST ]);
		break;
	default:
		$content = load_view(__DIR__ . '/views/grid.php', [ 'request' => $_REQUEST ]);
		break;
}

?>
<div class="container-fluid">
	<h1>
		<?php echo $heading ?>
	</h1>
	<?php echo $usagehtml ?>
	<div class="display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>