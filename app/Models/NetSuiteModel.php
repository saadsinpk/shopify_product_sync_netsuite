<?php
namespace App\Models;

use Core\Database;

class NetSuiteModel {
    /**
     * Get NetSuite settings by user ID
     */
    public function getSettingsByUserId($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM netsuite_settings WHERE user_id = :userId");
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Update NetSuite settings for a user
     */
    public function updateSettings($userId, $accountId, $tokenId, $tokenSecret) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE netsuite_settings SET account_id = :accountId, token_id = :tokenId, token_secret = :tokenSecret WHERE user_id = :userId");
        $stmt->execute([
            'userId' => $userId,
            'accountId' => $accountId,
            'tokenId' => $tokenId,
            'tokenSecret' => $tokenSecret
        ]);
        return $stmt->rowCount();
    }
}
