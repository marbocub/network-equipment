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

class ResponseParserTest extends TestCase
{
    /**
     * @dataProvider dataProviderForParser
     */
    public function testParser($command, $input, $correct)
    {
        $parser = new ResponseParser();
        $this->assertEquals($correct, $parser->parse($command, $input));
    }

    public function dataProviderForParser()
    {
        return [
            /* 
             * default parser
             */
            [
                "unknown command",
                "dummy response\n123 abc xyz",
                [
                    ["dummy", "response"],
                    ["123", "abc", "xyz"],
                ]
            ],
        ];
    }

    /** @test */
    public function testParseShowInterfaceStatus()
    {
        $parser = new ResponseParser();
        $command = "show interface status";
        $input = ""
            ."show interface status\n"
            ."\n"
            ."Port      Name               Status       Vlan       Duplex  Speed Type\n"
            ."Te1/0/1   description        connected    trunk        full    10G SFP-10GBase-SR\n"
            ."Gi0/1     looong description err-disabled 2            auto a-1000 10/100/1000BaseTX\n"
            ."Po1                          connected    trunk      a-full a-1000 \n"
            ."\n"
        ;
        $correct = [
            "Te1/0/1" => [
                'Port' => 'Te1/0/1',
                'Name' => 'description',
                'Status' => 'connected',
                'Vlan' => 'trunk',
                'Duplex' => 'full',
                'Speed' => '10G',
                'Type' => 'SFP-10GBase-SR',
            ],
            "Gi0/1" => [
                'Port' => 'Gi0/1',
                'Name' => 'looong description',
                'Status' => 'err-disabled',
                'Vlan' => '2',
                'Duplex' => 'auto',
                'Speed' => 'a-1000',
                'Type' => '10/100/1000BaseTX',
            ],
            "Po1" => [
                'Port' => 'Po1',
                'Name' => '',
                'Status' => 'connected',
                'Vlan' => 'trunk',
                'Duplex' => 'a-full',
                'Speed' => 'a-1000',
                'Type' => '',
            ],
        ];
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = "";
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = null;
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));
    }

    /** @test */
    public function testParseShowInterfaceCounters()
    {
        $parser = new ResponseParser();
        $command = "show interface counters";
        $input = ""
            ."show interface counters\n"
            ."\n"
            ."Port            InOctets    InUcastPkts    InMcastPkts    InBcastPkts\n"
            ."Gi0/1               1024             64             32             16\n"
            ."Po1       12345678901234 11223344556677 12312312312312 67890123456789\n"
            ."\n"
            ."Port           OutOctets   OutUcastPkts   OutMcastPkts   OutBcastPkts\n"
            ."Gi0/1               2048            512            256            128\n"
            ."Po1       43210987654321 77665544332211 21321321321321 98765432109876\n"
            ."\n"
        ;
        $correct = [
            "Gi0/1" => [
                'Port' => 'Gi0/1',
                'InOctets' => '1024',
                'InUcastPkts' => '64',
                'InMcastPkts' => '32',
                'InBcastPkts' => '16',
                'OutOctets' => '2048',
                'OutUcastPkts' => '512',
                'OutMcastPkts' => '256',
                'OutBcastPkts' => '128',
            ],
            "Po1" => [
                'Port' => 'Po1',
                'InOctets' => '12345678901234',
                'InUcastPkts' => '11223344556677',
                'InMcastPkts' => '12312312312312',
                'InBcastPkts' => '67890123456789',
                'OutOctets' => '43210987654321',
                'OutUcastPkts' => '77665544332211',
                'OutMcastPkts' => '21321321321321',
                'OutBcastPkts' => '98765432109876',
            ],
        ];
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = "";
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = null;
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));
    }

    /** @test */
    public function testParseShowMacAddressTable()
    {
        $parser = new ResponseParser();
        $command = "show mac address-table";
        $input = ""
            ."show mac address-table\n"
            ."          Mac Address Table\n"
            ."-------------------------------------------\n"
            ."\n"
            ."Vlan    Mac Address       Type        Ports\n"
            ."----    -----------       --------    -----\n"
            ." All    ffff.ffff.ffff    STATIC      CPU\n"
            ." 100    0102.0304.0506    DYNAMIC     Po1\n"
            ." 100    0102.0304.0507    DYNAMIC     Po1\n"
            ." 200    0102.0304.0506    DYNAMIC     Po1\n"
            ." 200    0102.0304.0508    DYNAMIC     Po1\n"
            ."Total Mac Addresses for this criterion: 1\n"
            ."\n"
        ;
        $correct = [
            "All" => [
                "ffff.ffff.ffff" => [
                    'Vlan' => 'All',
                    'Mac Address' => 'ffff.ffff.ffff',
                    'Type' => 'STATIC',
                    'Ports' => 'CPU'
                ],
            ],
            "100" => [
                "0102.0304.0506" => [
                    'Vlan' => '100',
                    'Mac Address' => '0102.0304.0506',
                    'Type' => 'DYNAMIC',
                    'Ports' => 'Po1'
                ],
                "0102.0304.0507" => [
                    'Vlan' => '100',
                    'Mac Address' => '0102.0304.0507',
                    'Type' => 'DYNAMIC',
                    'Ports' => 'Po1'
                ],
            ],
            "200" => [
                "0102.0304.0506" => [
                    'Vlan' => '200',
                    'Mac Address' => '0102.0304.0506',
                    'Type' => 'DYNAMIC',
                    'Ports' => 'Po1'
                ],
                "0102.0304.0508" => [
                    'Vlan' => '200',
                    'Mac Address' => '0102.0304.0508',
                    'Type' => 'DYNAMIC',
                    'Ports' => 'Po1'
                ],
            ],
        ];
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = "";
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = null;
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));
    }

    /** @test */
    public function testParseShowLldpNeighbors()
    {
        $parser = new ResponseParser();
        $command = "show lldp neighbors";
        $input = ""
            ."show lldp neighbors\n"
            ."Capability codes:\n"
            ."    (R) Router, (B) Bridge, (T) Telephone, (C) DOCSIS Cable Device\n"
            ."    (W) WLAN Access Point, (P) Repeater, (S) Station, (O) Other\n"
            ."\n"
            ."Device ID           Local Intf     Hold-time  Capability      Port ID\n"
            ."ROUTER-01           Gi1/0/1        120        B               Gi2/0/1\n"
            ."AP-01               Gi1/0/2        120        B,W             ffff.ffff.ffff\n"
            ."\n"
            ."Total entries displayed: 2\n"
            ."\n"
        ;
        $correct = [
            "Gi1/0/1" => [
                'Device ID' => 'ROUTER-01',
                'Local Intf' => 'Gi1/0/1',
                'Hold-time' => '120',
                'Capability' => 'B',
                'Port ID' => 'Gi2/0/1'
            ],
            "Gi1/0/2" => [
                'Device ID' => 'AP-01',
                'Local Intf' => 'Gi1/0/2',
                'Hold-time' => '120',
                'Capability' => 'B,W',
                'Port ID' => 'ffff.ffff.ffff'
            ],
        ];
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = "";
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = null;
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));
    }

    /** @test */
    public function testParseShowIpInterfaceBrief()
    {
        $parser = new ResponseParser();
        $command = "show ip interface brief";
        $input = ""
            ."show ip interface brief\n"
            ."Interface              IP-Address      OK? Method Status                Protocol\n"
            ."Vlan1                  10.0.0.1        YES NVRAM  up                    up\n"
            ."Vlan2                  192.168.100.100 YES NVRAM  administratively down down\n"
            ."GigabitEthernet0/1     unassigned      YES unset  up                    up\n"
        ;
        $correct = [
            "Vlan1" => [
                'Interface' => 'Vlan1',
                'IP-Address' => '10.0.0.1',
                'OK' => 'YES',
                'Method' => 'NVRAM',
                'Status' => 'up',
                'Protocol' => 'up',
            ],
            "Vlan2" => [
                'Interface' => 'Vlan2',
                'IP-Address' => '192.168.100.100',
                'OK' => 'YES',
                'Method' => 'NVRAM',
                'Status' => 'administratively down',
                'Protocol' => 'down',
            ],
            "GigabitEthernet0/1" => [
                'Interface' => 'GigabitEthernet0/1',
                'IP-Address' => 'unassigned',
                'OK' => 'YES',
                'Method' => 'unset',
                'Status' => 'up',
                'Protocol' => 'up',
            ],
        ];
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = "";
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = null;
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));
    }

    /** @test */
    public function testParseShowArp()
    {
        $parser = new ResponseParser();
        $command = "show arp";
        $input = ""
            ."show arp\n"
            ."Protocol  Address          Age (min)  Hardware Addr   Type   Interface\n"
            ."Internet  10.0.0.2                0   ffff.ffff.ffff  ARPA   Vlan1\n"
        ;
        $correct = [
            "10.0.0.2" => [
                'Protocol' => 'Internet',
                'Address' => '10.0.0.2',
                'Age' => '0',
                'Hardware Addr' => 'ffff.ffff.ffff',
                'Type' => 'ARPA',
                'Interface' => 'Vlan1',
            ],
        ];
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = "";
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));

        $input = null;
        $correct = null;
        $this->assertEquals($correct, $parser->parse($command, $input, "\n"));
    }
}