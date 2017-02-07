<?php

namespace App\Console\Commands;


use App\Models\Auction;
use Illuminate\Console\Command;

class SendLeadsToAuction extends Command
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function handle()
    {
        Auction::insert($this->query);
    }
}