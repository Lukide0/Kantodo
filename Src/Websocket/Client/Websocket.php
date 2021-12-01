<?php

declare(strict_types = 1);

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

        $contextOpts = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $context = stream_context_create($contextOpts);

        /** @phpstan-ignore-next-line */
        $this->streamSocket = @stream_socket_client($this->address, $errorCode, $errorMsg, $this->timeout, STREAM_CLIENT_CONNECT, $context);

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

        if ($ru === false) {
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
    public function disconnect(int $ms = 0)
    {
        if ($ms > 0)
            stream_set_timeout($this->streamSocket, 0, $ms);
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
    public function send(string $message, int $type = 0x81)
    {
        // https://stackoverflow.com/a/16608429/14456367
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($message);
        
        
        switch ($type) {
            case 0x81:
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 0x88:
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 0x89:
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 0x8A:
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
            default:
                return false;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = 255;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }

            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = 254;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = $payloadLength + 128;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }

        
        // generate a random mask:
        $mask = array();
        for ($i = 0; $i < 4; $i++) {
            $mask[$i] = chr(rand(0, 255));
        }

        $frameHead = array_merge($frameHead, $mask);
        $frame = implode('', $frameHead);
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= $message[$i] ^ $mask[$i % 4];
        }

        return fwrite($this->streamSocket, $frame . $message);
    }

    public function timeout(int $ms = 0)
    {
        if ($ms > 0)
            stream_set_timeout($this->streamSocket, 0, $ms);
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

            if ($header === false) {
                return false;
            }

            if (strlen($header) == 0)
                return false;

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

                if ($header === false) {
                    return false;
                }

                $payloadLength = 0;
                for ($i = 0; $i < $extLength; $i++) {
                    $payloadLength += ord($header[$i]) << ($extLength - $i - 1) * 8;
                }

            }

            if ($masked == false) {
                $mask = fread($this->streamSocket, 4);
                if ($mask == false) {
                    return false;
                }
                $mask = (int)$mask;
            } else {
                $mask = 0;
            }

            $frameData = '';
            while ($payloadLength > 0) {

                $frame = fread($this->streamSocket, $payloadLength);

                if ($frame === false) {
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
            /** @phpstan-ignore-next-line */
            if ($masked) {
                for ($i = 0; $i < $dataLength; $i++) {
                    /** @phpstan-ignore-next-line */
                    $data .= $frameData[$i] ^ $mask[$i % 4];
                }
            } else {
                $data .= $frameData;
            }
        
        /** @phpstan-ignore-next-line */
        } while (!$final);

        return $data;
    }
}
