<?php
class SecurityHelper {
    private static $algo = 'aes-256-cbc';

    /**
     * Get the 32-byte encryption key derived from environment secret_key
     */
    private static function getKey() {
        $secret = getenv('secret_key');
        if (!$secret) {
            // Fallback just in case, though it should be set
            $secret = 'imissyousomuchmysins';
        }
        return hash('sha256', $secret, true); // Return 32 bytes raw binary string
    }

    /**
     * Encrypt a string
     */
    public static function encrypt($data) {
        if (empty($data)) return $data;
        $key = self::getKey();
        $ivLength = openssl_cipher_iv_length(self::$algo);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, self::$algo, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a string
     */
    public static function decrypt($data) {
        if (empty($data)) return $data;
        $decoded = base64_decode($data);
        if ($decoded === false) return $data;

        $key = self::getKey();
        $ivLength = openssl_cipher_iv_length(self::$algo);
        
        if (strlen($decoded) <= $ivLength) return $data; // Not long enough

        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        $decrypted = openssl_decrypt($encrypted, self::$algo, $key, OPENSSL_RAW_DATA, $iv);
        
        // Return original data if decryption fails (e.g., it wasn't encrypted)
        return $decrypted !== false ? $decrypted : $data;
    }

    /**
     * Hash the ID for searching (Blind Index)
     */
    public static function hashThaiId($data) {
        if (empty($data)) return $data;
        return hash('sha256', "thai_id_salt_" . $data);
    }
}
?>
