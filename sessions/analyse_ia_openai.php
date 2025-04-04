<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$apiKey = 'sk-YOUR_API_KEY'; // Remplace par ta clé OpenAI

function callChatGPT($prompt) {
    global $apiKey;
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un expert en réglage de moto de course. Donne des conseils clairs et concrets pour régler une moto selon le ressenti du pilote.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $apiKey,
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);
    return $response['choices'][0]['message']['content'] ?? 'Erreur de réponse IA';
}
?>

<h2>Analyse IA – Réglage moto personnalisé</h2>
<form method="POST">
    <textarea name="ressenti" rows="6" cols="80" placeholder="Décris ici le comportement de ta moto sur piste..."></textarea><br><br>
    <button type="submit">Obtenir une analyse IA</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ressenti'])) {
    $prompt = $_POST['ressenti'];
    $reponse = callChatGPT($prompt);
    echo "<h3>Conseils IA :</h3><div style='border:1px solid #ccc;padding:10px;background:#f9f9f9'>";
    echo nl2br(htmlspecialchars($reponse));
    echo "</div>";
}
include '../includes/footer.php';
?>