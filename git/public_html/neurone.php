<?php
require_once('utils/ip_anonymizer.php');
IpAnonymizer::initialize();

$alias = $_GET['alias'] ?? '';
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

usort($conversations, function($a, $b) {
    return $b['last_activity'] - $a['last_activity'];
});

$currentIndex = array_search($alias, array_column($conversations, 'alias'));
$currentConversation = $conversations[$currentIndex] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la conversation</title>
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

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .navigation a {
            padding: 0.5rem 1rem;
            background-color: #00ff9d;
            color: #1a1a1a;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .navigation a:hover {
            background-color: #00cc7d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Détails de la conversation</h1>
    </div>

    <div class="container">
        <div class="navigation">
            <?php if ($currentIndex > 0): ?>
                <a href="neurone.php?alias=<?php echo urlencode($conversations[$currentIndex - 1]['alias']); ?>">Précédent</a>
            <?php endif; ?>
            <?php if ($currentIndex < count($conversations) - 1): ?>
                <a href="neurone.php?alias=<?php echo urlencode($conversations[$currentIndex + 1]['alias']); ?>">Suivant</a>
            <?php endif; ?>
        </div>

        <?php if ($currentConversation): ?>
            <?php $content = json_decode(file_get_contents('brain/' . $currentConversation['ip'] . '.json'), true); ?>
            <?php foreach ($content as $msg): ?>
                <div class="conversation-message user-message">
                    <div class="message-timestamp"><?php echo $msg['date']; ?></div>
                    <div><?php echo htmlspecialchars($msg['user_message']); ?></div>
                </div>
                <div class="conversation-message ai-message">
                    <div class="message-timestamp"><?php echo $msg['date']; ?></div>
                    <div><?php echo htmlspecialchars($msg['ai_response']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune conversation trouvée.</p>
        <?php endif; ?>
    </div>
</body>
</html>