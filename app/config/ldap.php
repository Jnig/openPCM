<?php

if ($container->getParameter('ldap') == 'on') {
    $this->import('security_ldap.yml');
} else {
    $this->import('security_normal.yml');
}
?>
