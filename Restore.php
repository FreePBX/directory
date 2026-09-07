<?php
namespace FreePBX\modules\Directory;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		foreach(($configs['directories'] ?? []) as $dir){
				$this->FreePBX->Directory->updateDirectory($dir);
		}
		foreach (($configs['entries'] ?? []) as $key => $value) {
			$this->FreePBX->Directory->updateEntries($key,$value);
		}
		if(array_key_exists('default', $configs)) {
			$this->FreePBX->Directory->setDefault($configs['default']);
		}
	}
	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyDatabase($pdo);
		try {
			$stmt = $pdo->query("SELECT `value` FROM `admin` WHERE `variable` = 'default_directory'");
			$default = $stmt ? $stmt->fetchColumn() : false;
			if($default !== false) {
				$this->FreePBX->Directory->setDefault($default);
			}
		} catch(\Throwable) {
			// Older backups may not contain the admin table.
		}
	}
}
