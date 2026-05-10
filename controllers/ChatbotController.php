<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$userMessage = trim($data['message'] ?? '');

if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(["error" => "No message provided"]);
    exit;
}

$hostelKeywords = [
    'hostel', 'room', 'rooms', 'bed', 'beds', 'facility', 'facilities',
    'fee', 'fees', 'staff', 'warden', 'owner', 'mess', 'canteen',
    'security', 'laundry', 'cleaning', 'notice', 'complaint', 'student',
    'registration', 'admission', 'housing', 'dorm', 'dormitory', 'wifi',
    'internet', 'study', 'board', 'meal', 'food', 'maintenance'
];
$userLower = strtolower($userMessage);
$hasHostelTopic = false;
foreach ($hostelKeywords as $keyword) {
    if (strpos($userLower, $keyword) !== false) {
        $hasHostelTopic = true;
        break;
    }
}

if (!$hasHostelTopic) {
    echo json_encode([
        "reply" => "I can only answer questions about Hostel facilities, rooms, fees, staff, notices, and related hostel services. Please ask a hostel-related question."
    ]);
    exit;
}

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$apiKey = trim($_ENV['MISTRAL_API_KEY'] ?? '');
$model = trim($_ENV['MISTRAL_MODEL'] ?? 'mistral-small-latest');
$maxTokens = (int) ($_ENV['MISTRAL_MAX_OUTPUT_TOKENS'] ?? 300);
$temperature = (float) ($_ENV['MISTRAL_TEMPERATURE'] ?? 0.7);
$apiUrl = trim($_ENV['MISTRAL_API_URL'] ?? 'https://api.mistral.ai/v1/chat/completions');

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(["error" => "Mistral API key is not configured."]);
    exit;
}

$payload = [
    "model" => $model,
    "messages" => [
        [
            "role" => "system",
            "content" => "You are the official AI Assistant for Pentatonic Hostel. You must only answer questions related to hostel facilities, rooms, fees, staff, notices, student housing, mess, laundry, cleaning, security, and hostel policies. If the user asks anything unrelated to the hostel, refuse politely with a short statement that you only answer hostel-related questions.

STRICT FORMATTING RULES:
1. NEVER use Markdown formatting like asterisks (**) or bullet points (-).
2. ONLY use plain text with numbered lists (1, 2, 3) when appropriate.
3. Use a single line break between different sections.
4. Keep the tone professional and the answers concise.
5. Keep answers short and mostly under 200 tokens.

Example format:
1. Facilities: We offer WiFi, laundry, and a study area.
2. Staff: Contact the Warden for room allocation."
        ],
        ["role" => "user", "content" => $userMessage]
    ],
    "temperature" => $temperature,
    "max_tokens" => $maxTokens
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(502);
    echo json_encode(["error" => curl_error($ch)]);
} elseif ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(["error" => "Mistral API error ($httpCode)."]);
} else {
    $body = json_decode($response, true);
    // Mistral uses the same response format as OpenAI: choices[0].message.content
    $reply = $body['choices'][0]['message']['content'] ?? "No response received.";
    echo json_encode(["reply" => trim($reply)]);
}

curl_close($ch);
