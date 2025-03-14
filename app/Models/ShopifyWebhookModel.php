<?php
namespace App\Models;

use Core\Database;

class ShopifyWebhookModel {

    /**
     * Save a new webhook entry.
     *
     * @param string $topic The Shopify webhook topic.
     * @param string $data The raw JSON payload.
     * @param string $logs Any log messages.
     * @return int The ID of the newly inserted webhook.
     */
    public function saveWebhook($topic, $data, $logs = '') {
        // Ensure $data is a string. If it's an object, convert it to JSON.
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO shopify_webhooks (topic, data, logs) VALUES (:topic, :data, :logs)");
        $stmt->execute([
            'topic' => $topic,
            'data'  => $data,
            'logs'  => $logs,
        ]);
        return $db->lastInsertId();
    }

    /**
     * Get a webhook entry by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getWebhookById($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM shopify_webhooks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all webhook entries.
     *
     * @return array
     */
    public function getAllWebhooks() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM shopify_webhooks ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Update logs for a given webhook entry.
     *
     * @param int $id
     * @param string $logs
     * @return int The number of rows updated.
     */
    public function updateWebhookLogs($id, $logs) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE shopify_webhooks SET logs = :logs WHERE id = :id");
        $stmt->execute([
            'logs' => $logs,
            'id'   => $id,
        ]);
        return $stmt->rowCount();
    }

    /**
     * Delete a webhook entry.
     *
     * @param int $id
     * @return int The number of rows deleted.
     */
    public function deleteWebhook($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM shopify_webhooks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
