<?php

namespace App\Tenancy;

class DomainVerifier
{
    public function verify(string $domain, string $token): bool
    {
        $records = dns_get_record('_schoolos-verification.'.$domain, DNS_TXT);
        return collect($records ?: [])->contains(fn ($record) => hash_equals($token, (string) ($record['txt'] ?? '')));
    }
}
