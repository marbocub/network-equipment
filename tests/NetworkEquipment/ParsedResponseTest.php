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

class ParsedResponseTest extends TestCase
{
    /** @test */
    public function testParsedResponse()
    {
        $response = new ParsedResponse(
            'dummy command',
            new TelnetResponse(
                false,
                "dummy response\nabc xyz 123",
                ['Cisco> ']
            )
        );

        $this->assertEquals(
            "dummy response\nabc xyz 123",
            (string)$response
        );
        $this->assertEquals(
            [
                ["dummy", "response"],
                ["abc", "xyz", "123"],
            ],
            $response->getResponseArray()
        );
    }
}