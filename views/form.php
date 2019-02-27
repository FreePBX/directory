<?php
extract($request);
if(empty($action) && empty($id)){
	$subhead = _("Add Directory");
	$action = $id = "";
}else{
	$subhead = _("Edit Directory");
	$dir = directory_get_dir_details($request['id']);
	extract($dir);
}
$recoptions = '<option value="0">'._("Default").'</option>';
if(function_exists('recordings_list')){
	foreach(recordings_list() as $r){
		$selected = (!empty($announcement) && $r['id'] == $announcement)?' SELECTED':'';
		$recoptions .= "<option value='$r[id]'$selected>$r[displayname]</option>";
	}
}
$invreprecoptions = '<option value="0">'._("Default").'</option>';
if(function_exists('recordings_list')){
	foreach(recordings_list() as $r){
		$selected = (!empty($repeat_recording) && $r['id'] == $repeat_recording)?' SELECTED':'';
		$invreprecoptions .= "<option value='$r[id]'$selected>$r[displayname]</option>";
	}
}
$invrecoptions = '<option value="0">'._("Default").'</option>';
if(function_exists('recordings_list')){
	foreach(recordings_list() as $r){
		$selected = (!empty($invalid_recording) && $r['id'] == $invalid_recording)?' SELECTED':'';
		$invrecoptions .= "<option value='$r[id]'$selected>$r[displayname]</option>";
	}
}
?>
<h2><?php echo $subhead ?></h2>
<form action="config.php?display=directory" method="post" name="frm_dirform" class="fpbx-submit" id="dirform" data-fpbx-delete="?display=directory&amp;action=delete&amp;id=<?php echo $id?>">

<!--Directory Name-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="dirname"><?php echo _("Directory Name") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="dirname"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="dirname" name="dirname" value="<?php echo isset($dirname) ? stripslashes($dirname) : ""?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="dirname-help" class="help-block fpbx-help-block"><?php echo _("Name of this directory")?></span>
		</div>
	</div>
</div>
<!--END Directory Name-->
<!--Directory Description-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="description"><?php echo _("Directory Description") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="description" name="description" value="<?php echo isset($description) ? stripslashes($description) : ""?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="description-help" class="help-block fpbx-help-block"><?php echo _("Description of this directory.")?></span>
		</div>
	</div>
</div>
<!--END Directory Description-->
<!--CallerID Name Prefix-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="callid_prefix"><?php echo _("CallerID Name Prefix") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="callid_prefix"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="callid_prefix" name="callid_prefix" value="<?php echo isset($callid_prefix) ? stripslashes($callid_prefix) : ""?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="callid_prefix-help" class="help-block fpbx-help-block"><?php echo _("Prefix to be appended to current CallerID Name.")?></span>
		</div>
	</div>
</div>
<!--END CallerID Name Prefix-->
<!--Alert Info-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="alert_info"><?php echo _("Alert Info") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="alert_info"></i>
					</div>
					<div class="col-md-9">
						<?php echo FreePBX::View()->alertInfoDrawSelect("alert_info",isset($alert_info) ? stripslashes($alert_info) : "");?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="alert_info-help" class="help-block fpbx-help-block"><?php echo _("ALERT_INFO to be sent when called from this Directory. Can be used for distinctive ring for SIP devices.")?></span>
		</div>
	</div>
