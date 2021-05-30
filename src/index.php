<?php 
    session_start();


    $_SESSION['test'] = 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div id="chat">

    </div>
    <input type="text" id="text">
    <button id="sendBtn">Send</button>
    <script>
        var input = document.getElementById("text");
        var sendBtn = document.getElementById("sendBtn");

        sendBtn.onclick = sendMessage;

        function sendMessage() {
            var text = input.value;
            socket.send(text);


        }

        var socket = new WebSocket("ws://localhost:8090/Kantodo/src/Websocket/server.php");
        //var socket = new WebSocket("ws://127.0.0.1:9000/server.php");

        socket.addEventListener('open', function (event) {
            //socket.send('Hello Server!');
        });

        // Listen for messages
        socket.addEventListener('message', function (event) {
            console.log('Message from server ', event.data);
        });

    </script>
</body>
</html>