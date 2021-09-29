<?php

namespace Kantodo\Websocket\Client;

class Websocket
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $address;
    /**
     * @var float
     */
    private $timeout = 10;

    /**
     * @var resource
     */
    private $streamSocket = null;

    public function __construct(string $host, int $port = 80, bool $tsl = false, float $timeout = 10)
    {
        $this->address = ($tsl ? 'tsl://' : '') . $host . ':' . $port;
        $this->host    = $host;
        $this->timeout = $timeout;
    }

    public function connect(string $path = '/'): bool
    {
        $errorCode = null;

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ]);

        /** @phpstan-ignore-next-line */
        $this->streamSocket = stream_socket_client($this->address, $errorCode, $errorMsg, $this->timeout, STREAM_CLIENT_CONNECT, $context);

        if ($errorCode != null) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        $key = base64_encode(openssl_random_pseudo_bytes(16));

        $header = "GET {$path} HTTP/1.1\r\n"
            . "Host: {$this->host}\r\n"
            . "pragma: no-cache\r\n"
            . "Upgrade: WebSocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: $key\r\n"
            . "Sec-WebSocket-Version: 13\r\n";

        // request upgrade
        /** @phpstan-ignore-next-line */
        $ru = fwrite($this->streamSocket, $header);

        if (!$ru) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        $response = fread($this->streamSocket, 1024);

        // success ?
        /** @phpstan-ignore-next-line */
        return stripos($response, ' 101 ') && stripos($response, 'Sec-WebSocket-Accept: ');
    }

    /**
     * @return  void
     */
    public function disconnect()
    {
        if ($this->streamSocket != null) {
            fclose($this->streamSocket);
        }

    }

    /**
     * Pošle zprávu
     *
     * @param   string  $message
     *
     * @return  int|false počet odeslaných bytů
     */
    public function send(string $message)
    {
        $header = '';
        if (strlen($message) < 126) {
            $header .= chr(0x80 | strlen($message));
        } elseif (strlen($message) < 0xFFFF) {
            $header .= chr(0x80 | 126) . pack('n', strlen($message));
        } else {
            $header .= chr(0x80 | 127) . pack('N', 0) . pack('N', strlen($message));
        }

        // Add mask
        $mask = pack('N', rand(1, 0x7FFFFFFF));
        $header .= $mask;

        // Mask application message.
        for ($i = 0; $i < strlen($message); $i++) {
            $message[$i] = chr(ord($message[$i]) ^ ord($mask[$i % 4]));
        }

        return fwrite($this->streamSocket, $header . $message);
    }

    /**
     * Přečte zprávu
     *
     * @return  null|false|string  Vrací zprávu, pokud je zpráva Close, tak vrátí null, pokud se nepodaří zprávu přečíst, tak vrací false
     */
    public function read()
    {
        $data = '';

        do {
            $header = fread($this->streamSocket, 2);

            if (!$header) {
                return false;
            }

            $opcode        = ord($header[0]) & 0x0F;
            $final         = ord($header[0]) & 0x80;
            $masked        = ord($header[1]) & 0x80;
            $payloadLength = ord($header[1]) & 0x7F;

            //extra data
            $extLength = 0;

            if ($payloadLength >= 0x7E) {
                $extLength = 2;

                if ($payloadLength == 0x7F) {
                    $extLength = 8;
                }

                $header = fread($this->streamSocket, $extLength);

                if (!$header) {
                    return false;
                }

                $payloadLength = 0;
                for ($i = 0; $i < $extLength; $i++) {
                    $payloadLength += ord($header[$i]) << ($extLength - $i - 1) * 8;
                }

            }

            if ($masked) {
                $mask = fread($this->streamSocket, 4);
                if (!$mask) {
                    return false;
                }

            } else {
                $mask = 0;
            }

            $frameData = '';
            while ($payloadLength > 0) {

                $frame = fread($this->streamSocket, $payloadLength);

                if (!$frame) {
                    return false;
                }

                $payloadLength -= strlen($frame);
                $frameData .= $frame;
            }

            // ping
            if ($opcode == 9) {
                //pong
                fwrite($this->streamSocket, chr(0x8A) . chr(0x80) . pack('N', rand(1, 0x7FFFFFFF)));
                continue;
            }

            //close
            if ($opcode == 8) {
                $this->disconnect();
                return null;
            }

            // Unmask data
            $dataLength = strlen($frameData);
            if ($masked) {
                for ($i = 0; $i < $dataLength; $i++) {
                    $data .= $frameData[$i] ^ $mask[$i % 4];
                }
            } else {
                $data .= $frameData;
            }

        } while (!$final);

        return $data;
    }
}
