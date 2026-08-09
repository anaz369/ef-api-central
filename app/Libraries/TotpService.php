<?php

namespace App\Libraries;

/**
 * Self-contained TOTP service (RFC 6238 / Google Authenticator compatible).
 * No external composer dependency required.
 */
class TotpService
{
    private string $issuer = 'EF Intelligence API Central';

    /**
     * Generate a new random TOTP secret (base32-encoded, 20 bytes = 160 bits).
     */
    public function createSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    /**
     * Verify a 6-digit TOTP code against a secret.
     * Allows 1 time-step discrepancy each side (±30 seconds).
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code      = str_pad(trim($code), 6, '0', STR_PAD_LEFT);
        $timeSlice = (int) (time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            if ($this->generateCode($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build the otpauth:// URI for QR code rendering.
     */
    public function getOtpauthUrl(string $email, string $secret): string
    {
        $label = rawurlencode($this->issuer . ':' . $email);
        return 'otpauth://totp/' . $label
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($this->issuer)
            . '&digits=6&period=30';
    }

    // -------------------------------------------------------------------------

    private function generateCode(string $secret, int $timeSlice): string
    {
        $key  = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);

        $offset = ord($hash[19]) & 0x0F;
        $code   = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % 1_000_000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output   = '';
        $v        = 0;
        $vBits    = 0;

        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $v      = ($v << 8) | ord($input[$i]);
            $vBits += 8;
            while ($vBits >= 5) {
                $vBits  -= 5;
                $output .= $alphabet[($v >> $vBits) & 0x1F];
            }
        }
        if ($vBits > 0) {
            $output .= $alphabet[($v << (5 - $vBits)) & 0x1F];
        }
        return $output;
    }

    private function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input    = strtoupper(trim($input));
        $output   = '';
        $v        = 0;
        $vBits    = 0;

        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $pos = strpos($alphabet, $input[$i]);
            if ($pos === false) {
                continue;
            }
            $v      = ($v << 5) | $pos;
            $vBits += 5;
            if ($vBits >= 8) {
                $vBits  -= 8;
                $output .= chr(($v >> $vBits) & 0xFF);
            }
        }
        return $output;
    }
}
