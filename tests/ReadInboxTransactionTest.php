<?php

namespace  UpnQr\Tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use UpnQr\Models\ReadInboxTransaction;

final class ReadInboxTransactionTest extends TestCase {

    public function testRead(){
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
        $dotenv->load();

        $readInboxTransaction = new ReadInboxTransaction([
            'host'          => $_ENV['HOST'],
            'port'          => $_ENV['PORT'],
            'username'      => $_ENV['EMAIL'],
            'password'      => $_ENV['PASSWORD'],
        ]);

        $transactions = $readInboxTransaction->read();
    }
}
