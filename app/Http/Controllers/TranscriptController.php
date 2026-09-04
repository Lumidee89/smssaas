<?php

namespace App\Http\Controllers;

use App\Models\Transcript;
use App\Services\Academics\TranscriptQrCode;

class TranscriptController extends Controller
{
    public function verify(string $publicId, TranscriptQrCode $qr)
    {
        $transcript = Transcript::where('public_id', $publicId)->with(['student.school', 'policy'])->firstOrFail();

        $qrCode = $qr->svg(route('transcripts.verify', $transcript->public_id));
        return view('transcripts.verify', compact('transcript', 'qrCode'));
    }

    public function show(Transcript $transcript, TranscriptQrCode $qr)
    {
        abort_unless(auth()->user()->role === 'super_admin' || $transcript->school_id === auth()->user()->school_id, 403);
        $transcript->load(['student.school', 'policy']);

        $qrCode = $qr->svg(route('transcripts.verify', $transcript->public_id));
        return view('transcripts.show', compact('transcript', 'qrCode'));
    }
}
