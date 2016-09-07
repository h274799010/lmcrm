<?php

namespace App\Console\Commands;

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

//        dd( Lead::find(100) );
//        dd( Lead::Expired()->get() );

        $expiredLeads = Lead::Expired()->get();

        if( $expiredLeads ){

            $expiredLeads->each(function( $lead ){


                // todo метод закрытия лида
//                PayMaster::closeLead( $lead );

                dd( PayMaster::systemInfo() );


//                $this->info( $lead->id );

            });

        }

        return true;

    }
}
