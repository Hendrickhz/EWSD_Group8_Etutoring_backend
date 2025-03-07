<?php

namespace App\Console\Commands;

use App\Mail\InactiveStudentNotificationMail;
use App\Mail\InactiveStudentsMail;
use App\Models\StudentTutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyInactiveStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:inactive-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send warning emails to students and tutors if a student is inactive for over 28 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = Carbon::now()->subDays(28);

        $inactiveStudents = User::where('role','student')
        ->where(function ($query) use ($threshold){
            $query->whereNull('last_active_at')
            ->orWhere('last_active_at','<',$threshold);
        })
        ->get();

        $tutorsWithInactiveStudents = [];


        foreach($inactiveStudents as $student){
            $tutor = StudentTutor::where('student_id',$student->id)->first()?->tutor;

            if($tutor){
                Mail::to([$student->email])->send(new InactiveStudentNotificationMail($student,$tutor));
                $tutorsWithInactiveStudents[$tutor->id][] = $student;
            }
        }

        foreach($tutorsWithInactiveStudents as $tutorId=>$students){
            $tutor = User::find($tutorId);
            if($tutor){
                Mail::to($tutor->email)->send(new InactiveStudentsMail($tutor,$students));
            }
        }

        $this->info('Warning emails sent to inactive students and their tutors');
    }
}
