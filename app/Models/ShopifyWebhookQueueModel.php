<?php
namespace App\Models;

use Core\Database;

class ShopifyWebhookQueueModel {

    /**
     * Save a new webhook entry to the queue.
     *
     * @param string $topic The webhook topic.
     * @param mixed $data The webhook payload (object or JSON string).
     * @param string $logMessages Log messages (each line separated by PHP_EOL).
     * @return int The ID of the newly inserted record.
     */
    public function saveWebhookQueue($topic, $data, $logMessages = '') {
        // Ensure $data is stored as a string
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO shopify_webhook_queue (topic, data, log_messages) VALUES (:topic, :data, :log_messages)");
        $stmt->execute([
            'topic'       => $topic,
            'data'        => $data,
            'log_messages'=> $logMessages,
        ]);
        return $db->lastInsertId();
    }

    /**
     * Retrieve all pending webhook entries (not processed yet).
     *
     * @return array
     */
    public function getPendingWebhooks() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM shopify_webhook_queue WHERE processed = 0 ORDER BY created_at ASC LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark a webhook entry as processed.
     *
     * @param int $id The ID of the webhook entry.
     * @return int The number of rows updated.
     */
    public function markAsProcessed($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE shopify_webhook_queue SET processed = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
