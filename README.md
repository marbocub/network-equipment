# marbocub/network-equipment

A Telnet client for network equipment.

This package extends [graze/telnet-client](https://github.com/graze/telnet-client) to provide several useful methods and the response parsers for remote watching/controlling networking equipment.

## Targetted network equipment

The targetted network equipment of this package are as follows:

* Cisco IOS
* Cisco IOS-XE

This package may work with the following network equipment:

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
        $telnet->connect("127.0.0.1:23"); // change your target ip address
        $telnet->login("username", "password");

        $telnet->execute("terminal length 0");
        $response = $telnet->execute("show interface status");
        echo $response;

    } catch (TelnetException $e) {
        echo $e->getMessage();
        die();
    }

## Usage

### Create a instance

    require_once("vendor/autoload.php");    // if you needed

    use Marbocub\NetworkEquipment\TelnetClient;
    use Graze\TelnetClient\Exception\TelnetException;

    $telnet = TelnetClient::factory();

### Connect and Login the network equipment

    $telnet->connect("127.0.0.1:23");
    try {
        $telnet->login("username", "password");
    } catch (TelnetException $e) {
        /* error */
    }
    /* success */

### Execute commands and parse the response

    $telnet->execute("terminal length 0");
    $response = $telnet->execute("show interface status");

    // Getting the response text
    echo $response;
    // or
    echo $response->getResponseText();

    // Getting the parsed array
    print_r($response->getResponseArray());

### Turn on privileged mode for Cisco IOS

    try {
        $telnet->enable("password");
    } catch (TelnetException $e) {
        /* error */
    }
    /* success */

### Batch execute Cisco IOS configure commands (privileged mode must be turned on)

    $commands = [
        'interface Gi1/0/1',
        'switchport access vlan 100',
    ];
    try {
        $telnet->configure($commands);
    } catch (TelnetException $e) {
        /* error */
    }
    /* done */

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

### Parser example

    $result = $telnet->execute('show int status');
    print_r($result->getResponseArray());

result:

    Array
    (
        [Gi1/0/1] => Array
            (
                [Port] => Gi1/0/1
                [Name] => example
                [Status] => connected
                [Vlan] => 100
                [Duplex] => a-full
                [Speed] => a-1000
                [Type] => 10/100/1000BaseTX
            )

        [Gi1/0/2] => Array
            (
                ...
            )

        ...
    )

## Limitations

This package currently implemented only the Telnet connection.
This package requires the PHP's low-level sockets extension (ext-sockets) for the underlying library.

## Authors

* marbocub - Initial work

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
