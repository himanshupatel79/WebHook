<?php
/**
 * Author: Himanshu Patel
 * File: webhook_sender.php
 * Created: March 22, 2025
 * Version 1.0
 * Description:  *This script processes a queue of webhooks from a file and sends them to the specified endpoints.
 * It implements an exponential backoff strategy for retrying failed webhook deliveries.
 *
 */

namespace Webhook;

define('MAX_RETRIES', 5);
define('MAX_DELAY', 60); // Maximum retry delay of 60 seconds
define('FAILURE_LIMIT', 5); // Stop sending webhooks to an endpoint after 5 failures
define('EXECUTION_TIME_LIMIT', 80); // Time limit to process webhooks
define('WEBHOOK_FILE', 'webhooks.txt');

$failedEndpoints = [];
$startTime = time();

/**
 * Sends a webhook using an exponential backoff strategy.
 */
function sendWebhook($url, $data)
{
    global $failedEndpoints, $startTime;

    $retries = 0;
    $delay = 1;

    if (isset($failedEndpoints[$url]) && $failedEndpoints[$url] >= FAILURE_LIMIT) {
        echo "Skipping $url (failure threshold reached)\n";
        return false;
    }

    while ($retries < MAX_RETRIES) {
        if (time() - $startTime > EXECUTION_TIME_LIMIT) {
            echo "Execution time limit reached. Stopping further retries.\n";
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout for response

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            echo "Webhook successfully sent to $url\n";
            return true;
        }

        echo "Failed to send webhook to $url (HTTP Code: $httpCode). Retrying in $delay seconds...\n";
        $retries++;
        sleep(min($delay, MAX_DELAY));
        $delay *= 2;
    }

    // Mark the endpoint as failed
    if (!isset($failedEndpoints[$url])) {
        $failedEndpoints[$url] = 0;
    }
    $failedEndpoints[$url]++;

    echo "Webhook delivery permanently failed for $url\n";
    return false;
}

/**
 * Reads webhooks from file and processes them.
 */
function processWebhooks()
{
    global $startTime;

    if (!file_exists(WEBHOOK_FILE)) {
        die("Error: Webhook file not found.\n");
    }

    $file = fopen(WEBHOOK_FILE, 'r');

    while (($line = fgets($file)) !== false) {
        $parts = explode(", ", trim($line));
        if (count($parts) < 4) {
            continue;
        }

        list($url, $orderId, $name, $event) = $parts;
        $webhookData = [
            "order_id" => (int) $orderId,
            "name" => $name,
            "event" => $event
        ];

        if (time() - $startTime > EXECUTION_TIME_LIMIT) {
            echo "Execution time limit reached. Stopping further processing.\n";
            break;
        }

        sendWebhook($url, $webhookData);
    }

    fclose($file);
}

// Start processing webhooks
processWebhooks();
