<?php 

declare(strict_types=1);

namespace Kantodo\API;

/**
 * Připojí se k WS serveru a odešle na něj data
 *
 * @param   string  $authToken    Auth token
 * @param   string  $action       akce
 * @param   mixed   $value        data
 * @param   string  $projectUUID  uuid projektu
 *
 * @return  void
 */
function connectToWebsoketServer(string $authToken, string $action, $value, string $projectUUID = null) : void
{
    $data = json_encode([
        'action'  => $action,
        'value'   => $value,
        'project' => $projectUUID,
    ]);
    if ($data === false)
        return;


    \Ratchet\Client\connect('ws://127.0.0.1:8443', ['access_token', $authToken])->then(
        function(\Ratchet\Client\WebSocket $conn) use ($data)
        {
            if ($conn->send($data)) 
            {
                $conn->close();
            }
        }
    );
}