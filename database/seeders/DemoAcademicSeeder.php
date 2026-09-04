<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAcademicSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::updateOrCreate(
            ['subdomain' => 'demo-academy'],
            ['name' => 'SchoolOS Demo Academy', 'institution_type' => 'secondary', 'email' => 'school@demo.schoolos.test', 'phone' => '+2348000000000', 'address' => '1 Learning Avenue, Lagos', 'theme_color' => '#06322C', 'currency' => 'NGN', 'timezone' => 'Africa/Lagos', 'is_active' => true, 'subscription_end_date' => now()->addYear()],
        );

        User::updateOrCreate(
            ['email' => 'demo.admin@schoolos.test'],
            ['school_id' => $school->id, 'name' => 'Demo School Admin', 'phone' => '+2348000000001', 'role' => 'school_admin', 'password' => Hash::make('password123'), 'email_verified_at' => now()],
        );

        $teachers = collect([
            ['email' => 'ada.teacher@schoolos.test', 'name' => 'Ada Okafor', 'phone' => '+2348000000011'],
            ['email' => 'tunde.teacher@schoolos.test', 'name' => 'Tunde Balogun', 'phone' => '+2348000000012'],
        ])->map(fn (array $data) => User::updateOrCreate(
            ['email' => $data['email']],
            [...$data, 'school_id' => $school->id, 'role' => 'teacher', 'password' => Hash::make('password123'), 'email_verified_at' => now()],
        ));

        $subjects = collect([
            ['code' => 'MTH101', 'name' => 'Mathematics', 'credit_hours' => 4, 'description' => 'Core mathematics and problem solving.'],
            ['code' => 'ENG101', 'name' => 'English Language', 'credit_hours' => 4, 'description' => 'Reading, writing, grammar, and communication.'],
        ])->map(fn (array $data) => Subject::updateOrCreate(
            ['school_id' => $school->id, 'code' => $data['code']],
            $data,
        ));

        $classes = collect([
            ['name' => 'Junior Secondary 1', 'section' => 'A', 'capacity' => 30, 'teacher_id' => $teachers[0]->id],
            ['name' => 'Junior Secondary 2', 'section' => 'A', 'capacity' => 30, 'teacher_id' => $teachers[1]->id],
        ])->map(fn (array $data) => Classes::updateOrCreate(
            ['school_id' => $school->id, 'name' => $data['name'], 'section' => $data['section']],
            $data,
        ));

        $teachers[0]->taughtSubjects()->syncWithoutDetaching([$subjects[0]->id]);
        $teachers[1]->taughtSubjects()->syncWithoutDetaching([$subjects[1]->id]);
        foreach ($classes as $class) {
            $class->subjects()->syncWithoutDetaching($subjects->pluck('id')->all());
        }

        collect([
            ['admission_number' => 'DEMO-2026-001', 'first_name' => 'Chiamaka', 'last_name' => 'Nwosu', 'email' => 'chiamaka.student@schoolos.test', 'phone' => '+2348000000021', 'date_of_birth' => '2013-04-12', 'gender' => 'female', 'address' => 'Lagos', 'class_id' => $classes[0]->id],
            ['admission_number' => 'DEMO-2026-002', 'first_name' => 'David', 'last_name' => 'Adeyemi', 'email' => 'david.student@schoolos.test', 'phone' => '+2348000000022', 'date_of_birth' => '2012-09-08', 'gender' => 'male', 'address' => 'Lagos', 'class_id' => $classes[1]->id],
        ])->each(fn (array $data) => Student::updateOrCreate(
            ['admission_number' => $data['admission_number']],
            [...$data, 'school_id' => $school->id],
        ));
    }
}
