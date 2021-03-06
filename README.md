# marbocub/network-equipment

A telnet client with response parser for network equipment written in PHP.

This library provide two features.

* Telnet client, that extends [graze/telnet-client](https://github.com/graze/telnet-client)
to provide waitForPrompt(), login(), enable(), configure()
and several presets for network equipment
* The parser, that converts a supported Cisco IOS command response to the array

The parser is implemented
as the integrated with telnet client and as the standalone library.
If you want to use your own telnet/SSH client library,
you can use the parser as standalone library.

## Supported network equipment

Supported network equipment by the parser and the telnet client are:

* Cisco IOS
* Cisco IOS-XE

Telnet client may work with targeted are:

* Juniper JUNOS (with [custom prompt setting](#custom-prompt-setting))
* Linux and other UNIX like platforms (with [custom prompt setting](#custom-prompt-setting))
* Alaxala network switch
* Extreme XOS, HP Procurve, DLink and other intelligent network switch

## Supported Cisco IOS commands by the parser

* show interface status
* show interface counters
* show mac address-table
* show lldp neighbors
* show ip interface brief
* show arp

## Getting Started

### Installing

Via Composer

```bash
composer require marbocub/network-equipment
```

### Quick start example for telnet client

The following example login to a Cisco IOS switch
to get and display the interface status:

```php
<?php
require_once("vendor/autoload.php");

use Marbocub\NetworkEquipment\TelnetClient;
use Graze\TelnetClient\Exception\TelnetException;

$telnet = TelnetClient::factory();
try {
    $telnet->connect("127.0.0.1:23");
    $telnet->login("username", "password");
    $telnet->execute("terminal length 0");

    $response = $telnet->execute("show interface status");

    echo $response;
    print_r($response->getResponseArray());

} catch (TelnetException $e) {
    echo $e->getMessage();
    die();
}
```

### Quick start example for standalone parser library

The following example parse the "show int status" command response
getted from a Cisco IOS switch:

```php
<?php
require_once("vendor/autoload.php");

use Marbocub\NetworkEquipment\ResponseParser;

/* executed command and the response */
$command = "show int status";
$response = $yourSSHClient->execute($command);

echo $response;

/* start parse */
$parser = new ResponseParser();
$result = $parser->parse($command, $response);

print_r($result);
```

### The result of examples

The result of both examples is:

```text
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
```

## Usage for Telnet Client

### Instantiating a client

To instantiation a client,
use the TelnetClient::factory() that return a TelnetClient instance:

```php
require_once("vendor/autoload.php");

use Marbocub\NetworkEquipment\TelnetClient;
use Graze\TelnetClient\Exception\TelnetException;

$telnet = TelnetClient::factory();
```

### Connect and Login to the network equipment

To login a network equipment,
use the connect() method to create a socket first and then use the login() method:

```php
try {
    $telnet->connect("127.0.0.1:23");
    $telnet->login("username", "password");
} catch (TelnetException $e) {
    /* failed */
}
```

**Note:**
The difference of connect() between this library and
the [graze/telnet-client](https://github.com/graze/telnet-client)
is the preset prompts for Cisco IOS.

### Execute command and parse the response

Once connected,
the execute() method can be used to write command to the socket and receive the response:

```php
try {
    $telnet->execute("terminal length 0");

    $response = $telnet->execute("show interface status");

    // Getting the response text
    echo $response;

    // Getting the parsed array
    print_r($response->getResponseArray());

} catch (TelnetException $e) {
    /* failed */
}
```

**Note:**
Must be execute "terminal length 0" first for Cisco IOS.

**Note:**
getResponseArray() calls the parser internally.
Only works well with supported Cisco IOS command executed.

### Turn on privileged mode for Cisco IOS

To enable privileged mode for Cisco IOS, the enable() method can be used:

```php
try {
    $telnet->enable("password");
} catch (TelnetException $e) {
    /* failed */
}
```

### Batch execute Cisco IOS configure commands (privileged mode must be turned on)

To configure Cisco IOS, the configure() method can be used:

```php
$commands = [
    'interface Gi1/0/1',
    'switchport access vlan 100',
];

try {
    $telnet->configure($commands);
} catch (TelnetException $e) {
    /* failed */
}
```

**Note:**
The first argument of the configure() is an array listing the commands to execute.

### Custom prompt setting

You can specify the following prompt constant at the 2nd argument of connect().

* For Juniper JUNOS

```php
$telnet->connect("127.0.0.1:23', TelnetClient::PROMPT_JUNOS);
```

* For Linux or other UNIX platforms

```php
$telnet->connect("127.0.0.1:23', TelnetClient::PROMPT_SHELL);
```

## Usage for Parser

### Instantiating a parser

```php
require_once("vendor/autoload.php");

use Marbocub\NetworkEquipment\ResponseParser;

$parser = new ResponseParser();
```

### Parse the command response

The first argument of parse() is the executed Cisco IOS command.
Required for the parser to select the format.

```php
$result = $parser->parse(
                "executed command",
                "response text"
            );
```

## Limitations

Telnet client of this library requires the PHP's low-level sockets extension (ext-sockets)
for the underlying library.

## Authors

* marbocub - Initial work

## License

This project is licensed under the MIT License -
see the [LICENSE](LICENSE) file for details.
