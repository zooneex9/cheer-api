<?php

namespace App\Console;

use App\Models\Coupon;
use App\Models\Article;
use App\Models\ArticleOffer;
use App\Models\Batch;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Batch statuses draft, published, paused, canceled, finished
        $schedule->call(function () {
            $batches = Batch::where('status', 'published')->get();
            foreach($batches as $batch) {
                $current_date = Carbon::now();
                $batch_date = Carbon::parse($batch->end_date);
                // $current_date = Carbon::now()->addMinutes(30);
                // $end_date = Carbon::parse($article->end_date);
                if ($current_date->gt($batch_date)) {
                    $batch->status = 'finished';
                    $batch->save();
                }
            }
        })->everyMinute();

        // Batch statuses draft, published, paused, canceled, finished
        $schedule->call(function () {
            $batches = Batch::where('status', 'draft')->get();
            foreach($batches as $batch) {
                $current_date = Carbon::now();
                $batch_date = Carbon::parse($batch->end_date);
                // $current_date = Carbon::now()->addMinutes(30);
                // $end_date = Carbon::parse($article->end_date);
                if ($current_date->gt($batch_date)) {
                    $batch->status = 'published';
                    $batch->save();
                }
            }
        })->everyMinute();

        // Article statuses draft, published, paused, ended, selled
        // $schedule->call(function () {
        //     $articles = Article::where('status', 'published')->get();
        //     foreach($articles as $article) {
        //         $current_date = Carbon::now();
        //         $article_date = Carbon::parse($article->end_date);
        //         if ($current_date->gt($article_date)) {
        //             $article->status = 'ended';
        //             $article->save();
        //         }
        //     }
        // })->everyMinute();

        // Article statuses draft, published, paused, ended, selled
        $schedule->call(function () {
            $articles = Article::where('status', 'published')->get();
            foreach($articles as $article) {
                $current_date = Carbon::now();
                $last_offer = ArticleOffer::where('article_id', $article->id)->orderBy('amount', 'DESC')->first();
                $last_offer_date = Carbon::parse($article->end_date);

                if ($current_date->gt($last_offer_date) && $current_date->diffInMinutes($last_offer_date) > 5) {
                    $offers = ArticleOffer::where('article_id', $article->id)->orderBy('amount', 'DESC')->get();
                    $count = 1;
                    foreach($offers as $offer) {
                        if ($count == 1) {
                            $offer->status = 'accepted';
                            $details = [
                                'article_name' => $article->name,
                                'payment_info' => $article->payment_info,
                            ];
                            $winner = User::find($offer->user_id);
                            \Mail::to($winner->email)->send(new \App\Mail\ArticlePurchase($details));
                        } else if ($count <= 5) {
                            $offer->status = 'top';
                        } else {
                            $offer->status = 'rejected';
                        }
                        $offer->save();
                        $count = $count + 1;
                    }
                    $article->status = 'selled';
                    $article->save();
                }
            }
        })->everyMinute();

        // $schedule->call(function () {
        //     $transactions = Transaction::where('status', 1)->get();
        //     foreach($transactions as $transaction) {
        //         $current_date = Carbon::now();
        //         $transaction_date = Carbon::parse($transaction->created_at);
        //         switch ($transaction->expiration) {
        //             case '1 year':
        //                 # code...
        //                 $transaction_date_end = $transaction_date->addYear();
        //                 break;
        //             case '1 month':
        //                 # code...
        //                 $transaction_date_end = $transaction_date->addMonth();
        //                 break;
        //             case '2 months':
        //                 # code...
        //                 $transaction_date_end = $transaction_date->addMonths(2);
        //                 break;
        //         }
        //         if ($current_date->gt($transaction_date_end)) {
        //             $transaction->status = 0;
        //             $transaction->save();
        //         }
        //     }
        // })->daily();

        $schedule->call(function () {
            $coupons = Coupon::where('status', 0)->get();
            foreach($coupons as $coupon) {
                $current_date = Carbon::now();
                $coupon_date = Carbon::parse($coupon->created_at);
                switch ($coupon->expiration) {
                    case '1 year':
                        # code...
                        $coupon_date_end = $coupon_date->addYear();
                        break;
                    case '1 month':
                        # code...
                        $coupon_date_end = $coupon_date->addMonth();
                        break;
                    
                    default:
                        # code...
                        $coupon_date_end = $coupon_date->addWeek();
                        break;
                }
                if ($current_date->gt($coupon_date_end)) {
                    $coupon->status = 1;
                    $coupon->save();
                }
            }
        })->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
