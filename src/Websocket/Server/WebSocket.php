<?php

namespace Kantodo\Websocket\Server;

use Socket;

define('WS_MSG_TEXT', 0x81);
define('WS_MSG_BINARY', 0x82);
define('WS_MSG_CLOSE', 0x88);
define('WS_MSG_PING', 0x89);
define('WS_MSG_PONG', 0x8A);

class WebSocket
{
    public $onMessage    = null;
    public $onConnect    = null;
    public $onDisconnect = null;
    public $onHandshake  = null;

    /**
     * Websocket secret key
     *
     * @var string
     */
    private $secret = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    private $uri;
    private $path;
    private $address;
    private $port;

    /**
     * Server socket
     */
    private $master;

    /**
     * Sockets
     *
     * @var array
     */
    private $sockets = [];

    /**
     * Clients
     *
     * @var Client[]
     */
    private $clients = array();

    public function __construct(string $address, int $port, string $path = '/')
    {
        //max execution time
        set_time_limit(0);

        $this->uri     = $address . ':' . $port;
        $this->address = $address;
        $this->port    = $port;
        $this->path    = $path;

        $parts = parse_url($this->uri);

        if (!$parts || !isset($parts['scheme'], $parts['host'], $parts['port'])) {
            throw new \InvalidArgumentException('Invalid URI');
            exit;
        }

    }

    public function run()
    {
        $context = stream_context_create();

        $this->master = stream_socket_server(
            $this->uri,
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );

        if ($this->uri === false) {
            throw new \RuntimeException('Failed to listen on "' . $this->uri . '": ' . $errstr, $errno);
        }

        stream_set_blocking($this->master, false);

        $this->sockets[(int) $this->master] = $this->master;

        $write  = [];
        $except = [];

        Console::log("Listening on uri: {$this->uri}");

        while (true) {
            $changed = $this->sockets;

            if (@stream_select($changed, $write, $except, null) === false) {
                continue;
            }

            foreach ($changed as $socket) {

                // new client
                if ($socket === $this->master) {
                    $newSocket = stream_socket_accept($this->master);

                    if ($newSocket === false) {
                        continue;
                    }

                    $this->connect($newSocket);
                    unset($newSocket);
                    continue;
                }

                //message
                $buffer = stream_get_contents($socket);

                // message is empty
                if ($buffer === false || empty($buffer)) {
                    $this->disconnect($socket, 'MESSAGE');
                    continue;
                }

                $client = $this->clients[(int) $socket];

                if ($client->handshake == false) {
                    $client->handshake = true;
                    $this->handshake($socket, $buffer);

                    unset($buffer);
                    unset($client);
                    continue;
                }

                $data = $this->decodeData($socket, $buffer);

                if (!empty($data)) {
                    switch ($data['type']) {
                        case WS_MSG_TEXT:
                            if ($this->onMessage !== null) {
                                call_user_func($this->onMessage, $data, $socket);
                            }

                            break;
                        case WS_MSG_PING:
                            $this->sendToSocket($socket, '', WS_MSG_PONG);
                            break;
                        case WS_MSG_PONG:
                            break;
                        case WS_MSG_CLOSE:
                            $this->disconnect($socket);
                            break;
                        default:
                            break;
                    }

                }
                unset($client);
                unset($data);

            }
        }
    }

    public function sendToSocket(&$socket, string $message, $type = WS_MSG_TEXT)
    {
        $message = $this->encodeData($message, $type);
        stream_socket_sendto($socket, $message);
    }

    private function connect($socket)
    {

        $this->sockets[(int) $socket] = $socket;

        if ($this->onConnect !== null) {
            call_user_func($this->onConnect, $socket);
        }

        $this->clients[(int) $socket] = new Client($socket);

    }

    private function disconnect(&$socket, $code = 0)
    {

        if ($this->onDisconnect !== null) {
            call_user_func($this->onDisconnect, $this->clients[(int) $socket], $code);
        }

        fclose($socket);

        unset($this->sockets[(int) $socket]);
        unset($this->clients[(int) $socket]);
    }

    private function handshake($socket, $buffer)
    {
        $headers = $this->parseHeaders($buffer);

        if (!isset($headers['Sec-WebSocket-Key'])) {
            $this->disconnect($socket, 'WEBSOCKET-KEY');
        }

        $key = $headers['Sec-WebSocket-Key'];
        $key = base64_encode(pack('H*', sha1($key . $this->secret)));

        $header = "HTTP/1.1 101 Websocket Protocol Handshake\r\n" .
            "Upgrade: WebSocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Origin: {$this->address}\r\n" .
            "Sec-WebSocket-Location: ws://{$this->address}:{$this->port}{$this->path}\r\n" .
            "Sec-WebSocket-Accept: {$key} \r\n\r\n";

        if ($this->onHandshake !== null) {
            call_user_func($this->onHandshake, $socket, $header);
        }

        stream_socket_sendto($socket, $header);
    }

