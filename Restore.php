<?php
namespace FreePBX\modules\Directory;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$cb = $this->FreePBX->Directory();
		$configs = $this->getConfigs();
		foreach($configs['directories'] as $dir){
				$this->FreePBX->Directory->updateDirectory($dir);
		}
		foreach ($configs['entries'] as $key => $value) {
			$this->FreePBX->Directory->updateEntries($key,$value);
		}
		$this->FreePBX->Directory->setDefault($configs['default']);
	}
	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyDatabase($pdo);
	}
}
