
# Webhook Sender - PHP Script

* **Author**: Himanshu Patel
* **File Name** : webhook_sender.php , **Created** on: 22 March 2025
* **File Name** : WebhookSenderTest.php , **Created** on: 22 March 2025

# Overview

This application processes webhooks by reading data from a file (`webhooks.txt`), sending requests to specified URLs, and handling retries in case of failures.  
The implementation includes **exponential backoff** for retry logic and **PHPUnit** tests for validation.

---

## Features
- Reads webhooks from `webhooks.txt`
- Sends webhooks as JSON `POST` requests
- Retries on failure with **exponential backoff** (1s, 2s, 4s, 8s, etc.)
- Stops retrying after **5 attempts**
- Blocks an endpoint after **5 consecutive failures**
- Ensures execution completes within **80 seconds**

---

## Prerequisites
- PHP **7.4+**
- Composer (for dependency management)
- cURL enabled in PHP

---

## Setup & Usage

### 1️⃣ Install PHP (if not installed)
Check if PHP is installed:
```sh
php -v
```

If not installed, use:

```sh
sudo apt install php-cli  # Ubuntu  
brew install php          # macOS  
```

2️⃣ Install Dependencies
Clone the repository and navigate to the project directory:

```sh
git clone https://github.com/himanshupatel79/WebHook.git 
cd WebHook  
composer install  
```

3️⃣ Run the Webhook Processor
```sh
php webhook_sender.php
This will read webhooks.txt, send requests, and retry failures as needed.
```


⏳ Measure Execution Time
To check how long the script takes to execute, use:

```sh
time php webhook_sender.php
```
**Explanation**:

* The time command measures the total execution time of the php webhook_sender.php script.
* This helps ensure the process completes within the 80-second limit.
* It outputs three times:
  * real → total elapsed time
  * user → CPU time spent in user mode
  * sys → CPU time spent in kernel mode

**Example output**:
```sql
real    0m12.345s  
user    0m2.678s  
sys     0m0.432s
```  
This means the script took 12.345 seconds to execute.

---

# Running Tests
Ensure PHPUnit is installed:

```sh
composer require --dev phpunit/phpunit
```
Then, run the tests:

```sh
vendor/bin/phpunit WebhookSenderTest.php
```
## Troubleshooting
If PHP is not installed, download it from the official site.
If you see the "Class TestCase not found" error while running tests, ensure you're using:
use PHPUnit\Framework\TestCase;
If you installed PHPUnit using Composer, run tests with:
vendor/bin/phpunit CollisionDetectionTest.php

---

# Design Decisions
1️⃣ **Exponential Backoff for Retries**
* On failure, retries are attempted with increasing delays: **1s, 2s, 4s, 8s**...
* Maximum delay per webhook: **1 minute**
* If an endpoint fails **5 times**, further webhooks to that endpoint are skipped.

2️⃣ **Parallel Processing Consideration**
* The current implementation processes webhooks **sequentially**.
* Future improvements could include **multi-threading or asynchronous processing** for higher efficiency.

3️⃣ **Error Handling**
* **Logs failures and retries** for debugging.
* Ensures webhook failures **do not block** the processing of other webhooks.

---

# Security Considerations
✅ **Data Validation**: Ensures webhooks contain valid JSON payloads.

✅ **Rate Limiting**: Avoids overwhelming an endpoint with too many retries.

✅ **Timeout Handling**: Ensures the script exits gracefully if execution time exceeds limits.

---

## License
This script is open-source and can be freely modified and distributed.
