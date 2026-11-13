<?php

use App\Jobs\SelfTrainingPipeline;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Autonomous Self-Training
|--------------------------------------------------------------------------
| Every night the RAG pipelines analyse the previous day of retrieval
| traffic, adjust retrieval weights/confidence and warm unindexed
| pipelines. Runs on the queue so it never blocks a web request.
*/
Schedule::job(new SelfTrainingPipeline)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('rag:self-training');
