<?php

/**
 * This file is part of marbocub/network-equipment.
 *
 * Copyright (c) 2021 marbocub. <marbocub@gmail.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed 
 * with this source code.
 *
 * @license https://github.com/marbocub/network-equipment/blob/master/LICENSE
 * @link https://github.com/marbocub/network-equipment
 */

namespace Marbocub\NetworkEquipment;

use PHPUnit\Framework\TestCase;
use Graze\TelnetClient\TelnetResponse;
use Graze\TelnetClient\TelnetClient as ParentTelnetClient;

class TelnetClientTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        $socket = $this->createMock(\Socket\Raw\Socket::class);
        $this->client = $this->getMockBuilder(TelnetClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'execute'])
            ->getMock();
        $this->client->setSocket($socket);
        $this->client->setPrompt("\S+[>#]\s?");
        $this->client->setPromptError("(([uU]ser[nN]ame|[lL]og[iI]n|[pP]ass[wW]ord):\s?|% (Login invalid|Bad passwords|Bad secrets))");
    }

    /** @test */
    public function testWaitForPrompt()
    {
        $this->client->method('getResponse')
            ->willReturn(
                new TelnetResponse(false, "\n\nCisco#", ["Cisco#"]),
                new TelnetResponse(false, "\n\nOVERRUN#", ["OVERRUN#"])
            );

        $response = $this->client->waitForPrompt();
        $this->assertFalse($response->isError());
        $this->assertEquals("Cisco#", $response->getPromptMatches()[0]);
    }

    /** @test */
    public function testLogin()
    {
        $this->client->method('getResponse')
            ->willReturnOnConsecutiveCalls(
                new TelnetResponse(true,
                     "\n"
                    ."User Access Verification\n"
                    ."\n"
                    ."Username: ",
                    ["Username: "]),
                new TelnetResponse(false, "\n\nOVERRUN>", ["OVERRUN>"])
            );
        $this->client->method('execute')
            ->willReturnMap([
                [
                    'username', null, null,
                    new TelnetResponse(true, "\nPassword: ", ["Password: "]),
                ],
                [
                    'password', null, null,
                    new TelnetResponse(false, "\nCisco>", ["Cisco>"]),
                ],
            ]);

        $response = $this->client->login("username", "password");
        $this->assertFalse($response->isError());
        $this->assertEquals("Cisco>", $response->getPromptMatches()[0]);
    }

    /** @test */
    public function testEnable()
    {
        $this->client->method('getResponse')
            ->willReturn(
                new TelnetResponse(false, "\nERROR#", ["ERROR#"])
            );
        $this->client->method('execute')
            ->willReturnMap([
                [
                    'enable', null, null,
                    new TelnetResponse(true, "\nPassword: ", ["Password: "]),
                ],
                [
                    'password', null, null,
                    new TelnetResponse(false, "\nCisco#", ["Cisco#"]),
                ],
            ]);


        $response = $this->client->enable("password");
        $this->assertFalse($response->isError());
        $this->assertEquals("Cisco#", $response->getPromptMatches()[0]);
    }

    /** @test */
    public function testConfigure()
    {
        $this->client->method('getResponse')
            ->willReturn(
                new TelnetResponse(false, "\nERROR#", ["ERROR#"])
            );
        $this->client->method('execute')
            ->willReturnMap([
                [
                    'configure terminal', null, null,
                    new TelnetResponse(false, "\nCisco(config)#", ["Cisco(config)#"]),
                ],
                [
                    'int gi0/1', null, null,
                    new TelnetResponse(false, "\nCisco(config-if)#", ["Cisco(config-if)#"]),
                ],
                [
                    'description test', null, null,
                    new TelnetResponse(false, "\nCisco(config-if)#", ["Cisco(config-if)#"]),
                ],
                [
                    'end', null, null,
                    new TelnetResponse(false, "\nCisco#", ["Cisco#"]),
                ],
            ]);

        $response = $this->client->configure([
            "int gi0/1",
            "description test",
        ]);
        $this->assertFalse($response->isError());
        $this->assertEquals("Cisco#", $response->getPromptMatches()[0]);
    }
}