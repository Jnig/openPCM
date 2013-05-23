<?php
namespace Jan\ZevirtBundle\Tests;

use Jan\ZevirtBundle\Model\iConnection;
use Jan\ZevirtBundle\Model\ConnectionException;

class Driver implements iConnection
{

    public $commands = array(
            'virsh pool-refresh',
            'virsh vol-list',
            'virsh vol-dumpxml',
            'qemu-img info'
        );
    
    
    public function exec($cmd) {
        $strlen = 0;
        $machted = '';
        foreach ($this->commands as $value) {
            if (strpos($cmd, $value) !== false) {
                
                if (strlen($value) > $strlen) {
                   $strlen = strlen($value); 
                   $machted = $value;
                }
            }
        }
        if ($strlen > 0) {
            $machted = str_replace('/', '', $machted);
            $out = file_get_contents(__DIR__.'/CommandOutput/'.$machted);
            return $this->cleanOutput($out);
        } else {
            throw new Exception("cmd $cmd not found");
        }


    }


    
    public function filePutContents ($file, $content) {

        try {
          file_put_contents("ssh2.sftp://".$this->getSftp().$file, $content);
        } catch (\Exception $e) {
            echo  $e->getMessage()."\n";
        } 
        return $this;
    }
    
    public function fileGetContents($file) {
        try {
            $content = file_get_contents("ssh2.sftp://".$this->getSftp().$file);
        } catch (\Exception $e) {
            return '';
        } 
        return $content;
    }
    
    public function fileExists($file) {
        if (file_exists("ssh2.sftp://".$this->getSftp().$file)) {
            return 1;            
        } else {
            return 0;
        }
    }
    
    
    public function cleanOutput($out) {
        $out = str_replace('setlocale: No such file or directory', '', $out);
        
        return trim($out);
    }
    

    


}

