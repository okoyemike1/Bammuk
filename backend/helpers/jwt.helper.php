<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

class JWTHelper
{
    private string $secret;
    private string $algo = 'HS256';
    private int $ttlSeconds;

    public function __construct(?string $secret = null, ?int $ttlSeconds = null)
    {
        require_once __DIR__ . '/../config/config.php';
        global $jwt;
        $cfgSecret = isset($jwt['secret']) ? (string)$jwt['secret'] : 'change_this_secret';
        $cfgTtl = isset($jwt['ttl_seconds']) ? (int)$jwt['ttl_seconds'] : 86400;
        $this->secret = $secret ?: $cfgSecret;
        $this->ttlSeconds = $ttlSeconds ?: $cfgTtl;
    }

    public function createToken(array $payload): string
    {
        $header = ['typ' => 'JWT', 'alg' => $this->algo];
        $iat = time();
        $exp = $iat + $this->ttlSeconds;
        $payload['iat'] = $iat;
        $payload['exp'] = $exp;

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload))
        ];
        $signingInput = implode('.', $segments);
        $signature = $this->sign($signingInput, $this->secret);
        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    public function verifyToken(string $token): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
        $signature = $this->base64UrlDecode($encodedSignature);
        $signingInput = $encodedHeader . '.' . $encodedPayload;
        $expected = $this->sign($signingInput, $this->secret);
        if (!hash_equals($expected, $signature)) {
            return false;
        }
        if (!isset($payload['exp']) || time() >= (int)$payload['exp']) {
            return false;
        }
        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function sign(string $data, string $secret): string
    {
        return hash_hmac('sha256', $data, $secret, true);
    }
}


