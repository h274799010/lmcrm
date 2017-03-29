<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestPayment extends Model
{
    protected $table = 'requests_payments';

    /**
     * Типы заявки
     */
    const TYPE_REPLENISHMENT = 1; // Заявка на пополнение
    const TYPE_WITHDRAWAL = 2;    // Заявка на снятие

    /**
     * Статусы заявки
     */
    const STATUS_WAITING_PROCESSING = 1; // Ждет обработки
    const STATUS_WAITING_PAYMENT = 2;    // Ждет оплаты
    const STATUS_WAITING_CONFIRMED = 3;  // Оплачено
    const STATUS_CONFIRMED = 4;          // Подтверждено
    const STATUS_REJECTED = 5;           // Отвергнуто

    /**
     * Минимальная сумма для снятия
     */
    const MINIMUM_AMOUNT = 1000;

    /**
     * Пользователь который обработал (обрабатывает) заявку
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function handler()
    {
        return $this->hasOne('App\Models\User', 'id', 'handler_id');
    }

    /**
     * Пользователь который подал заявку
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function initiator()
    {
        return $this->hasOne('App\Models\User', 'id', 'initiator_id');
    }

    /**
     * Получить файлы по заявке
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany('App\Models\CheckRequestPayment', 'request_payment_id', 'id');
    }

    /**
     * Переписка по заявке
     *
     * @return mixed
     */
    public function messages()
    {
        return $this->hasMany('App\Models\Message', 'detail', 'id')->orderBy('created_at');
    }

    /**
     * Возвращает коллекцию с именами типов заявки
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRequestPaymentType()
    {
        return collect(
            [
                self::TYPE_REPLENISHMENT => 'Replenishment',
                self::TYPE_WITHDRAWAL => 'Withdrawal',
                'description' => [
                    self::TYPE_REPLENISHMENT => 'Replenishment description',
                    self::TYPE_WITHDRAWAL => 'Withdrawal description'
                ]
            ]
        );
    }

    /**
     * Возвращает коллекцию со статусом заявки
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRequestPaymentStatus()
    {
        return collect(
            [
                self::STATUS_WAITING_PROCESSING => 'Waiting processing',
                self::STATUS_WAITING_PAYMENT => 'Waiting payment',
                self::STATUS_WAITING_CONFIRMED => 'Waiting confirmed',
                self::STATUS_CONFIRMED => 'Confirmed',
                self::STATUS_REJECTED => 'Rejected',
                'description' => [
                    self::STATUS_WAITING_PROCESSING => 'Waiting processing description',
                    self::STATUS_WAITING_PAYMENT => 'Waiting payment description',
                    self::STATUS_WAITING_CONFIRMED => 'Waiting confirmed description',
                    self::STATUS_CONFIRMED => 'Confirmed description',
                    self::STATUS_REJECTED => 'Rejected description'
                ]
            ]
        );
    }
}
