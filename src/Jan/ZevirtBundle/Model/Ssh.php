<?php

namespace Jan\ZevirtBundle\Model;

use Jan\ZevirtBundle\Model\iConnection;
use Jan\ZevirtBundle\Model\ConnectionException;

class Ssh implements iConnection {

    private $ip;
    private $password;
    private $privateKey;
    private $publicKey;
    private $user;
    private $port;
    private $connection;
    private $sftp;

    public function __construct() {
        $this->privateKey = __DIR__ . '/../../../../app/config/ssh/id_rsa';
        $this->publicKey = __DIR__ . '/../../../../app/config/ssh/id_rsa.pub';
        if (!file_exists($this->privateKey)) {
            throw new \Exception('no private key at ' . $this->privateKey);
        }

        if (!file_exists($this->publicKey)) {
            throw new \Exception('no public key at ' . $this->publicKey);
        }
    }

    public function setIp($ip) {
        if (strpos($ip, ':') !== false) {
            list ($ip, $port) = explode(':', $ip);
            $this->ip = $ip;
            $this->port = $port;
        } else {
            $this->ip = $ip;
            $this->port = 22;
        }

        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function setPrivateKey($file) {
        $this->privateKey = $file;
        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function connect() {

        $this->connection = @ssh2_connect($this->ip, $this->port);
        if ($this->connection) {
            if (!empty($this->password)) {
                if (!ssh2_auth_password($this->connection, $this->user, $this->password)) {
                    throw new ConnectionException('Authentification failed');
                }

                $this->exec('mkdir -p /root/.ssh/');
                $this->addSshKey('/root/.ssh/authorized_keys', array(file_get_contents($this->publicKey)));
            } else {


                if (!@ssh2_auth_pubkey_file($this->connection, $this->user, $this->publicKey, $this->privateKey
                        )) {
                    throw new ConnectionException("Authentification failed with public key\n{$this->publicKey}\n{$this->privateKey}");
                }
            }



            return $this;
        } else {
            throw new ConnectionException('Cannot connect to server');
        }
    }

    public function exec($cmd) {
        $cmd = "LC_ALL='en_US.UTF-8' " . $cmd;

        if ($this->connection) {
            if (!is_string($cmd)) {
                throw new Exception($cmd . ' is not a string');
            }


            $stdio = ssh2_exec($this->connection, $cmd);
            stream_set_blocking($stdio, true);

            $stdout = stream_get_contents($stdio);
            $stderr = stream_get_contents(ssh2_fetch_stream($stdio, SSH2_STREAM_STDERR));

            $stderr = $this->cleanOutput($stderr);
            if (!empty($stderr)) {
                throw new \Exception('Command failed with output' . $this->cleanOutput($stdout . $stderr));
            }

            return $this->cleanOutput($stdout . $stderr);
        } else {
            throw new ConnectionException('Cannot connect to server');
        }
    }

    public function execBackground($cmd) {
        $cmd = "LC_ALL='en_US.UTF-8' " . $cmd . ' > /tmp/log 2>&1 &';

        if ($this->connection) {
            if (!is_string($cmd)) {
                throw new Exception($cmd . ' is not a string');
            }


            $stdio = ssh2_exec($this->connection, $cmd);
            stream_set_blocking($stdio, true);

            $stdout = stream_get_contents($stdio);
            $stderr = stream_get_contents(ssh2_fetch_stream($stdio, SSH2_STREAM_STDERR));

            return $stdout . $stderr;
        } else {
            throw new Exception('Cannot connect to server');
        }
    }

    public function execWithStreamReturn($cmd) {
        $cmd = "LC_ALL='en_US.UTF-8' " . $cmd;
        $stdio = ssh2_exec($this->connection, $cmd);
        stream_set_blocking($stdio, true);
        return $stdio;
    }

    public function execWithReturnCode($cmd, &$output) {
        $cmd = "LC_ALL='en_US.UTF-8' " . $cmd;
        $cmd = $cmd . ' ; echo $?';
        $stdio = ssh2_exec($this->connection, $cmd);
        stream_set_blocking($stdio, true);

        $stdout = stream_get_contents($stdio);
        $stderr = stream_get_contents(ssh2_fetch_stream($stdio, SSH2_STREAM_STDERR));

        $stdout = explode("\n", $stdout);

        array_pop($stdout); // delete last array element, because it is empty
        $returnCode = array_pop($stdout);

        $stdout = implode("\n", $stdout);

        $output = $stdout . $stderr;

        return $returnCode;
    }

    public function getSftp() {
        if (!$this->sftp) {
            $this->sftp = ssh2_sftp($this->connection);
        }
        return $this->sftp;
    }

    public function filePutContents($file, $content) {

        try {
            file_put_contents("ssh2.sftp://" . $this->getSftp() . $file, $content);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
        return $this;
    }

    public function fileGetContents($file) {
        try {
            $content = file_get_contents("ssh2.sftp://" . $this->getSftp() . $file);
        } catch (\Exception $e) {
            return '';
        }
        return $content;
    }

    public function fileExists($file) {
        if (file_exists("ssh2.sftp://" . $this->getSftp() . $file)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function execWithAgentForward($cmd) {
        $config = Zend_Registry::get('config');
        echo ("ssh -o StrictHostKeyChecking=no -A -i " . $config->get('ssh')->get('privateKey') . " $this->user@$this->ip " . escapeshellarg($cmd));
        shell_exec("ssh -o StrictHostKeyChecking=no -A -i " . $config->get('ssh')->get('privateKey') . " $this->user@$this->ip " . escapeshellarg($cmd));
    }

    public function execAsFile($cmd, $background = 0) {
        $file = '/tmp/cmd_' . uniqid();

        $cmd = str_replace("\r\n", "\n", $cmd);
        $this->filePutContents($file, $cmd);
        $this->exec('chmod +x ' . $file);




        if ($background) {
            $this->exec($file . ' > /tmp/log 2>&1 &');
        } else {
            $stream = $this->execWithStreamReturn($file);
            $out = '';

            while ($line = fgets($stream)) {
                $out .= $line;
                Default_Model_JobsDetails::addDetails($line);
            }

            return $out;
        }

        $this->exec('rm ' . $file);
    }

    public function __destruct() {
        if ($this->connection) {

            $this->connection = null;
        }
    }

    public function addSshKey($file, array $keys) {
        if ($this->fileExists($file)) {
            $authorizedKeysArray = explode("\n", $this->fileGetContents($file));
        } else {
            $authorizedKeysArray = array();
        }

        foreach ($authorizedKeysArray as $key => $value) {
            if (empty($value)) {
                unset($authorizedKeysArray[$key]);
            } else {
                $authorizedKeysArray[$key] = trim($value);
            }
        }

        foreach ($keys as $key => $value) {
            if (empty($value)) {
                unset($keys[$key]);
            } else {
                $keys[$key] = trim($value);
            }
        }



        $add = array_merge($authorizedKeysArray, $keys);


        $add = array_unique($add); // remove duplicate values


        $this->filePutContents($file, implode("\n", $add) . "\n");
    }

    public function removeSshKeys($file, array $keys) {
        $authorizedKeysArray = explode("\n", $this->fileGetContents($file));


        foreach ($keys as $key) {
            if (array_search($key, $authorizedKeysArray) !== false) {

                unset($authorizedKeysArray[array_search($key, $authorizedKeysArray)]);
            }
        }

        $this->filePutContents($file, implode("\n", $authorizedKeysArray) . "\n");
    }

    public function cleanOutput($out) {
        $out = str_replace('setlocale: No such file or directory', '', $out);

        return trim($out);
    }

}

