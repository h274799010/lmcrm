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
     * @return mixed
     */
    public function handle()
    {

        // ищем просроченные лиды
        $expiredLeads = Lead::Expired()->get();

        // если они есть - обрабатываем
        $expiredLeads->each(function( $lead ){

            // метод завершает лид
             PayMaster::finishLead( $lead );
        });

        // todo сделать на логах

        return true;
    }
}
