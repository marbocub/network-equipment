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

class NoLineEndingPromptMatcherTest extends TestCase
{
    /** @test */
    public function testIsMatch()
    {
        $matcher = new NoLineEndingPromptMatcher();

        $prompt = "\S+[>#]\s?";
        $promptUnix = "\S+[>#%\$]\s?";
        $promptError = "(([uU]ser[nN]ame|[lL]og[iI]n|[pP]ass[wW]ord):\s?|% (Login invalid|Bad passwords|Bad secrets))";

        $input = ""
            ."User Access Verification\n"
            ."\n"
            ."Username: "
        ;
        $this->assertFalse($matcher->isMatch($prompt, $input, "\n"), "Username prompt");
        $this->assertTrue($matcher->isMatch($promptError, $input, "\n"), "Username prompt");

        $input = ""
            ."\n"
            ."\n"
            ."User Access Verification\n"
            ."\n"
            ."Password: "
        ;
        $this->assertFalse($matcher->isMatch($prompt, $input, "\n"), "Password prompt");
        $this->assertTrue($matcher->isMatch($promptError, $input, "\n"), "Password prompt");

        $input = ""
            ."\n"
            ."\n"
            ."User Access Verification\n"
            ."\n"
            ."Password: \n"
            ."Password: \n"
            ."Password: \n"
            ."% Bad passwords\n"
        ;
        $this->assertFalse($matcher->isMatch($prompt, $input, "\n"), "Bad passwords message");
        $this->assertTrue($matcher->isMatch($promptError, $input, "\n"), "Bad passwords message");

        $input = "\nCisco>";
        $this->assertTrue($matcher->isMatch($prompt, $input, "\n"), "Cisco prompt 1");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "Cisco prompt 1");

        $input = "\nCisco#";
        $this->assertTrue($matcher->isMatch($prompt, $input, "\n"), "Cisco prompt 2");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "Cisco prompt 2");

        $input = "\nCisco(config)#";
        $this->assertTrue($matcher->isMatch($prompt, $input, "\n"), "Cisco prompt 3");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "Cisco prompt 3");

        $input = "\nuser@host%";
        $this->assertTrue($matcher->isMatch($promptUnix, $input, "\n"), "JunOS prompt 1");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "JunOS prompt 1");

        $input = "\nuser@host>";
        $this->assertTrue($matcher->isMatch($promptUnix, $input, "\n"), "JunOS prompt 2");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "JunOS prompt 2");

        $input = "\nuser@host#";
        $this->assertTrue($matcher->isMatch($promptUnix, $input, "\n"), "JunOS prompt 3");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "JunOS prompt 3");

        $input = "\nuser@host:~$";
        $this->assertTrue($matcher->isMatch($promptUnix, $input, "\n"), "Unix prompt 1");
        $this->assertFalse($matcher->isMatch($promptError, $input, "\n"), "Unix prompt 1");
    }
}