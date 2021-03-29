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

namespace Marbocub\NetworkEquipment\Parsers;

trait Cisco
{
    /**
     * Parse the output of 'show interface status'
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function parseShowInterfaceStatus($input)
    {
        $pattern = "/^(\S+)\s+(.*?)\s+(connected|notconnect|suspended|inactive|disabled|err-disabled|monitoring)\s+(\S+)\s+(\S+)\s+(\S+)\s?(.*)?$/";
        $columns = [
            'Port',
            'Name',
            'Status',
            'Vlan',
            'Duplex',
            'Speed',
            'Type',
        ];
        $keyColumn = 'Port';

        $extracted = $this->extractResultRegex($input, null, "/^Port .*$/", "/^$/");
        return $this->parseRegexFormat($extracted, $pattern, $columns, $keyColumn, null);
        /*
        $format = [
            'Port' => [0, 10],
            'Name' => [10, 19],
            'Status' => [29, 13],
            'Vlan' => [42, 5],
            'Duplex' => [48, 11],
            'Speed' => [59, 7],
            'Type' => [67],
        ];
        $keyColumn = 'Port';

        $extracted = $this->extractResultRegex($input, null, "/^Port .*$/", "/^$/");
        return $this->parseFixedFormat($extracted, $format, $keyColumn, null);
        */
    }

    /**
     * Parse the output of 'show interface counters'
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function parseShowInterfaceCounters($input)
    {
        $inStart = "/^Port +InOctets +InUcastPkts +InMcastPkts +InBcastPkts.*$/";
        $outStart = "/^Port +OutOctets +OutUcastPkts +OutMcastPkts +OutBcastPkts.*$/";

        $in = $this->extractResultRegex($input, null, $inStart, "/^$/");
        $out = $this->extractResultRegex($input, null, $outStart, "/^$/");

        $pattern = "/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)$/";
        $inColumns = [
            'Port',
            'InOctets',
            'InUcastPkts',
            'InMcastPkts',
            'InBcastPkts',
        ];
        $outColumns = [
            'Port',
            'OutOctets',
            'OutUcastPkts',
            'OutMcastPkts',
            'OutBcastPkts',
        ];
        $keyColumn = 'Port';

        $inParsed = $this->parseRegexFormat($in, $pattern, $inColumns, $keyColumn, null);
        $outParsed = $this->parseRegexFormat($out, $pattern, $outColumns, $keyColumn, null);
        if (!is_array($outParsed)) {
            $outParsed = [];
        }
        array_walk($outParsed, function(&$port, $key) {
            unset($port['Port']);
        });
        return is_array($inParsed) ? array_merge_recursive($inParsed, $outParsed) : null;
    }

    /**
     * Parse the output of 'show mac address-table'
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function parseShowMacAddressTable($input)
    {
        $format = [
            'Vlan' => [0, 8],
            'Mac Address' => [8, 18],
            'Type' => [26, 12],
            'Ports' => [38, null],
        ];
        $keyColumns = ['Vlan', 'Mac Address'];

        $extracted = $this->extractResultRegex($input, null, "/^----    ----.*$/", "/^Total Mac Addresses .*$/");
        return $this->parseFixedFormat($extracted, $format, $keyColumns, null);
    }

    /**
     * Parse the output of 'show lldp neighbors'
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function parseShowLldpNeighbors($input)
    {
        $format = [
            'Device ID' => [0, 20],
            'Local Intf' => [20, 15],
            'Hold-time' => [35, 11],
            'Capability' => [46, 16],
            'Port ID' => [62, null],
        ];
        $keyColumn = 'Local Intf';

        $extracted = $this->extractResultRegex($input, null, "/^Device ID .*$/", "/^$/");
        return $this->parseFixedFormat($extracted, $format, $keyColumn, null);
    }

    /**
     * Parse the output of 'show ip interface brief'
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function parseShowIpInterfaceBrief($input)
    {
        $format = [
            'Interface' => [0, 23],
            'IP-Address' => [23, 15],
            'OK' => [39, 4],
            'Method' => [43, 7],
            'Status' => [50, 22],
            'Protocol' => [72, null],
        ];
        $keyColumn = 'Interface';

        $extracted = $this->extractResultRegex($input, null, "/^Interface .*$/", "/^$/");
        return $this->parseFixedFormat($extracted, $format, $keyColumn, null);
    }

    /**
     * Parse the output of 'show arp'
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function parseShowArp($input)
    {
        $format = [
            'Protocol' => [0, 10],
            'Address' => [10, 15],
            'Age' => [25, 13],
            'Hardware Addr' => [38, 16],
            'Type' => [54, 7],
            'Interface' => [61, null],
        ];
        $keyColumn = 'Address';

        $extracted = $this->extractResultRegex($input, null, "/^Protocol .*$/", "/^$/");
        return $this->parseFixedFormat($extracted, $format, $keyColumn, null);
    }

    /**
     * Split lines by line ending
     * 
     * @param string $input
     * 
     * @return Array
     */
    protected function splitlines($input)
    {
        return explode("\n", str_replace(["\r\n", "\r", "\n"], "\n", $input));
    }

    /**
     * Extract result text by regex start/end pattern
     * 
     * @param string $input
     * @param string $startPattern
     * @param string $nextPattern
     * @param string $endPattern
     * 
     * @return string
     */
    protected function extractResultRegex($input, $startPattern, $nextPattern, $endPattern)
    {
        $flag = false;
        $next = false;
        foreach ($this->splitlines($input) as $line) {
            if (!empty($startPattern) && preg_match($startPattern, $line)) {
                $flag = true;
            }
            if (!empty($nextPattern) && preg_match($nextPattern, $line)) {
                $next = true;
                $flag = false;
            }
            if (!empty($endPattern) && preg_match($endPattern, $line)) {
                $flag = false;
            }
            if ($flag) {
                $extracted[] = $line;
            }
            if ($next) {
                $next = false;
                $flag = true;
            }
        }

        return isset($extracted) ? implode("\n", $extracted) : null;
    }

    /**
     * Parse fixed format
     * 
     * @param string $input
     * @param array $format
     * @param string $keyName
     * @param callable $isValidFunc
     * 
     * @return array
     */
    protected function parseFixedFormat(
        $input, $format, $keyNames,
        callable $isValidFunc = null)
    {
        $matcher = function($line) use ($format) {
            foreach ($format as $name=>$subformat) {
                @list($offset, $length) = $subformat;
                $result[$name] = trim(
                    !empty($length) ? substr($line, $offset, $length) : substr($line, $offset)
                );
            }
            return $result;
        };

        return $this->parseWithUserMatcher($input, $matcher, $keyNames, $isValidFunc);
    }

    /**
     * Parse regex format
     * 
     * @param string $input
     * @param string $pattern
     * @param array $keys,
     * @param string $keyName
     * @param callable $isValidFunc
     * 
     * @return array
     */
    protected function parseRegexFormat(
        $input, $pattern, $columns, $keyColumns,
        callable $isValidFunc = null)
    {
        $matcher = function($line) use ($pattern, $columns) {
            if (preg_match($pattern, trim($line), $matches)) {
                unset($matches[0]);
                return array_combine($columns, $matches);
            }
            return null;
        };

        return $this->parseWithUserMatcher($input, $matcher, $keyColumns, $isValidFunc);
    }

    /**
     * Parse with user matcher
     * 
     * @param string $input
     * @param callable $matcher
     * @param string $keyColumns
     * @param callable $isValidFunc
     * 
     * @return array
     */
    protected function parseWithUserMatcher(
        $input,
        callable $matcher,
        $keyColumns = null,
        callable $isValidFunc = null)
    {
        foreach ($this->splitlines($input) as $line) {
            if (strlen($line) === 0) {
                continue;
            }

            $result = $matcher($line);
            if (is_null($result)) {
                continue;
            }

            $keyValues = null;
            foreach((array)$keyColumns as $key) {
                $keyValues[] = @$result[$key];
            }

            if (!is_callable($isValidFunc) || $isValidFunc($line, is_array($keyColumns) || is_null($keyColumns) ? $keyValues : $keyValues[0])) {
                $p = &$results;
                foreach((array)$keyValues as $key) {
                    if (is_null($key)) {
                        $p = &$p[];
                    } else {
                        $p = &$p[$key];
                    }
                }
                if (isset($p)) {
                    for ($i=1; ; $i++) {
                        $p = &$duplicates[$i];
                        foreach((array)$keyValues as $key) {
                            if (is_null($key)) {
                                $p = &$p[];
                            } else {
                                $p = &$p[$key];
                            }
                        }
                        if (!isset($p)) {
                            break;
                        }
                    }
                }
                $p = $result;
            }
        }

        if (isset($duplicates)) {
            $duplicates[0] = $results;
            return $duplicates;
        }

        return isset($results) ? $results : null;
    }
}