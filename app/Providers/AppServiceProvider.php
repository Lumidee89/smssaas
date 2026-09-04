<?php

namespace App\Providers;

use App\Contracts\AiContentProvider;
use App\Contracts\OtpSender;
use App\Contracts\PaymentGateway;
use App\Contracts\PushSender;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\AiGenerationRequest;
use App\Models\Announcement;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\AttendanceRecord;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\CbtQuestion;
use App\Models\Classes;
use App\Models\Conversation;
use App\Models\Expense;
use App\Models\FeeInvoice;
use App\Models\GradingPolicy;
use App\Models\Message;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\Result;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\StaffAttendanceRecord;
use App\Models\Student;
use App\Models\StudentDevelopmentSignal;
use App\Models\StudentIntervention;
use App\Models\StudentSuccessScore;
use App\Models\Subject;
use App\Models\Transcript;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Services\Ai\OpenAiCompatibleProvider;
use App\Services\Notifications\FirebaseCloudMessagingSender;
use App\Services\Otp\BulkSmsLiveOtpSender;
use App\Services\Otp\LogOtpSender;
use App\Services\Payments\PaystackGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpSender::class, fn () => app()->isProduction() ? new BulkSmsLiveOtpSender : new LogOtpSender);
        $this->app->bind(PaymentGateway::class, PaystackGateway::class);
        $this->app->bind(PushSender::class, FirebaseCloudMessagingSender::class);
        $this->app->bind(AiContentProvider::class, OpenAiCompatibleProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('parent-otp', function (Request $request) {
            $phone = preg_replace('/\D+/', '', (string) $request->input('phone'));

            return [
                Limit::perMinute(6)->by('otp-ip:'.$request->ip()),
                Limit::perHour(10)->by('otp-phone:'.$phone),
            ];
        });

        RateLimiter::for('parent-login', fn (Request $request) => [
            Limit::perMinute(10)->by('login-ip:'.$request->ip()),
            Limit::perHour(20)->by('login-phone:'.preg_replace('/\D+/', '', (string) $request->input('phone'))),
        ]);

        RateLimiter::for('payment-webhook', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('cbt-start', fn (Request $request) => Limit::perMinute(10)->by($request->ip().':'.$request->route('exam')));

        foreach ([School::class, User::class, Student::class, Classes::class, Subject::class, Result::class, Payment::class, AcademicYear::class, AcademicTerm::class, AttendanceRecord::class, Announcement::class, Conversation::class, Message::class, FeeInvoice::class, Scholarship::class, Expense::class, PayrollRun::class, GradingPolicy::class, Assessment::class, AssessmentScore::class, Transcript::class, CbtQuestion::class, CbtExam::class, CbtAttempt::class, CbtAnswer::class, StaffAttendanceRecord::class, StudentDevelopmentSignal::class, StudentSuccessScore::class, StudentIntervention::class, AiGenerationRequest::class] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
