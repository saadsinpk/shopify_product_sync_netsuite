<?php
namespace App\Models;

use Core\Database;

class ShopifyModel {
    /**
     * Get Shopify settings by user ID
     */
    public function getSettingsByUserId($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM shopify_settings WHERE user_id = :userId");
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Update Shopify settings for a user
     */
    public function updateSettings($userId, $domainName, $apiKey, $apiSecretKey, $webhook) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE shopify_settings SET domain_name = :domainName, api_key = :apiKey, api_secret_key = :apiSecretKey, webhook = :webhook WHERE user_id = :userId");
        $stmt->execute([
            'userId' => $userId,
            'domainName' => $domainName,
            'apiKey' => $apiKey,
            'apiSecretKey' => $apiSecretKey,
            'webhook' => $webhook
        ]);
        return $stmt->rowCount();
    }

}
