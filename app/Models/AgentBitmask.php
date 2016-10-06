<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentBitmask extends Bitmask
{

    protected $table = NULL;

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
        //$fields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci, `status` TINYINT(1) DEFAULT 0, `lead_price` FLOAT DEFAULT 0, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`))';
        $fields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `status` TINYINT(1) DEFAULT 0, `lead_price` FLOAT DEFAULT 0, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`))';

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




    /**
     * Добавление нового столбца fb к таблице
     *
     * @param  integer  $attr_id
     * @param  integer|array  $opt_id
     *
     * @return object
     */
    public function addFb($attr_id,$opt_id){

        // todo вынести в переменные объекта, чтобы не создавать каждый раз заново
        $leadBitmask = new LeadBitmask($this->tableNum);

        if(is_array($opt_id)) {
            foreach($opt_id as $option) $this->addFb($attr_id, $option);

        } else {
            $this->addAttr($attr_id, $opt_id);
            $leadBitmask->addAttr($attr_id, $opt_id);
        }

    }


    /**
     * Находим агента маски
     *
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getName()
    {
        return $this->hasOne('App\Models\MaskNames', 'id', 'mask_id');
    }
}