    private function parseHeaders($header)
    {
        $headers = array();
        $key     = '';

        foreach (explode("\n", $header) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } else if (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            } else {
                if (substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
                } elseif (!$key) {
                    $headers[0] = trim($h[0]);
                }

            }
        }

        return $headers;
    }

    private function decodeData($socket, $data)
    {
        //https://tools.ietf.org/html/draft-ietf-hybi-thewebsocketprotocol-10#section-4.2
        /*
        0                   1                   2                   3      => bytes
        0 1 2 3 4 5 6 7 0 1 2 3 4 5 6 7 0 1 2 3 4 5 6 7 0 1 2 3 4 5 6 7  => bits
        +-+-+-+-+-------+-+-------------+-------------------------------+
        |F|R|R|R| opcode|M| Payload len |    Extended payload length    |
        |I|S|S|S|  (4)  |A|     (7)     |             (16/63)           |
        |N|V|V|V|       |S|             |   (if payload len==126/127)   |
        | |1|2|3|       |K|             |                               |
        +-+-+-+-+-------+-+-------------+ - - - - - - - - - - - - - - - +
        |     Extended payload length continued, if payload len == 127  |
        + - - - - - - - - - - - - - - - +-------------------------------+
        |                               |Masking-key, if MASK set to 1  |
        +-------------------------------+-------------------------------+
        | Masking-key (continued)       |          Payload Data         |
        +-------------------------------- - - - - - - - - - - - - - - - +
        :                     Payload Data continued ...                :
        + - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +
        |                     Payload Data continued ...                |
        +---------------------------------------------------------------+

        ord =>char to ASCII (0-255)
        sprintf('%08b') => int to binary

         */
        $decodedData = array();

        $firstByteBinary = sprintf('%08b', ord($data[0])); // FIN, RSV1-3, opcode

        $secondByteBinary = sprintf('%08b', ord($data[1])); // MASK Payload length

        $opcode = hexdec(substr($firstByteBinary, 4, 4));

        $isMasked = $secondByteBinary[0] === '1';
        $mask     = null;

        $payloadLength = ord($data[1]) & 127;
        $dataLength    = null;

        switch ($opcode) {
            case 1:
                $decodedData['type'] = WS_MSG_TEXT;
                break;
            case 2:
                $decodedData['type'] = WS_MSG_BINARY;
                break;
            case 8:
                $decodedData['type'] = WS_MSG_CLOSE;
                break;
            case 9:
                $decodedData['type'] = WS_MSG_PING;
                break;
            case 10:
                $decodedData['type'] = WS_MSG_PONG;
                break;
            default:
                $this->disconnect($socket, 'decodedData');
                return array();
        }

        if ($payloadLength == 126) {
            $mask          = substr($data, 4, 4); // 4 => bytes offset from start
            $payloadOffset = 8; // offset mask + mask size
            $dataLength    = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset; // data length
        } else if ($payloadLength == 127) {
            $mask          = substr($data, 10, 4); // 10 => bytes offset from start
            $payloadOffset = 14; // offset mask + mask size

            $tmp = '';

            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[2 + $i]));
            }

            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask          = substr($data, 2, 4); // 2 => bytes offset from start
            $payloadOffset = 6; // offset mask + mask size
            $dataLength    = $payloadLength + $payloadOffset;
        }

        // data is not complete
        if (strlen($data) < $dataLength) {
            return array();
        }

        if ($isMasked === true) {
            $tmp = '';
            for ($i = $payloadOffset, $j = 0; $i < $dataLength; $i++, $j++) {
                if (!isset($data[$i])) {
                    break;
                }

                $tmp .= $data[$i] ^ $mask[$j % 4];
            }
            $decodedData['message'] = $tmp;
        } else {
            $decodedData['message'] = substr($data, $payloadOffset - 4);
        }

        return $decodedData;
    }

    /**
     * Encode data
     *
     * @param   string  $data  data to encode
     * @param   int  $type  hex value
     *
     * $type: 0x81 - text
     *        0x82 - binary
     *        0x88 - close
     *        0x89 - ping
     *        0x8A - pong
     *
     * @return  string  message
     */
    private function encodeData($data, $type = 0x81)
    {
        $frame   = array();
        $encoded = '';

        /*
        0x82 = 10000010

        FIN    1
        RSV1   0
        RSV2   0
        RSV3   0
        opcode 0010
         */
        $frame[0]   = $type;
        $dataLength = strlen($data);

        if ($dataLength <= 125) {
            $frame[1] = $dataLength; // payload len
        } else {
            $frame[1] = 126; // Extended payload length
            $frame[2] = $dataLength >> 8;
            $frame[3] = $dataLength & 0xFF;
        }

        for ($i = 0; $i < count($frame); $i++) {
            $encoded .= chr($frame[$i]);
        }

        $encoded .= $data;

        return $encoded;
    }
}
