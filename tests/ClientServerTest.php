<?php

/**
 * Copyright (C) 2025 Hawkins Computer Services, LLC
 *
 * This file is part of PHP JSON-RPC HTTP.
 *
 * PHP JSON-RPC HTTP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * PHP JSON-RPC HTTP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PHP JSON-RPC HTTP. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Hawkins Computer Services, LLC <dev@hawkinscomputerservices.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2025 Hawkins Computer Services, LLC
 */

namespace Datto\JsonRpc\Http\Tests;

use Datto\JsonRpc\Http\Client;
use Datto\JsonRpc\Responses\ErrorResponse;
use PHPUnit\Framework\TestCase;

class ClientServerTest extends TestCase
{
    private static $serverProcess;
    private static $serverAddress = 'localhost:8088';

    public static function setUpBeforeClass(): void
    {
        // Command to start the built-in PHP server
        $command = sprintf(
            'php -S %s %s',
            self::$serverAddress,
            realpath(__DIR__ . '/../examples/server.php')
        );

        // Start the server process
        self::$serverProcess = proc_open($command, [], $pipes);

        // Give the server a moment to start up
        sleep(1);
    }

    public static function tearDownAfterClass(): void
    {
        // Stop the server process
        if (self::$serverProcess) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }
    }

    public function testSuccessfulQuery()
    {
        $client = new Client('http://' . self::$serverAddress);
        $client->query('add', [5, 3], $result);
        $client->send();

        $this->assertEquals(8, $result);
    }

    public function testErrorQuery()
    {
        $client = new Client('http://' . self::$serverAddress);
        $client->query('add', ['a', 'b'], $error);
        $client->send();

        $this->assertInstanceOf(ErrorResponse::class, $error);
        $this->assertEquals('Invalid params', $error->getMessage());
    }
}

