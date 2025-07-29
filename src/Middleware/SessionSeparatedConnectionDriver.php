<?php

namespace App\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Create new database for each session, delete old databases.
 * This works only with this app (sqlite with 'user' table expected, seed file location hardwired )
 */
class SessionSeparatedConnectionDriver extends AbstractDriverMiddleware
{
    public function __construct(
        private readonly RequestStack $requestStack,
        Driver $wrappedDriver
    ) {
        parent::__construct($wrappedDriver);
    }


    public function connect(array $params): Connection
    {
        try {
            $session = $this->requestStack->getSession();
            $sessionId = $session->getId();
        } catch (SessionNotFoundException) {
            $sessionId = '';
        }
        $dbPathPattern = $params['path'];
        $params['path'] = str_replace('sessionId', $sessionId, $dbPathPattern);
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
            $globPattern = str_replace('sessionId', '*', $dbPathPattern);
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