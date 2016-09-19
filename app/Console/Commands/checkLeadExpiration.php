<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use App\Helper\PayMaster;
use App\Models\Lead;

class checkLeadExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkLeadExpiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Вернет всех лидов срок которых уже истек и обработает';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

//        $this->info('Метод пока отключен, нужно дописать финиш и проверить в целом');
//
//        exit();



        // ищем просроченные лиды
        $expiredLeads = Lead::Expired()->get();

        // если они есть - обрабатываем
        $expiredLeads->each(function( $lead ){

            // помечаем как завершенные
            $lead->markExpired();
        });

        // ищем просроченность по открытым лидам
        // лиды, которые уже можно завершить
        $expiredOpenLeads = Lead::OpenLeadExpired()->get();

        // если они есть - обрабатываем
        $expiredOpenLeads->each(function( $lead ){

            // помечаем как завершенные
            $lead->finish();
        });

        // todo сделать на логах
    }
}
