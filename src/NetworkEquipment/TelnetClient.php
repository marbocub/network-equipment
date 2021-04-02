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

use Graze\TelnetClient\TelnetClient as ParentTelnetClient;
use Socket\Raw\Factory as SocketFactory;
use Graze\TelnetClient\InterpretAsCommand;
use Graze\TelnetClient\Exception\TelnetException;

class TelnetClient extends ParentTelnetClient
{
    const PROMPT_CISCO = "((?<username>\S+?)\@)?(?<hostname>\S+?)(\((?<mode>.+)\))?[>#]\s?";
    const PROMPT_JUNOS = "((?<username>\S+?)\@)?(?<hostname>\S+?)[%>#]\s?";
    const PROMPT_SHELL = "((?<username>\S+?)\@)?(?<hostname>\S+?)(:(?<path>.+))?[#\$]\s?";
    const PROMPT_ERROR = "(([uU]ser[nN]ame|[lL]og[iI]n|[pP]ass[wW]ord):\s?|% (Login invalid|Bad passwords|Bad secrets))";

    /**
     * Connect the network equipment
     * 
     * Default preset parameters are for Cisco
     * 
     * @param $dsn
     * @param $prompt
     * @param $promptError
     * @param $lineEnding
     * @param $timeout
     */
    public function connect(
        $dsn = '127.0.0.1:23',
        $prompt = self::PROMPT_CISCO,
        $promptError = self::PROMPT_ERROR,
        $lineEnding = "\r\n",
        $timeout = 10)
    {
        parent::connect($dsn, $prompt, $promptError, $lineEnding, $timeout);
    }

    /**
     * Wait for recieving prompt
     * 
     * @return ParsedResponse
     */
    public function waitForPrompt()
    {
        if (!$this->socket) {
            throw new TelnetException('attempt to execute without a connection - call connect first');
        }

        return new ParsedResponse(
            null,
            $this->getResponse()
        );
    }

    /**
     * Login the network equipment
     * 
     * @param string $username
     * @param string $password
     * 
     * @return ParsedResponse
     */
    public function login($username, $password)
    {
        $response = $this->waitForPrompt();

        for ($i=0; $i<3; $i++) {
            if (!$response->isError()) {
                break;
            }

            $matched = $response->getPromptMatches()[0] ?: null;

            if (preg_match("/([lL]og[iI]n|[uU]ser[nN]ame):\s?/", $matched)) {
                $response = $this->execute($username);

            } else if (preg_match("/[pP]ass[wW]ord:\s?/", $matched)) {
                $response = $this->execute($password);

            } else if (preg_match("/% (Login invalid|Bad passwords)/", $matched)) {
                throw new TelnetException('Login Error');
            }
        }

        return $response;
    }

    /**
     * Turn on privileged mode
     * 
     * @param string $username
     * @param string $password
     * 
     * @return ParsedResponse
     */
    public function enable($password)
    {
        $response = $this->execute('enable');

        for ($i=0; $i<3; $i++) {
            if (!$response->isError()) {
                break;
            }

            $matched = $response->getPromptMatches()[0] ?: null;

            if (preg_match("/[pP]ass[wW]ord:\s?/", $matched)) {
                $response = $this->execute($password);

            } else if (preg_match("/% (Bad secrets)/", $matched)) {
                throw new TelnetException('Cannot turn on privileged mode');
            }
        }

        return $response;
    }

    /**
     * Batch execute configure commands over the turn on the configure mode
     * 
     * @param array $commands
     * 
     * @return ParsedResponse
     */
    public function configure($commands)
    {
        if (!is_array($commands)) {
            $commands = [$commands];
        }

        $response = $this->execute("configure terminal");
        if (!preg_match("/.*\([Cc]onfig\)#\s?/", $response->getPromptMatches()[0])) {
            throw new TelnetException('Cannot trun on configure mode');
        }

        foreach ($commands as $command) {
            $response = $this->execute($command);
        }
        $response = $this->execute("end");

        return $response;
    }

    /**
     * Execute a command and build parser
     * 
     * @param string $command
     * @param string $prompt
     * @param string $promptError
     *
     * @return ParsedResponse
     */
    public function execute($command, $prompt = null, $promptError = null)
    {
        return new ParsedResponse(
            $command,
            parent::execute($command, $prompt, $promptError)
        );
    }

    /**
     * Create instance
     *
     * @return TelnetClientInterface
     */
    public static function factory()
    {
        return new static(
            new SocketFactory(),
            new NoLineEndingPromptMatcher(),
            new InterpretAsCommand()
        );
    }
}