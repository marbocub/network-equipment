# marbocub/network-equipment

A Telnet client for network equipment.

This library provide two features are useful for implementing web applications for remotely watching/controlling networking equipment.

* Extends [graze/telnet-client](https://github.com/graze/telnet-client) to provide waitForPrompt(), login(), enable(), configure() and several useful methods
* The response parser and the parser integrated Telnet client

The parser is implemented as the standalone library and as the integrated Telnet client. If you want to use a different Telnet client library, you can use the parser as standalone library.

## Targetted network equipment

The targetted network equipment of this library are as follows:

* Cisco IOS
* Cisco IOS-XE

Methods of Telnet client waitForPrompt() and login() may work with the following network equipment:

* Juniper JUNOS (with custom prompt settings)
* Linux and other UNIX like platforms (with custom prompt settings)
* Alaxala network switch
* Extreme XOS, HP Procurve, DLink and other intelligent network switch

## Getting Started

### Installing

Via Composer

    composer require marbocub/network-equipment

### Quick start example

Once installed, you can use the following example to get and display interface status of Cisco IOS network switch.

    <?php
    require_once("vendor/autoload.php");

    use Marbocub\NetworkEquipment\TelnetClient;
    use Graze\TelnetClient\Exception\TelnetException;

    $telnet = TelnetClient::factory();
    try {
        $telnet->connect("127.0.0.1:23"); // please changes to your target ip address
        $telnet->login("username", "password");
        $telnet->execute("terminal length 0");

        $response = $telnet->execute("show interface status");
        echo $response;
        print_r($response->getResponseArray());

    } catch (TelnetException $e) {
        echo $e->getMessage();
        die();
    }

Example of the result:

    Port      Name               Status       Vlan       Duplex  Speed Type
    Te1/0/1   description        connected    trunk        full    10G SFP-10GBase-SR
    Po1                          connected    trunk      a-full a-1000 

    Array
    (
        [Te1/0/1] => Array
            (
                [Port] => 'Te1/0/1',
                [Name] => 'description',
                [Status] => 'connected',
                [Vlan] => 'trunk',
                [Duplex] => 'full',
                [Speed] => '10G',
                [Type] => 'SFP-10GBase-SR',
            )

        [Po1] => Array
            (
                [Port] => 'Po1',
                [Name] => '',
                [Status] => 'connected',
                [Vlan] => 'trunk',
                [Duplex] => 'a-full',
                [Speed] => 'a-1000',
                [Type] => '',
            )
    )


## Usage for Telnet Client

### Create a instance

    require_once("vendor/autoload.php");

    use Marbocub\NetworkEquipment\TelnetClient;
    use Graze\TelnetClient\Exception\TelnetException;

    $telnet = TelnetClient::factory();

### Connect and Login the network equipment

    $telnet->connect("127.0.0.1:23");

    try {
        $telnet->login("username", "password");
    } catch (TelnetException $e) {
        /* failed */
    }

### Execute commands and parse the response

    $telnet->execute("terminal length 0");

    $response = $telnet->execute("show interface status");

    // Getting the response text
    echo $response;

    // Getting the parsed array
    print_r($response->getResponseArray());

### Turn on privileged mode for Cisco IOS

    try {
        $telnet->enable("password");
    } catch (TelnetException $e) {
        /* failed */
    }

### Batch execute Cisco IOS configure commands (privileged mode must be turned on)

    $commands = [
        'interface Gi1/0/1',
        'switchport access vlan 100',
    ];

    try {
        $telnet->configure($commands);
    } catch (TelnetException $e) {
        /* failed */
    }

### Custom prompt setting

For Juniper JUNOS and Linux or other UNIX platforms, you can use the following regex prompt.

    $telnet->setPrompt("\S+[>#%\$]\s?");

## Parser

The parser is implemented the ability to convert the following Cisco IOS (and compatible) command responses to the array.

* show interface status
* show interface counters
* show mac address-table
* show lldp neighbors
* show ip interface brief
* show arp

## Usage for standalone parser library

### Quick start example

    <?php
    require_once("vendor/autoload.php");

    use Marbocub\NetworkEquipment\ResponseParser;

    /* a command executed and the response returned by your Telnet client */
    $response = $yourTelnetClient->execute("show int status");
    echo $response;

    /* start parse */
    $parser = new ResponseParser();
    $result = $parser->parse($command, $response, "\n");

    print_r($result);

Example of the result:

    Port      Name               Status       Vlan       Duplex  Speed Type
    Te1/0/1   description        connected    trunk        full    10G SFP-10GBase-SR
    Po1                          connected    trunk      a-full a-1000 

    Array
    (
        [Te1/0/1] => Array
            (
                [Port] => 'Te1/0/1',
                [Name] => 'description',
                [Status] => 'connected',
                [Vlan] => 'trunk',
                [Duplex] => 'full',
                [Speed] => '10G',
                [Type] => 'SFP-10GBase-SR',
            )

        [Po1] => Array
            (
                [Port] => 'Po1',
                [Name] => '',
                [Status] => 'connected',
                [Vlan] => 'trunk',
                [Duplex] => 'a-full',
                [Speed] => 'a-1000',
                [Type] => '',
            )
    )

## Limitations

Telnet Client of this library requires the PHP's low-level sockets extension (ext-sockets) for the underlying library.

## Authors

* marbocub - Initial work

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
