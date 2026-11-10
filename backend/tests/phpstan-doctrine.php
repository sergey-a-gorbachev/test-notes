<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;

require dirname(__DIR__).'/vendor/autoload.php';

$_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///:memory:';

$kernel = new Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('test.service_container')->get(EntityManagerInterface::class);