</div>
<!--END Alert Info-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="rvolume"><?php echo _("Ringer Volume Override") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="rvolume"></i>
					</div>
					<div class="col-md-9">
						<select class="form-control" id="rvolume" name="rvolume">
							<option value="0"><?php echo _("None")?></option>
							<?php for($i = 1; $i <= 14; $i++) { ?>
								<option value="<?php echo $i?>" <?php echo ($rvolume == $i) ? 'selected' : ''?>><?php echo $i?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="rvolume-help" class="help-block fpbx-help-block"><?php echo sprintf(_("Override the ringer volume. Note: This is only valid for %s phones at this time"),"Sangoma")?></span>
		</div>
	</div>
</div>
<!--Announcement-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="announcement"><?php echo _("Announcement") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="announcement"></i>
					</div>
					<div class="col-md-9">
						<select class="form-control" id="announcement" name="announcement">
							<?php echo $recoptions ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="announcement-help" class="help-block fpbx-help-block"><?php echo _("Greeting to be played on entry to the directory.")?></span>
		</div>
	</div>
</div>
<!--END Announcement-->
<!--Invalid Retries-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="repeat_loops"><?php echo _("Invalid Retries") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="repeat_loops"></i>
					</div>
					<div class="col-md-9">
						<input type="number" max='10' min='0' class="form-control" id="repeat_loops" name="repeat_loops" value="<?php echo isset($repeat_loops)?$repeat_loops:'3' ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="repeat_loops-help" class="help-block fpbx-help-block"><?php echo _("Number of times to retry when receiving an invalid/unmatched response from the caller")?></span>
		</div>
	</div>
</div>
<!--END Invalid Retries-->
<!--Invalid Retry Recording-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="repeat_recording"><?php echo _("Invalid Retry Recording") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="repeat_recording"></i>
					</div>
					<div class="col-md-9">
						<select class="form-control" id="repeat_recording" name="repeat_recording">
							<?php echo $invreprecoptions ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="repeat_recording-help" class="help-block fpbx-help-block"><?php echo _("Prompt to be played when an invalid/unmatched response is received, before prompting the caller to try again")?></span>
		</div>
	</div>
</div>
<!--END Invalid Retry Recording-->
<!--Invalid Recording-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="invalid_recording"><?php echo _("Invalid Recording") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="invalid_recording"></i>
					</div>
					<div class="col-md-9">
						<select class="form-control" id="invalid_recording" name="invalid_recording">
							<?php echo $invrecoptions?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="invalid_recording-help" class="help-block fpbx-help-block"><?php echo _("Prompt to be played before sending the caller to an alternate destination due to the caller pressing 0 or receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries)")?></span>
		</div>
	</div>
</div>
<!--END Invalid Recording-->
<!--Invalid Destination-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="goto0"><?php echo _("Invalid Destination") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="goto0"></i>
					</div>
					<div class="col-md-9">
						<?php echo drawselects(isset($invalid_destination)?$invalid_destination:null,0)?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="goto0-help" class="help-block fpbx-help-block"><?php echo _("Destination to send the call to after Invalid Recording is played.")?></span>
		</div>
	</div>
</div>
<!--END Invalid Destination-->
<!--Return to IVR-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="retivr"><?php echo _("Return to IVR") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="retivr"></i>
					</div>
					<div class="col-md-9 radioset">
						<input type="radio" name="retivr" id="retivryes" value="1" <?php echo (isset($retivr) && $retivr == "1"?"CHECKED":"") ?>>
						<label for="retivryes"><?php echo _("Yes");?></label>
						<input type="radio" name="retivr" id="retivrno" value="0" <?php echo (isset($retivr) && $retivr == "1"?"":"CHECKED") ?>>
						<label for="retivrno"><?php echo _("No");?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="retivr-help" class="help-block fpbx-help-block"><?php echo _("When selected, if the call passed through an IVR that had \"Return to IVR\" selected, the call will be returned there instead of the Invalid destination.")?></span>
		</div>
	</div>
</div>
<!--END Return to IVR-->
<!--Announce Extension-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="say_extension"><?php echo _("Announce Extension") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="say_extension"></i>
					</div>
					<div class="col-md-9 radioset">
						<input type="radio" name="say_extension" id="say_extensionyes" value="1" <?php echo (isset($say_extension) && $say_extension == "1"?"CHECKED":"") ?>>
						<label for="say_extensionyes"><?php echo _("Yes");?></label>
						<input type="radio" name="say_extension" id="say_extensionno" value="0" <?php echo (isset($say_extension) && $say_extension == "1"?"":"CHECKED") ?>>
						<label for="say_extensionno"><?php echo _("No");?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="say_extension-help" class="help-block fpbx-help-block"><?php echo _("When checked, the extension number being transferred to will be announced prior to the transfer")?></span>
		</div>
	</div>
</div>
<!--END Announce Extension-->
<?php echo directory_draw_entries(isset($dir) ? $dir['id'] : "")?>
<input type='hidden' name="action" value="edit">
<input type='hidden' name="id" value="<?php echo $id?>">
<input type='hidden' name="display" value="directory">
<input type='hidden' name="view" value="form">
</form>
<script>
var dirnames = <?php print json_encode(\FreePBX::Directory()->getallnames($id)); ?>;
</script>
