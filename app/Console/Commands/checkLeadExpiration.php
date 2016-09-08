<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use App\Helper\PayMaster;
use App\Models\Lead;
use App\Lmcrm\Lead as L;

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

        L::seeder( 1, 9 );

        return true;



        $this->info('Ok');

        return true;

        $expiredLeads = Lead::Expired()->get();

        if( $expiredLeads ){

            $expiredLeads->each(function( $lead ){


//                dd( PayMaster::leadInfo( 16 ) );
//                dd( PayMaster::leadBuyers( 11 ) );


                // todo метод закрытия лида
//                PayMaster::finishLead( $lead );

                dd( PayMaster::finishLead( $lead ) );

//                dd( PayMaster::systemInfo() );


            });

        }

        return true;

    }
}
