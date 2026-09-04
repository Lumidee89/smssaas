<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\Analytics\StudentSuccessCalculator;
use Illuminate\Console\Command;

class CalculateStudentSuccessScores extends Command
{
    protected $signature = 'schoolos:calculate-success-scores {--school=}';

    protected $description = 'Recalculate explainable Student Success Scores';

    public function handle(StudentSuccessCalculator $calculator): int
    {
        $query = Student::query()->when($this->option('school'), fn ($q, $school) => $q->where('school_id', $school));
        $count = 0;
        $query->orderBy('id')->chunkById(200, function ($students) use ($calculator, &$count): void {
            foreach ($students as $student) {
                $calculator->calculate($student);
                $count++;
            }
        });
        $this->info("Calculated {$count} student success scores.");

        return self::SUCCESS;
    }
}
