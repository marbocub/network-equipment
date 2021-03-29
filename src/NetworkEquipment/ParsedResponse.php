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

use Graze\TelnetClient\TelnetResponse;

class ParsedResponse extends TelnetResponse
{
    protected $command;
    protected $parser;

    /**
     * Constructor
     * 
     * @param string $command
     * @param TelnetResponse $response
     */
    public function __construct($command, $response)
    {
        $this->command = $command;

        $this->isError = $response->isError;
        $this->responseText = str_replace(["\r\n", "\r", "\n"], "\n", $response->responseText);
        $this->promptMatches = $response->promptMatches;

        $this->parser = new ResponseParser();
    }

    /**
     * to string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->responseText;
    }

    /**
     * return the array formated response
     * 
     * @return Array
     */
    public function getResponseArray()
    {
        return $this->buildResponseArray();
    }

    /**
     * Build the array formatted from response
     * 
     * @return Array
     */
    protected function buildResponseArray()
    {
        return $this->parser->parse($this->command, $this->responseText);
    }
}