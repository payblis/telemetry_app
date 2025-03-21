<?php
// Définir un répertoire de session personnalisé
$custom_session_path = __DIR__ . '/sessions';
if (!file_exists($custom_session_path)) {
    mkdir($custom_session_path, 0755, true);
}
session_save_path($custom_session_path);

require_once 'config.php';
require_once 'database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemetry App - Assistant Moto</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #333;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .main-content {
            display: flex;
            gap: 20px;
        }
        .session-panel {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chat-panel {
            flex: 2;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .circuit-select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .user-message {
            background: #e3f2fd;
            margin-left: 20%;
        }
        .ai-message {
            background: #f5f5f5;
            margin-right: 20%;
        }
        .chat-input {
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Telemetry App - Assistant Moto</h1>
    </div>
    
    <div class="container">
        <div class="main-content">
            <div class="session-panel">
                <h2>Nouvelle Session</h2>
                <button class="btn" onclick="createNewSession()">Créer une session</button>
                <select class="circuit-select" id="circuitSelect">
                    <option value="">Sélectionnez un circuit</option>
                    <option value="lemans">Circuit du Mans</option>
                    <option value="nogaro">Circuit de Nogaro</option>
                    <option value="barcelone">Circuit de Barcelone</option>
                </select>
                <div id="circuitInfo"></div>
            </div>
            
            <div class="chat-panel">
                <h2>Conversation avec l'Assistant</h2>
                <div class="chat-messages" id="chatMessages"></div>
                <div class="chat-input">
                    <input type="text" id="messageInput" placeholder="Posez votre question...">
                    <button class="btn" onclick="sendMessage()">Envoyer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        
        function createNewSession() {
            const circuit = document.getElementById('circuitSelect').value;
            if (!circuit) {
                alert('Veuillez sélectionner un circuit');
                return;
            }
            // Appel AJAX pour créer une nouvelle session
            fetch(SITE_URL + '/api/create_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ circuit: circuit })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('circuitInfo').innerHTML = data.circuitInfo;
            });
        }

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value;
            if (!message) return;

            // Afficher le message de l'utilisateur
            appendMessage(message, true);
            messageInput.value = '';

            // Appel AJAX pour envoyer le message à l'API
            fetch(SITE_URL + '/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                appendMessage(data.response, false);
            });
        }

        function appendMessage(message, isUser) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'ai-message'}`;
            messageDiv.textContent = message;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html> 