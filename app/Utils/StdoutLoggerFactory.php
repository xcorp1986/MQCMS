<?php
declare(strict_types=1);

namespace App\Utils;

use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return Log::get();
    }
}