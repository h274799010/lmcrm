<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosedDeals extends Model
{

    /**
     * Таблица
     * @var string
     */
    protected $table = "closed_deals";

    /**
     * Поля БД с датой
     *
     */
    protected $dates = ['purchase_date'];

    /**
     * Источник появления лида
     * либо с аукциона лидо передан по группе
     */
    const LEAD_SOURCE_AUCTION = 1;
    const LEAD_SOURCE_GROUP = 2;

    /**
     * Статусы сделок
     *
     */
    const DEAL_STATUS_WAITING = 1;
    const DEAL_STATUS_CONFIRMED = 2;
    const DEAL_STATUS_REJECTED = 3;


    /**
     * Данные открытого лида
     *
     */
    public function openLeads()
    {

        return $this->hasOne('App\Models\OpenLeads', 'id', 'open_lead_id');
    }


    /**
     * Данные пользователя, который совершает сделку
     *
     */
    public function userData()
    {

        return $this
            ->hasOne('App\Models\User', 'id', 'agent_id')
            ->select('id', 'email', 'first_name', 'last_name', 'created_at');
    }


    /**
     * Возвращает массив с источниками по лидам
     *
     * ключ массива = id источника
     *
     */
    public static function getLeadSources()
    {

        // возвращает коллекцию с именами источников лидов
        return collect(
            [
                self::LEAD_SOURCE_AUCTION => 'auction',
                self::LEAD_SOURCE_GROUP => 'group'
            ]
        );
    }


    public static function getDealStatuses()
    {

        // возвращает коллекцию с именами статусов сделок
        return collect(
            [
                self::DEAL_STATUS_WAITING => 'waiting for payment',
                self::DEAL_STATUS_CONFIRMED => 'deal confirmed',
                self::DEAL_STATUS_REJECTED => 'rejected'
            ]
        );

    }
}
