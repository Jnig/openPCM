<?php

namespace Jan\ZevirtBundle\Model;

interface iConnection {

    public function fileGetContents($file);

    public function filePutContents($file, $content);

    public function exec($cmd);
}

