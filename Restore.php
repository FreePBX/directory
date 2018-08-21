<?php
namespace FreePBX\modules\Directory;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $cb = $this->FreePBX->Directory();
    $configs = reset($this->getConfigs());
    foreach($configs['directories'] as $dir){
        $cb->updateDirectory($dir);
    }
    foreach ($configs['entries'] as $key => $value) {
        $cb->updateEntries($key,$value);
    }
    $cb->setDefault($configs['default']);
  }
  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
    $tables = array_flip($tables + $unknownTables);
    if (!isset($tables['directory_entries'])) {
      return $this;
    }
    $cb = $this->FreePBX->Directory();
    $cb->setDatabase($pdo);
    $data = $cb->listCallbacks();
    $directories = $cb->listDirectories();
    $entries = [];
    foreach ($directories as $dir) {
      $entries[$dir['id']] = $cb->getEntriesById($dir['id']);
    }
    $configs = [
      'directories' => $directories,
      'default' => $cb->getDefault(),
      'entries' => $entries,
    ];
    $cb->resetDatabase();
    foreach ($data as $callback) {
      $cb->upsert($callback['callback_id'], $callback['description'], $callback['callbacknum'], $callback['destination'], $callback['sleep'], $callback['deptname']);
    }
    return $this;
  }
}
