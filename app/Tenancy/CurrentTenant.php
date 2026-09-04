<?php

namespace App\Tenancy;

use App\Models\School;

class CurrentTenant
{
    public function __construct(public readonly ?School $school = null) {}
    public function id(): ?int { return $this->school?->id; }
    public function resolved(): bool { return $this->school !== null; }
}
