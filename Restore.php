<?php
namespace FreePBX\modules\Directory;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = reset($this->getConfigs());
    foreach($configs['directories'] as $dir){
        $this->FreePBX->Directory->updateDirectory($dir);
    }
    foreach ($configs['entries'] as $key => $value) {
      $this->FreePBX->Directory->updateEntries($key,$value);
    }
    $this->FreePBX->Directory->setDefault($configs['default']);
  }
  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
    $tables = array_flip($tables + $unknownTables);
    if (!isset($tables['directory_entries'])) {
      return $this;
    }
    $bmo = $this->FreePBX->Directory();
    $bmo->setDatabase($pdo);
    $directories = $bmo->listDirectories();
    $entries = [];
    foreach ($directories as $dir) {
      $entries[$dir['id']] = $bmo->getEntriesById($dir['id']);
    }
    $configs = [
      'directories' => $directories,
      'default' => $  $bmo->getDefault(),
      'entries' => $entries,
    ];
    $bmo->resetDatabase();
    foreach ($configs['directories'] as $dir) {
      $bmo->updateDirectory($dir);
    }
    foreach ($configs['entries'] as $key => $value) {
      $bmo->updateEntries($key, $value);
    }
    $bmo->setDefault($configs['default']);
    return $this;
  }
}
