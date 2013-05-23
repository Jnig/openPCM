<?php

exec('rm '.__DIR__.'/cache/test/test.db');
exec('php '.__DIR__.'/console doctrine:schema:create --env=test');
exec('yes | php '.__DIR__.'/console doctrine:fixtures:load --env=test');

require __DIR__.'/bootstrap.php.cache';