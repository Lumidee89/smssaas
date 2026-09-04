<?php

namespace App\Http\Controllers;

use App\Models\CbtExam;
use Illuminate\View\View;

class StudentCbtController extends Controller
{
    public function show(CbtExam $exam): View
    {
        abort_unless($exam->status === 'published', 404);
        $exam->load(['school:id,name', 'subject:id,name', 'schoolClass:id,name,section'])->loadCount('questions');

        return view('cbt.student', compact('exam'));
    }
}
