<?php


use PHPUnit\Framework\TestCase;
use function Webhook\sendWebhook;
use function Webhook\processWebhooks;

require_once 'webhook_sender.php';

class WebhookSenderTest extends TestCase
{
    public function testWebhookProcessing()
    {
        $url = "https://webhook-test.info1100.workers.dev/success1";
        $data = [
            "order_id" => 1,
            "name" => "Test User",
            "event" => "Test Event"
        ];

        $result = sendWebhook($url, $data);
        $this->assertTrue($result);
    }

    public function testExponentialBackoff()
    {
        $url = "https://webhook-test.info1100.workers.dev/fail1";
        $data = [
            "order_id" => 2,
            "name" => "Failing User",
            "event" => "Failure Test"
        ];

        $result = sendWebhook($url, $data);
        $this->assertFalse($result);
    }

    public function testExecutionTimeLimit()
    {
        $startTime = time();
        processWebhooks();
        $endTime = time();
        $this->assertLessThanOrEqual(80, $endTime - $startTime);
    }
}

?>
