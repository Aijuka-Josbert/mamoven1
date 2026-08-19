<?php
/**
 * PesaJet Pay API client.
 *
 * Wraps the documented endpoints at https://payments.pesajet.com/api/v1.
 * Credentials come from environment variables (config/database.php already
 * loads .env before this file is ever used) — never hardcode the API key.
 *
 * Required in .env:
 *   PESAJET_BASE_URL=https://payments.pesajet.com/api/v1
 *   PESAJET_API_KEY=pk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * Usage:
 *   $pesajet = new PesaJetPay();
 *   $result = $pesajet->createCollection([
 *       'amount'         => 10000,
 *       'phoneNumber'    => '+256700123456',
 *       'provider'       => 'mtn',       // or 'airtel', or 'sandbox' for testing
 *       'reference'      => $order_number,
 *       'idempotencyKey' => $order_number . '-attempt-1',
 *   ]);
 */
class PesaJetPay
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeoutSeconds;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null, int $timeoutSeconds = 15)
    {
        $this->baseUrl = rtrim($baseUrl ?? (getenv('PESAJET_BASE_URL') ?: 'https://payments.pesajet.com/api/v1'), '/');
        $this->apiKey = $apiKey ?? (getenv('PESAJET_API_KEY') ?: '');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Create a COLLECTION transaction — customer pays the merchant account
     * after approving a mobile money prompt on their phone.
     *
     * Required keys in $params: amount, phoneNumber, provider, reference, idempotencyKey
     * Returns ['success' => bool, 'data' => array|null, 'error' => string|null, 'http_code' => int]
     */
    public function createCollection(array $params): array
    {
        $payload = array_merge(['type' => 'COLLECTION', 'currency' => 'UGX'], $params);
        return $this->request('POST', '/payments', $payload);
    }

    /**
     * Create a DISBURSEMENT transaction — merchant balance sends funds out.
     * Included for completeness; not currently used by the checkout flow.
     */
    public function createDisbursement(array $params): array
    {
        $payload = array_merge(['type' => 'DISBURSEMENT', 'currency' => 'UGX'], $params);
        return $this->request('POST', '/payments', $payload);
    }

    /**
     * Look up the current status of a transaction by its PesaJet transactionId.
     * Treat 'pending' and 'processing' as non-final states — poll again later.
     */
    public function getTransaction(string $transactionId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($transactionId));
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'PesaJet is not configured. Set PESAJET_API_KEY in your .env file.',
                'http_code' => 0,
            ];
        }

        $url = $this->baseUrl . $path;
        $ch = curl_init();

        $headers = [
            'X-API-Key: ' . $this->apiKey,
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $options);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            error_log('PesaJet request failed (network): ' . $curlError);
            return [
                'success' => false,
                'data' => null,
                'error' => 'Could not reach PesaJet: ' . $curlError,
                'http_code' => 0,
            ];
        }

        $decoded = json_decode($responseBody, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMessage = is_array($decoded) && isset($decoded['message'])
                ? $decoded['message']
                : 'PesaJet returned HTTP ' . $httpCode;
            error_log('PesaJet API error (' . $httpCode . '): ' . $responseBody);
            return [
                'success' => false,
                'data' => $decoded,
                'error' => $errorMessage,
                'http_code' => $httpCode,
            ];
        }

        return [
            'success' => true,
            'data' => $decoded,
            'error' => null,
            'http_code' => $httpCode,
        ];
    }
}
