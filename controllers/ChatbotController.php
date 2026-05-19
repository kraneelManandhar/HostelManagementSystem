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

HOSTEL KNOWLEDGE BASE:

ROOMS:
Pentatonic Hostel offers two room types:
1. Single occupancy rooms - for one student
2. Double occupancy rooms - for two students sharing
Room allocation is managed by the Warden. Students can view their assigned room from their personal dashboard.

FEES:
Fee records are maintained per student. Students can log in to view their fee status and payment history from their dashboard. For payment queries, contact the Admin or Warden. Fee management is handled by the Owner and Admin.

FACILITIES:
1. Single and double occupancy rooms
2. Mess and dining services
3. Laundry services
4. Cleaning and maintenance
5. Notice board for hostel announcements
6. WiFi connectivity
7. Study area
8. Complaint submission system
9. 24/7 security

STAFF AND ROLES:
1. Owner - Full access: manages students, rooms, fees, complaints, notices, and staff
2. Admin - Administrative dashboard with management capabilities
3. Warden - Day-to-day hostel supervision and room allocation
4. Student - Personal dashboard to view room info, fees, and notices; submit complaints

COMPLAINTS:
Students can submit and delete their own complaints through the student dashboard. The Owner reviews all complaints. For urgent matters, contact the Warden directly.

NOTICES:
Hostel notices are posted by the Owner or Admin and can be viewed by all students from their dashboard.

REGISTRATION:
New student registration is a multi-step process that includes uploading a profile photo. Contact the Admin to initiate registration.

SECURITY:
Physical security is maintained 24/7. System access is role-based; each user can only access their authorized section.

POLICIES:
1. Room allocation must be formally assigned through the system by the Warden.
2. Fee payments must be kept up to date. View your status from the student dashboard.
3. Complaints must be submitted through the official complaint system.
4. All hostel notices are official communications and must be followed.
5. Unauthorized access to other users' information is strictly prohibited."
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
