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

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$apiKey = trim($_ENV['GEMINI_API_KEY'] ?? '');
$model = trim($_ENV['GEMINI_MODEL'] ?? 'gemini-1.5-flash');
$maxOutputTokens = (int) ($_ENV['GEMINI_MAX_OUTPUT_TOKENS'] ?? 300);
$temperature = (float) ($_ENV['GEMINI_TEMPERATURE'] ?? 0.7);

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(["error" => "AI chat is not configured. Missing GEMINI_API_KEY in .env."]);
    exit;
}

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . rawurlencode($model) . ":generateContent?key=" . rawurlencode($apiKey);

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "System Instructions: You are a helpful assistant for a hostel management system. Help students and visitors with questions about hostel facilities, rooms, fees, and staff. Keep answers concise and friendly."]
            ]
        ],
        [
            "parts" => [
                ["text" => $userMessage]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => $temperature,
        "maxOutputTokens" => $maxOutputTokens
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(502);
    echo json_encode(["error" => curl_error($ch)]);
} elseif ($httpCode !== 200) {
    http_response_code(502);
    echo json_encode(["error" => "Gemini API error ($httpCode). Check if your API key is valid."]);
} else {
    $body = json_decode($response, true);
    $reply = $body['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't get a response.";
    echo json_encode(["reply" => trim($reply)]);
}

curl_close($ch);
