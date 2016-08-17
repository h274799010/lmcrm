<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTypes extends Model {
    protected $table="credit_types";
    const LEAD_PURCHASE = -1;
    const LEAD_SALE = 2;
    const EXTERNAL_REFILL = 3;
    const MANUAL_CHANGE = 4;
    const LEAD_BAD_INC = 5;
    const LEAD_BAD_DEC = -6;
    const OPERATOR_PAYMENT = -7;
}
