<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentBitmask extends Bitmask
{
    /**
     * Конструктор AgentBitmask
     *
     * выбирает или создает таблицу lead_bitmask_id
     *
     *
     * @param  integer  $id
     * @param  integer  $agentID
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function __construct( $id = NULL, $agentID = NULL, array $attributes = array() )
    {
        $tablePrefix = 'agent_bitmask_';
        $fields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `status` TINYINT(1) DEFAULT 0, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`))';

        parent::__construct( $id, $agentID, $attributes, $tablePrefix, $fields );

        return $this->table;
    }



    /**
     * Связь с таблицей агентов
     *
     * @return object
     */
    public function agent() {
        return $this->hasOne('\App\Models\Agent','id','user_id');
    }


}
