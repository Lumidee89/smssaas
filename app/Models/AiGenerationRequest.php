<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGenerationRequest extends Model
{
    protected $fillable = ['school_id', 'requested_by', 'type', 'status', 'input', 'output', 'provider', 'model', 'input_tokens', 'output_tokens', 'error', 'completed_at'];

    protected $casts = ['input' => 'array', 'output' => 'array', 'completed_at' => 'datetime'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
