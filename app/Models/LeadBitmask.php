<?php

namespace App\Models;

use App\Models\Bitmask;

class LeadBitmask extends Bitmask
{

    /**
     * Конструктор LeadBitmask
     *
     * выбирает или создает таблицу lead_bitmask_id
     *
     *
     * @param  integer  $id
     * @param  integer  $leadID
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function __construct( $id = NULL, $leadID = NULL, array $attributes = array() )
    {
        $tablePrefix = 'lead_bitmask_';
        $fields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `status` TINYINT(1) DEFAULT 0, `lead_price` FLOAT NULL,`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`))';
//        $fields = '(`id` INT NOT NULL AUTO_INCREMENT, `lead_id` BIGINT NOT NULL, `type` ENUM( \'agent\', \'lead\' ) NOT NULL DEFAULT \'agent\' , `status` TINYINT(1) DEFAULT 0, `lead_price` FLOAT NULL,`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`))';
        parent::__construct( $id, $leadID, $attributes, $tablePrefix, $fields );

        return $this->table;
    }


    /**
     * Связь с таблицей лидов
     *
     * @return object
     */
    public function lead() {
        return $this->hasOne('\App\Models\Lead','id','lead_id');
    }


}
