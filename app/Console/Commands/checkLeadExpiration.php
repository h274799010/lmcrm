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

//        $this->info( PayMaster::finishLead( 4 ) );
//
//        return true;

        // todo добавить, только те, которые не завершены и "открытые"
        // ищем просроченные лиды
        $expiredLeads = Lead::Expired()->get();

        // если они есть - обрабатываем
        $expiredLeads->each(function( $lead ){


            // todo этот метод будет завершать лид
//             PayMaster::finishLead( $lead );

//            dd( PayMaster::finishLead( $lead ) );

//                dd( PayMaster::systemInfo() );

            $this->info('Просрочен лид с id ' .$lead->id. ' - ' .$lead->name );


        });

        // todo сделать это на логах или на БД (пока что на логах)
        // если завершенных лидов нет, сообщаем об этом
        if( $expiredLeads->count() == 0 ) $this->info('Просроченных лидов нет');

        return true;
    }
}
