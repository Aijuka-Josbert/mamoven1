<?php
// mtn_working_fixed.php - Fixed with proper headers

// ========== YOUR CREDENTIALS ==========
$primaryKey = "f1aeeaffb99349619fb3b8b50eaa6b39";
$callbackUrl = "https://unmanually-autecious-dannie.ngrok-free.dev";
$environment = "sandbox";
$baseUrl = "https://sandbox.momodeveloper.mtn.com";

// ========== Generate UUID v4 ==========
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function callAPI($url, $method, $headers, $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => $response, 'error' => $error];
}

echo "========================================\n";
echo "MTN MoMo Sandbox Integration\n";
echo "========================================\n\n";

// ========== STEP 1: Create API User ==========
$apiUserId = generateUUID();
echo "1️⃣ Creating API User: $apiUserId\n";

$response = callAPI(
    "$baseUrl/v1_0/apiuser",
    "POST",
    [
        "Content-Type: application/json",
        "X-Reference-Id: $apiUserId",
        "Ocp-Apim-Subscription-Key: $primaryKey"
    ],
    json_encode(["providerCallbackHost" => str_replace("https://", "", $callbackUrl)])
);

if ($response['code'] == 201 || $response['code'] == 409) {
    echo "   ✅ API User created (or already exists)\n";
} else {
    die("   ❌ Failed: " . $response['response'] . "\n");
}

// ========== STEP 2: Get API Key (FIXED - Added Content-Length) ==========
echo "\n2️⃣ Getting API Key...\n";

$headers = [
    "Ocp-Apim-Subscription-Key: $primaryKey",
    "Content-Type: application/json",
    "Content-Length: 0"
];

$ch = curl_init("$baseUrl/v1_0/apiuser/$apiUserId/apikey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, "");  // Empty string for body

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 201) {
    $apiKeyData = json_decode($response, true);
    $apiKey = $apiKeyData['apiKey'];
    echo "   ✅ API Key obtained: " . substr($apiKey, 0, 20) . "...\n";
} else {
    die("   ❌ Failed: HTTP $httpCode\nResponse: $response\n");
}

// ========== STEP 3: Create API User OAuth Token ==========
echo "\n3️⃣ Creating OAuth Token for API User...\n";

$ch = curl_init("$baseUrl/collection/token/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode("$apiUserId:$apiKey"),
    "Ocp-Apim-Subscription-Key: $primaryKey",
    "Content-Type: application/json",
    "Content-Length: 2"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'];
    echo "   ✅ Access token obtained\n";
} else {
    die("   ❌ Failed: HTTP $httpCode\nResponse: $response\n");
}

// ========== STEP 4: Request Payment ==========
echo "\n4️⃣ Requesting payment...\n";

$transactionId = generateUUID();
$testPhone = "256772123456";  // Sandbox test number for success
$amount = "500";
$currency = "EUR";

$payload = [
    "amount" => $amount,
    "currency" => $currency,
    "externalId" => "order_" . time(),
    "payer" => [
        "partyIdType" => "MSISDN",
        "partyId" => $testPhone
    ],
    "payerMessage" => "MamaOven Bakery - Fresh bread",
    "payeeNote" => "Thank you for shopping at MamaOven"
];

echo "   Transaction ID: $transactionId\n";
echo "   Amount: UGX $amount\n";
echo "   Customer: $testPhone\n";

$ch = curl_init("$baseUrl/collection/v1_0/requesttopay");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "X-Reference-Id: $transactionId",
    "X-Target-Environment: $environment",
    "Content-Type: application/json",
    "Ocp-Apim-Subscription-Key: $primaryKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 202) {
    echo "   ✅ Payment request ACCEPTED by MTN!\n";
    echo "\n========================================\n";
    echo "📱 SANDBOX SIMULATION:\n";
    echo "   The system is now simulating a PIN prompt on phone: $testPhone\n";
    echo "   For test number $testPhone → SUCCESSFUL payment\n";
    echo "========================================\n\n";
    
    // ========== STEP 5: Check Status ==========
    echo "5️⃣ Checking transaction status (waiting 5 seconds)...\n";
    sleep(5);
    
    $ch = curl_init("$baseUrl/collection/v1_0/requesttopay/$transactionId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "X-Target-Environment: $environment",
        "Ocp-Apim-Subscription-Key: $primaryKey"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $status = json_decode($response, true);
        echo "\n📊 TRANSACTION RESULT:\n";
        echo "─────────────────────────────────\n";
        echo "Status: " . ($status['status'] ?? 'UNKNOWN') . "\n";
        echo "Amount: " . ($status['amount'] ?? $amount) . " " . ($status['currency'] ?? $currency) . "\n";
        if (isset($status['financialTransactionId'])) {
            echo "Financial ID: " . $status['financialTransactionId'] . "\n";
        }
        echo "─────────────────────────────────\n";
        
        if ($status['status'] == 'SUCCESSFUL') {
            echo "\n🎉 SIMULATION: Customer entered PIN correctly!\n";
            echo "💰 In production, money would be deducted from customer's account\n";
            echo "   and sent to your merchant wallet.\n";
        } elseif ($status['status'] == 'PENDING') {
            echo "\n⏳ Still waiting for customer to enter PIN...\n";
        } else {
            echo "\n❌ Payment failed or was cancelled.\n";
        }
    } else {
        echo "   ⚠️ Status check returned HTTP $httpCode\n";
    }
} else {
    echo "   ❌ Payment request failed!\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $response\n";
}

echo "\n✨ Script completed.\n";
?>