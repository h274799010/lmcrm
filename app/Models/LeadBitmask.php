<?php

namespace App\Models;

use App\Models\Bitmask;

class LeadBitmask extends Bitmask
{


    // имя таблицы
    protected $table = NULL;

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
        $fields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `status` TINYINT(1) DEFAULT 0, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`))';
        parent::__construct( $id, $leadID, $attributes, $tablePrefix, $fields );

        return $this->table;
    }


    /**
     * Связь с таблицей лидов
     *
     * @return object
     */
    public function lead() {
        return $this->hasOne('\App\Models\Lead','id','user_id');
    }



    /**
     * Установка значения опций атрибута полей "ad_"
     *
     *
     * @param  integer  $attr
     * @param  array|integer  $options
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function setBool( $attr, $options, $lead_id=NULL ){

        // id лида
        $lead_id = ($lead_id) ? $lead_id : $this->userID;

        // если id лида нет - останавливаем метод
        if(!$lead_id){ return false; }

        // опции могут быть заданны как массив значений, либо как одно значение
        // если число имеет тип string преобразовываем в int и помещаем в массив
        if(is_string($options)){
            $options = [intval($options)];
        }

        // если опция заданна как int, делаем из нее массив (для удобства обработки)
        if( is_integer($options) ){
            $options = [$options];
        }

        // массив с индексами полей в качестве ключей
        $optField = array_flip($options);

        // получаем все поля ad_
        $AllAdAttributes = array_keys($this->findAdMask($lead_id));

        $values = []; // массив опций с присвоенными значениями

        // получаем индексы всех опций заданного атрибута
        $AllAdAttributes = collect($AllAdAttributes);
        $AllAdAttributes->each(function( $ad_attr_opt ) use( &$values, $attr, $optField ){
            // перебираем все атрибуты

            // разбиваем имя поля ([ 0=>префикс, 1=>id атрибута , 2=>id опции ])
            $explode = explode('_', $ad_attr_opt);
            $attr_id_db = $explode[1]; // индекс атрибута в БД
            $opt_id_db = $explode[2]; // индекс опции в БД


            // если атрибут поля совпадает с заданным атрибутом
            if($attr_id_db==$attr){

                // установка значения поля в зависимости от наличия опции в заданных параметрах
                // если опция есть - 1
                // если опиции нет - 0
                $values[ 'ad_' .$attr_id_db. '_' .$opt_id_db ] = ( isset($optField[ $opt_id_db ]) ) ? 1 : 0;
            }
        });

        // сохранение значения в БД
        $this->where('user_id','=',$lead_id)->update($values);

        return $this->where('user_id','=',$lead_id)->first();
    }



    /**
     * Обнуляет все поля "ad_"
     *
     * булеан поля ставит в 0
     * другие типы полей в NULL
     *
     * обязательно должен быть указан лид (lead_id)
     * если лид не указан, метод попытается достать его из свойства объекта
     * если не получится - работа метода прекращается
     *
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function resetAllAd( $lead_id=NULL )
    {

        // id лида
        $lead_id = ($lead_id) ? $lead_id : $this->userID;

        // если id лида нет - останавливаем метод
        if(!$lead_id){ return false; }


        // получаем все поля ad_
        $AllAdAttributes = array_keys($this->findAdMask($lead_id));

        $values = []; // массив опций с присвоенными значениями

        // получаем индексы всех опций заданного атрибута
        $AllAdAttributes = collect($AllAdAttributes);
        $AllAdAttributes->each(function( $ad_attr_opt ) use ( &$values ){
            // перебираем все атрибуты

            // разбиваем имя поля ([ 0=>префикс, 1=>id атрибута , 2=>id опции ])
            $explode = explode('_', $ad_attr_opt);
            $opt = $explode[2]; // индекс опции в БД

            // присваиваем полю значение в зависимости от его типа
            if( $opt==0 ){
                // если индекс опции 0
                // значить что это текстовое поле, календарь или пр.
                // этому полю присваиваем значение NULL
                $values[ $ad_attr_opt ] = NULL;

            }else{
                // не 0
                // значить это поле булеан
                // присваиваем ему 0
                $values[ $ad_attr_opt ] = 0;
            }

        });


//        $testMask = $this->where('user_id','=',$lead_id)->first();
//
//        if(!$testMask){
//            $this->tableDB->insertGetId(['user_id'=>$lead_id]);
//        }

        // сохранение значения в БД
        $this->where('user_id','=',$lead_id)->update($values);

        return $this->where('user_id','=',$lead_id)->first();
    }


    /**
     * Установка значения опций атрибута полей "ad_"
     *
     *
     * @param  integer  $attr
     * @param  array|integer  $options
     * @param  string  $type
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function setAd( $attr, $options, $type, $lead_id=NULL ){

        // id лида
        $lead_id = ($lead_id) ? $lead_id : $this->userID;

        // если id лида нет - останавливаем метод
        if(!$lead_id){ return false; }

        $values = []; // массив опций с присвоенными значениями


        // обработка типа checkbox
        if( $type=='checkbox' || $type=='radio' || $type=='select' ){
            // опции атрибута этого типа всегда приходят в массиве

            // опции могут быть заданны как массив значений, либо как одно значение
            // если число имеет тип string преобразовываем в int и помещаем в массив
            if(is_string($options)){
                $options = [intval($options)];
            }

            // если опция заданна как int, делаем из нее массив (для удобства обработки)
            if( is_integer($options) ){
                $options = [$options];
            }

            // массив с индексами полей в качестве ключей
            $optField = array_flip($options);

            // получаем все поля ad_
            $AllAdAttributes = array_keys($this->findAdMask($lead_id));

            // получаем индексы всех опций заданного атрибута
            $AllAdAttributes = collect($AllAdAttributes);
            $AllAdAttributes->each(function( $ad_attr_opt ) use( &$values, $attr, $optField ){
                // перебираем все атрибуты

                // разбиваем имя поля ([ 0=>префикс, 1=>id атрибута , 2=>id опции ])
                $explode = explode('_', $ad_attr_opt);
                $attr_id_db = $explode[1]; // индекс атрибута в БД
                $opt_id_db = $explode[2]; // индекс опции в БД


                // если атрибут поля совпадает с заданным атрибутом
                if($attr_id_db==$attr){

                    // установка значения поля в зависимости от наличия опции в заданных параметрах
                    // если опция есть - 1
                    // если опиции нет - 0
                    $values[ 'ad_' .$attr_id_db. '_' .$opt_id_db ] = ( isset($optField[ $opt_id_db ]) ) ? 1 : 0;
                }
            });

        }elseif( $type=='calendar' ){

            $values[ 'ad_' .$attr. '_0' ] = date("Y-m-d H:i:s", strtotime($options));

        }else{

            $values[ 'ad_' .$attr. '_0' ] = $options;

        }

        // сохранение значения в БД
        $this->where('user_id','=',$lead_id)->update($values);


        return $this->where('user_id','=',$lead_id)->first();
    }


    /**
     * Установка значений опций полей "ad_" через массив поле=>значение
     *
     *
     * @param array $fieldsData
     * @param integer|NULL $lead_id
     *
     * @return LeadBitmask
     */
    public function setAdByFields( $fieldsData, $lead_id=NULL ){

        // получаем запись из маски по id пользователя (либо лида)
        $mask = $this->where('user_id', $lead_id)->first();

        // проверяем наличие записи
        if( !$mask ) {
            // если записи нет

            // создаем новую запись
            $this->tableDB->insertGetId(['user_id'=>$lead_id]);
        }

        // id лида
        $lead_id = ($lead_id) ? $lead_id : $this->userID;

        // если id лида нет - останавливаем метод
        if(!$lead_id){ return false; }

        // сохранение значения в БД
        $this->where('user_id','=',$lead_id)->update( $fieldsData );

        return $this->where('user_id','=',$lead_id)->first();
    }


    /**
     * Получение данных только ad_ полей из маски лидов
     *
     * если задан id лида
     *      - возвращает данные только указанного лида
     *      - если лида с таким индексом нет - вернет пустой массив
     *
     * если параметр не задан
     *      - вернет данные всех лидов в массиве
     *          ключ массива - id лида
     *
     *
     * @param  integer  $leadId
     *
     * @return array
     */
    public function findAdMask( $leadId=NULL ){

        // если в системе уже есть номер лида, он будет подставлен,
        // если лида не указан в параметрах
        $leadId = ($leadId) ? $leadId : ( ($this->userID)?$this->userID:NULL );

        // возвращаемое значение
        // либо маска одного лида
        // либо массив с масками лидов (ключ - id лида)
        $short_mask=array();


        if($leadId){
            // если указан id лида

            // находим маску лида
            $mask = $this->where('user_id', '=', $leadId)->first();

            // перебираем все поля маски лида
            $leadMask = collect($mask);
            $leadMask->each(function( $val, $field ) use( &$short_mask ){

                // выбираем поля с префиксом 'ad_'
                if(stripos($field,'ad_')!==false){
                    // сохраняем значение в массиве (ключ - полное имя поля, значение - значение в поле маски)
                    $short_mask[$field]=$val;
                }
            });

        }else{
            // id лида не указан

            // находим маски всех лидов
            $mask = $this->get();

            // перебираем все маски лидов
            $mask->each(function( $lMask ) use( &$short_mask ){

                // сохраняем id лида в переменную, для удобства
                $leadId = $lMask->user_id;

                // перебираем все поля маски лида
                $leadMask = collect($lMask);
                $leadMask->each(function( $val, $field ) use( $leadId, &$short_mask ){

                    // выбираем поля с префиксом 'ad_'
                    if(stripos($field,'ad_')!==false){
                        // сохраняем значение в массиве с индексом лида
                        // и значениями - поля ad_ с значениями
                        $short_mask[$leadId][$field]=$val;
                    }
                });
            });
        }

        return $short_mask;
    }











}
