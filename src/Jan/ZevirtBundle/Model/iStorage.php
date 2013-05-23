<?php

namespace Jan\ZevirtBundle\Model;

interface iStorage {

    public function getDiskEntity();

    public function create($disk, $size);
}

