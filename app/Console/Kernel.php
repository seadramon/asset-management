<?php

namespace Asset\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Asset\Console\Commands\Inspire::class,
        \Asset\Console\Commands\CustomTest::class,
        \Asset\Console\Commands\PrwRutin::class,
        \Asset\Console\Commands\MsTahunan::class,
        \Asset\Console\Commands\Monitoring::class,
        \Asset\Console\Commands\PrwRutinGenerate::class,
        \Asset\Console\Commands\MonitoringDev::class,
        \Asset\Console\Commands\PrwRutinGenerateDev::class,
        \Asset\Console\Commands\removeDuplicateMonitoring::class,
        \Asset\Console\Commands\RemoveDuplicatePrw::class,
        \Asset\Console\Commands\JadwalPompa::class,
        \Asset\Console\Commands\RemoveMsYear::class,
        \Asset\Console\Commands\KomponenRefresh::class,
        \Asset\Console\Commands\RemoveByKondisi::class,
        \Asset\Console\Commands\DeleteUndisposed::class,
        \Asset\Console\Commands\CloseKpi::class,
        \Asset\Console\Commands\PrwTahunan::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();

        // closing laporan kpi
        $schedule->command('kpi:close')->dailyAt('23:50');

        // Filter komponen dalam kondisi tidak dapat beroperasi
        $schedule->command('komponen:filterkondisi')->weekly()->mondays()->at('01:00');

        // Penyesuaian wo perawatan rutin dan monitoring pada jadwal pompa
        $schedule->command('jadwal:refresh')->weekly()->mondays()->at('01:00');

        // remove duplicate row of ms 4w
        $schedule->command('monitoring:removeduplicate')->weekly()->mondays()->at('02:00');

        // Eksekusi ke database sesuai frekuensi perawatan rutin
        $schedule->command('prw:rutin')
            ->weekly()
            ->mondays()
            ->at('05:00');
    }
}
