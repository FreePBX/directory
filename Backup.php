<?php
namespace FreePBX\modules\Directory;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $directories = $this->FreePBX->Directory->listDirectories(true);
    $entries = [];
    foreach($directories as $dir){
        $entries[$dir['id']] = $this->FreePBX->Directory->getEntriesById($dir['id']);
    }
    $configs = [
        'directories' => $directories,
        'default' => $this->FreePBX->Directory->getDefault(),
        'entries' => $entries,
    ];
    $this->addDependency('recordings');
    $this->addConfigs($configs);
  }
}
