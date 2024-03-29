<?php
namespace FreePBX\modules;

//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015-2018 Sangoma Technologies.
//
use BMO;
use FreePBX_Helpers;
use PDO;
use Exception;

class Directory extends FreePBX_Helpers implements BMO {

	public function install() {
		$files = [ 'cdir-please-enter-first-three.wav', 'cdir-transferring-further-assistance.wav', 'cdir-matching-entries-continue.wav', 'cdir-there-are.wav', 'cdir-welcome.wav', 'cdir-sorry-no-entries.wav', 'cdir-matching-entries-or-pound.wav' ];
		$path  = $this->FreePBX->Config->Get('ASTVARLIBDIR');

		foreach ($files as $file) {
			if (is_link($path . '/sounds/fr/' . $file) && !file_exists($path . '/sounds/fr/' . $file)) {
				unlink($path . '/sounds/fr/' . $file);
			}
		}
	}

	public function uninstall() {
	}

	public function doConfigPageInit($page) {
		$request           = $_REQUEST;
		$request['action'] = !empty($request['action']) ? $request['action'] : "";
		switch ($page) {
			case 'directory':
				//check for ajax request and process that immediately
				if (isset($_REQUEST['ajaxgettr'])) { //got ajax request
					$opts = $opts = explode('|', urldecode((string) $_REQUEST['ajaxgettr']));
					if ($opts[0] == 'all') {
						echo directory_draw_entries_all_users($opts[1]);
					}
					else {
						if ($opts[0] != '') {
							$real_id  = $opts[0];
							$name     = '';
							$realname = $opts[1];
							$audio    = 'vm';
						}
						else {
							$real_id  = 'custom';
							$name     = $opts[1];
							$realname = 'Custom Entry';
							$audio    = 'tts';
						}
						echo directory_draw_entries_tr($opts[0], $real_id, $name, $realname, $audio, '', $opts[2]);
					}
					exit;
				}
				$requestvars = [ 'id', 'action', 'entries', 'newentries', 'def_dir', 'Submit' ];
				foreach ($requestvars as $var) {
					$rvars_def = match ($var) {
						'def_dir' => false,
						default => '',
					};
					${$var}    = $_REQUEST[$var] ?? $rvars_def;
				}

				if (isset($Submit) && $Submit == 'Submit' && isset($def_dir) && $def_dir !== false) {
					directory_save_default_dir($def_dir);
				}
				break;
		}
		if ($page == 'directory') {
			//get variables for directory_details
			$requestvars = [ 'id', 'dirname', 'description', 'announcement', 'callid_prefix', 'alert_info', 'repeat_loops', 'repeat_recording', 'invalid_recording', 'invalid_destination', 'retivr', 'say_extension', 'rvolume' ];
			foreach ($requestvars as $var) {
				$vars[$var] = $_REQUEST[$var] ?? null;
			}
			$action  = $_REQUEST['action'] ?? null;
			$entries = $_REQUEST['entries'] ?? [];
			switch ($action) {
				case 'edit':
					//get real dest
					if (isset($_REQUEST['goto0']) && isset($_REQUEST[$_REQUEST['goto0'] . '0']))
						$vars['invalid_destination'] = $_REQUEST[$_REQUEST['goto0'] . '0'];
					$vars['id'] = directory_save_dir_details($vars);
					\directory_save_dir_entries($vars['id'], $entries);
					$this_dest = directory_getdest($vars['id']);
					\fwmsg::set_dest($this_dest[0]);
					needreload();
					$_REQUEST['id'] = $vars['id'];
					unset($_REQUEST['view']);
					break;
				case 'delete':
					directory_delete($vars['id']);
					needreload();
					break;
			}
		}
	}
	public function getGrid() {
		$results = $this->listDirectories();
		$def_dir = $this->getDefault();
		$dirs    = [];
		if ($results) {
			foreach ($results as $key => $result) {
				$result['default'] = false;
				if (!$result['dirname']) {
					$result['dirname'] = 'Directory ' . $result['id'];
				}
				if ($result['id'] == $def_dir) {
					$result['default'] = true;
				}
				$dirs[] = [ 'id' => $result['id'], 'name' => $result['dirname'], 'link' => [ 'id' => $result['id'], 'name' => $result['dirname'] ], 'default' => [ 'id' => $result['id'], 'default' => $result['default'] ] ];
			}
		}
		return $dirs;
	}
	public function listDirectories($complete = false) {
		$data = $complete ? '*' : 'id,dirname';
		$sql  = 'SELECT ' . $data . ' FROM directory_details ORDER BY dirname';
		$stmt = $this->Database->prepare($sql);
		$stmt->execute();
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);
		return $results;
	}

	public function getallnames($id) {
		$sql        = 'SELECT `dirname` FROM directory_details';
		$parameters = [];

		if ($id) {
			$sql .= ' WHERE id != :id';
			$parameters[':id'] = $id;
		}

		$stmt = $this->Database->prepare($sql);
		$stmt->execute($parameters);

		$ret     = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
		$results = $ret ?: [];
		return $results;
	}

	public function getDefault() {
		$sql  = "SELECT value FROM `admin` WHERE `variable` = 'default_directory'";
		$stmt = $this->Database->prepare($sql);
		$stmt->execute();
		$ret = $stmt->fetchColumn();
		return $ret ?: '';
	}

	public function setDefault($id) {
		$sql = "REPLACE INTO `admin` (`variable`, value) VALUES ('default_directory',:id)";
		$this->Database->prepare($sql)->execute([ ':id' => $id ]);
		return $this;
	}

	public function getEntriesById($id) {
		$sql  = "SELECT a.name, a.type, a.audio, a.dial, a.foreign_id, a.e_id, b.name foreign_name, IF(a.name != \"\",a.name,b.name) realname
		FROM directory_entries a LEFT JOIN users b ON a.foreign_id = b.extension WHERE id = :id ORDER BY realname";
		$stmt = $this->Database->prepare($sql);
		$stmt->execute([ ':id' => $id ]);
		return $stmt->fetchall(PDO::FETCH_ASSOC);
	}

	public function updateDirectory($vals) {
		$sql    = 'REPLACE INTO directory_details (id,dirname,description,announcement,
		callid_prefix,alert_info,repeat_loops,repeat_recording,
		invalid_recording,invalid_destination,retivr,say_extension,rvolume)
		VALUES (:id,:dirname,:description,:announcement,
		:callid_prefix,:alert_info,:repeat_loops,:repeat_recording,
		:invalid_recording,:invalid_destination,:retivr,:say_extension,:rvolume)';
		$insert = [
			'id'                  => $vals['id'],
			'dirname'             => $vals['dirname'],
			'description'         => $vals['description'],
			'announcement'        => $vals['announcement'],
			'callid_prefix'       => $vals['callid_prefix'],
			'alert_info'          => $vals['alert_info'],
			'repeat_loops'        => $vals['repeat_loops'],
			'repeat_recording'    => $vals['repeat_recording'],
			'invalid_recording'   => $vals['invalid_recording'],
			'invalid_destination' => $vals['invalid_destination'],
			'retivr'              => $vals['retivr'],
			'say_extension'       => $vals['say_extension'],
			'rvolume'             => !empty($vals['rvolume']) ? $vals['rvolume'] : '',
		];
		$this->Database->prepare($sql)->execute($insert);
		return $vals['id'];
	}

	public function addDirectory($vals) {
		$sql = 'INSERT INTO directory_details (dirname,description,announcement,
		callid_prefix,alert_info,repeat_loops,repeat_recording,
		invalid_recording,invalid_destination,retivr,say_extension,rvolume)
		VALUES (:dirname,:description,:announcement,
		:callid_prefix,:alert_info,:repeat_loops,:repeat_recording,
		:invalid_recording,:invalid_destination,:retivr,:say_extension,:rvolume)';

		$insert = [
			'dirname'             => $vals['dirname'],
			'description'         => $vals['description'],
			'announcement'        => $vals['announcement'],
			'callid_prefix'       => $vals['callid_prefix'],
			'alert_info'          => $vals['alert_info'],
			'repeat_loops'        => $vals['repeat_loops'],
			'repeat_recording'    => $vals['repeat_recording'],
			'invalid_recording'   => $vals['invalid_recording'],
			'invalid_destination' => $vals['invalid_destination'],
			'retivr'              => $vals['retivr'],
			'say_extension'       => $vals['say_extension'],
			'rvolume'             => !empty($vals['rvolume']) ? $vals['rvolume'] : '',
		];
		$this->Database->prepare($sql)->execute($insert);
		return $this->Database->lastinsertid('id');
	}

	public function deleteEntriesById($id) {
		$sql = "DELETE FROM directory_entries WHERE id = :id";
		$this->Database->prepare($sql)->execute([ ':id' => $id ]);
		return $this;
	}

	public function updateEntries($id, $entries) {
		$this->deleteEntriesById($id);
		$sql  = 'INSERT INTO directory_entries (id, e_id, name,type,foreign_id,audio,dial) VALUES (:id, :e_id, :name, :type, :foriegn_id, :audio,:dial)';
		$stmt = $this->Database->prepare($sql);
		foreach ($entries as $idx => $row) {
			if ('custom' == $row['foreign_id'] && '' == trim((string) $row['name']) || '' == $row['foreign_id']) {
				continue; //dont insert a blank row
			}
			$type       = 'user';
			$foreign_id = $row['foreign_id'];
			if ($row['foreign_id'] == 'custom') {
				$type       = 'custom';
				$foreign_id = '';
			}
			$audio = '' != $row['audio'] ? $row['audio'] : ('custom' == $row['foreign_id'] ? 'tts' : 'vm');
			$stmt->execute([
				':id'         => $id,
				':e_id'       => $idx,
				':name'       => ($row['name'] ?? ''),
				':type'       => $type,
				':foriegn_id' => $foreign_id,
				':audio'      => $audio,
				':dial'       => ($row['num'] ?? ''),
			]);
		}
		return $this;
	}

	public function getActionBar($request) {
		$buttons = [];
		switch ($request['display']) {
			case 'directory':
				$buttons = [ 'delete' => [ 'name' => 'delete', 'id' => 'delete', 'value' => _('Delete') ], 'reset' => [ 'name' => 'reset', 'id' => 'reset', 'value' => _('Reset') ], 'submit' => [ 'name' => 'submit', 'id' => 'submit', 'value' => _('Submit') ] ];
				if (empty($request['id'])) {
					unset($buttons['delete']);
				}
				if (empty($request['view']) || $request['view'] != 'form') {
					$buttons = [];
				}
				break;
		}
		return $buttons;
	}
	public function ivrHook($request) {
		$ivr = [];
		if (isset($request['id'])) {
			$ivr = $this->FreePBX->Ivr->getDetails($request['id']);
		}
		$directdial = $ivr['directdial'] ?? '';
		$dirs       = directory_list();
		$options    = '$("<option />", {text: \'' . _("Disabled") . '\'}).appendTo(sel);';
		$options .= '$("<option />", {val: \'ext-local\', text: \'' . _("Enabled") . '\'}).appendTo(sel);';
		foreach ($dirs as $dir) {
			$name    = $dir['dirname'] ?: 'Directory ' . $dir['id'];
			$options .= '$("<option />", {val: \'' . $dir['id'] . '\', text: \'' . $name . '\'}).appendTo(sel);';
		}
		$html = '
			<script type="text/javascript">
				var sel = $("<select id=\"directdial\" name=\"directdial\" class=\"form-control\" />");
				var target = $("#directdialyes").parent();
			';
		$html .= $options;
		$html .= '
				$(target).html(sel);
				$("#directdial").find("option").each( function() {
  					var $this = $(this);
  					if ($this.val() == "' . $directdial . '") {
						$this.prop("selected", true);
	 					return false;
  					}
				});
			</script>
		';
		return $html;
	}
	public function ajaxRequest($req, &$setting) {
		return match ($req) {
			'getJSON' => true,
			default => false,
		};
	}
	public function ajaxHandler() {
		return match ($_REQUEST['command']) {
			'getJSON' => match ($_REQUEST['jdata']) {
					'grid' => $this->getGrid(),
					default => false,
				},
			default => false,
		};
	}
	public function getRightNav($request) {
		if (isset($request['view']) && $request['view'] == 'form') {
			return load_view(__DIR__ . "/views/bootnav.php", []);
		}
	}
}