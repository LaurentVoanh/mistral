<?php
header('Content-Type: application/json');

require_once('../utils/ip_anonymizer.php');
IpAnonymizer::initialize();

$alias = $_GET['alias'] ?? '';
$ip = IpAnonymizer::getOriginalIp($alias);

if (!$ip || !file_exists("../brain/{$ip}.json")) {
    echo json_encode(['error' => 'Conversation non trouvée']);
    exit;
}

$conversation = json_decode(file_get_contents("../brain/{$ip}.json"), true);
echo json_encode($conversation);
?>