<?php

namespace App\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Create new database for each client identified by a cookie, delete old databases.
 * (Cannot use session id, because Symfony likes to destroy that on authentication events.)
 * This works only with this app (sqlite with 'user' table expected, seed file location hardwired )
 */
class CookieSeparatedConnectionDriver extends AbstractDriverMiddleware
{
    public const COOKIE_NAME = 'db_unique_id';

    public function __construct(
        private readonly RequestStack $requestStack,
        Driver $wrappedDriver
    ) {
        parent::__construct($wrappedDriver);
    }


    public function connect(array $params): Connection
    {
        $request = $this->requestStack->getCurrentRequest();
        $uniqueId = $request->cookies->get(self::COOKIE_NAME) ?? uniqid();
        // cookie will be actually set in response in App\EventSubscriber\KernelSubscriber
        $request->cookies->set(self::COOKIE_NAME, $uniqueId);

        $dbPathPattern = $params['path'];
        $params['path'] = str_replace('uniqueId', $uniqueId, $dbPathPattern);
        $dbDir = dirname($params['path']);

        if (!file_exists($dbDir)) {
            mkdir($dbDir, recursive: true);
        }

        $connection = parent::connect($params);
        $result = $connection->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='user'");
        $count = (int) $result->fetchOne();

        if ($count === 0) {
            $seedFile = dirname(__DIR__, 2) . '/seed/db_seed.sql';
            $sql = file_get_contents($seedFile);
            $connection->exec($sql);

            // keep only freshest N databases
            $globPattern = str_replace('uniqueId', '*', $dbPathPattern);
            $existingDbs = [];
            foreach (glob($globPattern) as $path) {
                $existingDbs[] = ['path' => $path, 'mtime' => filemtime($path)];
            }
            // sort oldest last
            usort($existingDbs, fn($a, $b) =>  $b['mtime'] - $a['mtime']);
            // remove old databases
            foreach (array_slice($existingDbs, 5) as $oldDb) {
                unlink($oldDb['path']);
            }
        }

        return $connection;
    }
}