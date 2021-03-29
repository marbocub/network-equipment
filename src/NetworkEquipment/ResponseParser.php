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

class ResponseParser
{
    use Parsers\Cisco;

    public function parse($command, $responseText)
    {
        /*
         * Cisco: show interface status
         */
        if (preg_match("/(s|sh(o|ow)?)\s+int(e(r(f(a(c(e)?)?)?)?)?)?\s+status?.*/", $command)) {
            return $this->parseShowInterfaceStatus($responseText);
        }

        /*
         * Cisco: show interface counters
         */
        if (preg_match("/(s|sh(o|ow)?)\s+int(e(r(f(a(c(e)?)?)?)?)?)?\s+co(u(n(t(e(r(s)?)?)?)?)?)?.*/", $command)) {
            return $this->parseShowInterfaceCounters($responseText);
        }

        /*
         * Cisco: show mac address-table
         */
        if (preg_match("/(s|sh(o|ow)?)\s+mac\s+ad(d(r(e(s(s(-(t(a(b(l(e)?)?)?)?)?)?)?)?)?)?)?.*/", $command)) {
            return $this->parseShowMacAddressTable($responseText);
        }

        /*
         * Cisco: show lldp neighbors
         */
        if (preg_match("/(s|sh(o|ow)?)\s+ll(d(p)?)?\s+n(e(i(g(h(b(o(r(s)?)?)?)?)?)?)?)?.*/", $command)) {
            return $this->parseShowLldpNeighbors($responseText);
        }

        /*
         * Cisco: show ip interface brief
         */
        if (preg_match("/(s|sh(o|ow)?)\s+ip\s+in(t(e(r(f(a(c(e)?)?)?)?)?)?)?\s+br(r(i(e(f)?)?)?)?.*/", $command)) {
            return $this->parseShowIpInterfaceBrief($responseText);
        }

        /*
         * Cisco: show arp
         */
        if (preg_match("/(s|sh(o|ow)?)\s+arp?.*/", $command)) {
            return $this->parseShowArp($responseText);
        }

        /*
         * default
         */
        $responseText = str_replace(["\r\n", "\r", "\n"], "\n", $responseText);
        foreach (explode("\n", $responseText) as $line) {
            $results[] = preg_split("/\s+/", $line);
        }
        return $results;
    }
}