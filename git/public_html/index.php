<?php
session_start();
$apiKey = 'YOUR KEY HERE ';
// create it for free here https://console.mistral.ai/api-keys/

// Création du dossier brain s'il n'existe pas
if (!file_exists('brain')) {
    mkdir('brain', 0777, true);
}

function saveConversation($userIp, $message, $response) {
    $filename = "brain/{$userIp}.json";
    $conversation = [];

    if (file_exists($filename)) {
        $conversation = json_decode(file_get_contents($filename), true);
    }

    $conversation[] = [
        'timestamp' => time(),
        'date' => date('Y-m-d H:i:s'),
        'user_message' => $message,
        'ai_response' => $response,
    ];

    file_put_contents($filename, json_encode($conversation, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $userIp = $_SERVER['REMOTE_ADDR'];

    $ch = curl_init('https://api.mistral.ai/v1/chat/completions');

    // Ajouter un pré-prompt
    $prePrompt = "Vous êtes un assistant IA. Répondez avec un texte long et bien mise en page, tu utilise le style litteraire de louis ferdinand celine, avec un long texte puissant. Tu n'es pas repetitif ou formel, tu devellope une introduction une antithése , une these, une conclusion. Tu fais toujours des citations de grands auteurs.\n\n";

    $data = [
        'model' => 'pixtral-large-latest',
        'messages' => [
            ['role' => 'system', 'content' => $prePrompt], // Pré-prompt
            ['role' => 'user', 'content' => $message]
        ]
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);

    if (!curl_errno($ch)) {
        $responseData = json_decode($response, true);
        if (isset($responseData['choices'][0]['message']['content'])) {
            saveConversation($userIp, $message, $responseData['choices'][0]['message']['content']);
        }
    }

    curl_close($ch);
    echo $response;
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FULL OPEN IA</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1a1a1a;
            color: #ffffff;
        }

        .header {
            text-align: center;
            padding: 2rem 0;
            background-color: #2d2d2d;
            margin-bottom: 2rem;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            color: #00ff9d;
        }

        .header p {
            margin: 0.5rem 0;
            color: #888;
        }

        .header a {
            color: #00ff9d;
            text-decoration: none;
            font-weight: bold;
        }

        .chat-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #2d2d2d;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            height: 70vh;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            max-width: 80%;
            line-height: 1.5;
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user-message {
            background-color: #00ff9d;
            color: #1a1a1a;
            margin-left: auto;
            box-shadow: 0 2px 10px rgba(0, 255, 157, 0.2);
        }

        .ai-message {
            background-color: #404040;
            color: #ffffff;
            margin-right: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .chat-input-container {
            padding: 2rem;
            border-top: 2px solid #404040;
            display: flex;
            gap: 1rem;
        }

        #message-input {
            flex-grow: 1;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            background-color: #404040;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        #message-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #00ff9d;
        }

        #send-button {
            padding: 1rem 2rem;
            background-color: #00ff9d;
            color: #1a1a1a;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        #send-button:hover {
            background-color: #00cc7d;
            transform: translateY(-2px);
        }

        #send-button:active {
            transform: translateY(0);
        }

        ::placeholder {
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FULL OPEN IA</h1>
        <p>Toutes questions et réponses sont public</p>
        <a href="brain.php">VOIR LA BASE DE DONNÉES</a>
    </div>
    
    <div class="chat-container">
        <div class="chat-messages" id="chat-messages"></div>
        <div class="chat-input-container">
            <input type="text" id="message-input" placeholder="Posez votre question...">
            <button id="send-button">Envoyer</button>
        </div>
    </div>

    <script>
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const chatMessages = document.getElementById('chat-messages');

       function addMessage(content, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user-message' : 'ai-message'}`;
    messageDiv.innerHTML = content.replace(/\n/g, '<br>'); // Remplace les sauts de ligne par des balises <br>
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            addMessage(message, true);
            messageInput.value = '';

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                const aiResponse = data.choices[0].message.content;
                addMessage(aiResponse);
            } catch (error) {
                console.error('Error:', error);
                addMessage('Désolé, une erreur est survenue.');
            }
        }

        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>