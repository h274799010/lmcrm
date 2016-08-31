<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model {
    protected $table="credit_history";


    /**
     * Название ресурса
     *
     * todo доработать, когда переименуется bill_id
     *
     */
    public function sourceName()
    {
        return $this->hasOne('App\Models\CreditTypes', 'id', 'source');
    }


}
/// event on save/update/delete - change Credit


