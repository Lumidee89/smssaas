<?php

namespace App\Contracts;

interface PushSender
{
    /** Returns false only when the device token is permanently invalid. */
    public function send(string $token, string $title, string $body, array $data = []): bool;
}
