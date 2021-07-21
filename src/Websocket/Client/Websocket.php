<?php

namespace Kantodo\Websocket\Client;


class Websocket
{
    private $host;
    private $address;
    private $timeout = 10;
    private $ctx;
    private $streamSocket = NULL;

    public function __construct(string $host, int $port = 80, bool $ssl = false, float $timeout = 10) {
        $this->address = ($ssl ? 'ssl://' : '') . $host . ':' . $port;
        $this->host = $host;
        $this->timeout = $timeout;
    }

    public function connect(string $path = '/') : bool
    {
        $errorCode = NULL;
        $this->ctx = stream_context_create();
        $this->streamSocket = stream_socket_client($this->address, $errorCode, $errorMsg, $this->timeout, STREAM_CLIENT_CONNECT);

        if ($errorCode != NULL) return false;

        $key = base64_encode(openssl_random_pseudo_bytes(16));

        $header = "GET {$path} HTTP/1.1\r\n"
            ."Host: {$this->host}\r\n"
            ."pragma: no-cache\r\n"
            ."Upgrade: WebSocket\r\n"
            ."Connection: Upgrade\r\n"
            ."Sec-WebSocket-Key: $key\r\n"
            ."Sec-WebSocket-Version: 13\r\n";

        // request upgrade
        $ru = fwrite($this->streamSocket, $header);

        if (!$ru)
            return false;
        
        $response = fread($this->streamSocket, 1024);

        // success ?
        return stripos($response, ' 101 ') && stripos($response, 'Sec-WebSocket-Accept: ');
    }

    public function disconnect()
    {
        if ($this->streamSocket != NULL) fclose($this->streamSocket);
    }

    public function send(string $message)
    {
        $header = '';
        if(strlen($message)<126) $header.=chr(0x80 | strlen($message));
        elseif (strlen($message)<0xFFFF) $header.=chr(0x80 | 126) . pack('n',strlen($message));
        else $header.=chr(0x80 | 127) . pack('N',0) . pack('N',strlen($message));

        // Add mask
        $mask=pack('N',rand(1,0x7FFFFFFF));
        $header.=$mask;

        // Mask application message.
        for($i = 0; $i < strlen($message); $i++)
            $message[$i]=chr(ord($message[$i]) ^ ord($mask[$i % 4]));

        return fwrite($this->streamSocket,$header.$message);
    }

    public function read()
    {
        $data = '';

        do {
            $header = fread($this->streamSocket, 2);

            if (!$header)
                return false;

            $opcode = ord($header[0]) & 0x0F;
            $final = ord($header[0]) & 0x80;
            $masked = ord($header[1]) & 0x80;
            $payloadLength = ord($header[1]) & 0x7F;

            //extra data
            $extLength = 0;

            if($payloadLength >= 0x7E){
                $extLength = 2;

                if($payloadLength == 0x7F)
                    $extLength = 8;

                $header = fread($this->streamSocket,$extLength);
                
                if (!$header)
                    return false;

                $payloadLength= 0;
                for($i = 0; $i < $extLength; $i++)
                    $payloadLength += ord($header[$i]) << ($extLength-$i-1)*8;
            }


            if($masked)
            {
                $mask=fread($this->streamSocket, 4);
                if(!$mask)
                    return false;
            }

            $frameData = '';
            while($payloadLength > 0)
            {

                $frame= fread($this->streamSocket, $payloadLength);

                if(!$frame)
                    return false;

                $payloadLength -= strlen($frame);
                $frameData .= $frame;
            }

            // ping
            if ($opcode == 9) 
            {
                //pong
                fwrite($this->streamSocket, chr(0x8A) . chr(0x80) . pack('N', rand(1,0x7FFFFFFF)));
                continue;
            }

            //close
            if ($opcode == 8) 
            {
                $this->disconnect();
                return;
            }


            // Unmask data
            $dataLength = strlen($frameData);
            if($masked)
                for ($i = 0; $i < $dataLength; $i++)
                    $data .= $frameData[$i] ^ $mask[$i % 4];
            else
                $data .= $frameData;
        } while (!$final);

        return $data;
    }
}






?>