<?php
require_once('utils/ip_anonymizer.php');
IpAnonymizer::initialize();

function getConversationFiles() {
    $files = glob('brain/*.json');
    $conversations = [];
    
    foreach ($files as $file) {
        if (basename($file) === 'ip_map.json') continue;
        
        $ip = basename($file, '.json');
        $content = json_decode(file_get_contents($file), true);
        $fileSize = filesize($file);
        $lastMessage = end($content);
        
        $conversations[] = [
            'ip' => $ip,
            'alias' => IpAnonymizer::anonymizeIp($ip),
            'last_activity' => $lastMessage['timestamp'],
            'message_count' => count($content),
            'file_size' => $fileSize,
            'last_message_date' => $lastMessage['date']
        ];
    }
    
    return $conversations;
}

function sortConversations($conversations, $sort = 'recent') {
    switch ($sort) {
        case 'recent':
            usort($conversations, function($a, $b) {
                return $b['last_activity'] - $a['last_activity'];
            });
            break;
        case 'oldest':
            usort($conversations, function($a, $b) {
                return $a['last_activity'] - $b['last_activity'];
            });
            break;
        case 'size':
            usort($conversations, function($a, $b) {
                return $b['file_size'] - $a['file_size'];
            });
            break;
    }
    return $conversations;
}

$sort = $_GET['sort'] ?? 'recent';
$conversations = getConversationFiles();
$conversations = sortConversations($conversations, $sort);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de données FULL OPEN IA</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .sort-controls {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .sort-button {
            padding: 0.5rem 1rem;
            background-color: #404040;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sort-button.active {
            background-color: #00ff9d;
            color: #1a1a1a;
        }

        .conversations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .conversation-card {
            background-color: #2d2d2d;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .conversation-card:hover {
            transform: translateY(-5px);
        }

        .conversation-card h3 {
            margin: 0 0 1rem 0;
            color: #00ff9d;
        }

        .conversation-info {
            margin-bottom: 0.5rem;
            color: #888;
        }

        .back-button {
            display: inline-block;
            margin: 1rem;
            padding: 0.5rem 1rem;
            background-color: #00ff9d;
            color: #1a1a1a;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: #00cc7d;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #2d2d2d;
            margin: 2rem auto;
            padding: 2rem;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 10px;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #fff;
        }

        .conversation-message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 8px;
        }

        .user-message {
            background-color: #404040;
            margin-left: 2rem;
        }

        .ai-message {
            background-color: #1a1a1a;
            margin-right: 2rem;
        }

        .message-timestamp {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-button">Retour au chat</a>
    
    <div class="header">
        <h1>Base de données FULL OPEN IA</h1>
    </div>

    <div class="container">
        <div class="sort-controls">
            <a href="?sort=recent" class="sort-button <?php echo $sort === 'recent' ? 'active' : ''; ?>">Plus récent</a>
            <a href="?sort=oldest" class="sort-button <?php echo $sort === 'oldest' ? 'active' : ''; ?>">Plus ancien</a>
            <a href="?sort=size" class="sort-button <?php echo $sort === 'size' ? 'active' : ''; ?>">Plus de messages</a>
        </div>

       
<div class="conversations-grid">
    <?php foreach ($conversations as $conv): ?>
    <a href="neurone.php?alias=<?php echo urlencode($conv['alias']); ?>" class="conversation-card">
        <h3>Utilisateur: <?php echo htmlspecialchars($conv['alias']); ?></h3>
        <div class="conversation-info">
            Dernière activité: <?php echo $conv['last_message_date']; ?>
        </div>
        <div class="conversation-info">
            Nombre de messages: <?php echo $conv['message_count']; ?>
        </div>
        <div class="conversation-info">
            Taille: <?php echo round($conv['file_size'] / 1024, 2); ?> KB
        </div>
    </a>
    <?php endforeach; ?>
</div>
    </div>





    <div id="conversationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        async function showConversation(alias) {
            try {
                const response = await fetch(`api/get_conversation.php?alias=${alias}`);
                const conversation = await response.json();
                
                const modalContent = document.getElementById('modalContent');
                modalContent.innerHTML = '';
                
                conversation.forEach(msg => {
                    const userDiv = document.createElement('div');
                    userDiv.className = 'conversation-message user-message';
                    userDiv.innerHTML = `
                        <div class="message-timestamp">${msg.date}</div>
                        <div>${msg.user_message}</div>
                    `;
                    modalContent.appendChild(userDiv);

                    const aiDiv = document.createElement('div');
                    aiDiv.className = 'conversation-message ai-message';
                    aiDiv.innerHTML = `
                        <div class="message-timestamp">${msg.date}</div>
                        <div>${msg.ai_response}</div>
                    `;
                    modalContent.appendChild(aiDiv);
                });

                document.getElementById('conversationModal').style.display = 'block';
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        function closeModal() {
            document.getElementById('conversationModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('conversationModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>