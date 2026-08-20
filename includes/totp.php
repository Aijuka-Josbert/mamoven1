<?php
/**
 * Minimal RFC 6238 TOTP (Time-based One-Time Password) implementation.
 * Compatible with Google Authenticator, Authy, and any standard TOTP app.
 * No external dependency — self-contained so it works without needing
 * Packagist/Composer network access to add a library.
 */
class TOTP
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PERIOD = 30;
    private const DIGITS = 6;

    /** Generate a random base32 secret (160 bits, standard for TOTP). */
    public static function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return self::base32Encode($bytes);
    }

    /** Build the otpauth:// URI for QR code generation. */
    public static function getOtpAuthUrl(string $secret, string $accountLabel, string $issuer): string
    {
        $label = rawurlencode($issuer . ':' . $accountLabel);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);
        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Verify a submitted code against the secret, allowing a small window
     * (±1 period, ~30s each way) to tolerate clock drift between server
     * and the user's phone.
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $currentTimeSlice = (int) floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::generateCode($secret, $currentTimeSlice + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    private static function generateCode(string $secret, int $timeSlice): string
    {
        $secretKey = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );
        $code = $truncated % (10 ** self::DIGITS);
        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $binaryString = '';
        foreach (str_split($data) as $byte) {
            $binaryString .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binaryString, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= $alphabet[bindec($chunk)];
        }
        return $result;
    }

    private static function base32Decode(string $secret): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $secret = strtoupper($secret);
        $binaryString = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($binaryString, 8) as $byte) {
            if (strlen($byte) < 8) continue;
            $bytes .= chr(bindec($byte));
        }
        return $bytes;
    }
}
