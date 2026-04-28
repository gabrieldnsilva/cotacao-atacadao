<?php

function testHealthCheck() {
    $url = "http://localhost:8080/api/health";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "FAIL: Expected HTTP 200, got $httpCode\n";
        exit(1);
    }

    $data = json_decode($response, true);
    if ($data['status'] !== 'ok') {
        echo "FAIL: Expected status 'ok', got '{$data['status']}'\n";
        exit(1);
    }

    if (!isset($data['timestamp'])) {
        echo "FAIL: Timestamp missing in response\n";
        exit(1);
    }

    echo "PASS: Health check is working!\n";
}

testHealthCheck();
