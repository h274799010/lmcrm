<?php

namespace App\Models;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use DB;

class Bitmask extends Model
{

    // префикс таблицы
    protected $tablePrefix = NULL;

    // Номер таблицы (id сферы)
    protected $tableNum = NULL;

    // имя таблицы
    protected $table = NULL;

    // поля создаваемой таблицы
    protected $tableFields = NULL;

    // индекс пользователя
    protected $userID = NULL;

    // переменная с данными конструктора запросов
    public $tableDB = NULL;

    // отключаем метки времени
    public $timestamps = false;

    public $fillable = ['name'];

    /**
     * Выбирает таблицу DB
     *
     * если таблицы с таким индексом нет - создает ее
     *
     *
     * @param  integer  $id
     * @param  integer  $userID
     * @param  array  $attributes
     * @param  string  $tablePrefix
     * @param  string  $tableFields
     *
     * @return mixed
     */
    public function __construct($id = NULL, $userID = NULL, array $attributes = array(), $tablePrefix = NULL, $tableFields = NULL)
    {
        // индекс таблицы
        $this->tableNum = $id;

        // префикс таблицы
        $this->tablePrefix = $tablePrefix;

        // название таблицы
        $this->table = $this->tablePrefix .(int)$id;

        // поля таблицы
        $this->tableFields = $tableFields;

        // сохраняем данные конструктора запросов (на будущее удалить)
        $this->tableDB = DB::table($this->table);

        // если задан пользователь, сохраняем его id в переменную
        if($userID) { $this->userID=$userID; }

        parent::__construct($attributes);

        return $this->table;
    }


    /**
     * Возвращает маску по id
     *
     *
     * @param  integer  $id
     *
     * @return object
     */
    public function find( $id ){

        // выбираем данные маски по id
        $mask = $this->where('id', '=', $id)->first();

        if( $mask ){

            // возвращаем имя таблицы
            $mask->table = $this->table;

            // возвращаем индекс пользователя
            $mask->userID = $this->userID;

            // возвращаем конструктор запросов
            $mask->tableDB = $this->tableDB;
        }

        return $mask;
    }


    /**
     * Создание таблиц
     * agent_bitmask_*
     * lead_bitmask_*
     *
     * @param $id
     */
    public static function createTables($id)
    {
        $agentBitmaskTable = 'agent_bitmask_'.$id;
        $agentBitmaskFields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `status` TINYINT(1) DEFAULT 0, `lead_price` FLOAT DEFAULT 0, `region_index` BIGINT NULL DEFAULT NULL, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `deleted_at` TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (`id`))';

        if ( $id && !DB::getSchemaBuilder()->hasTable( $agentBitmaskTable ) ) {
            DB::statement('CREATE TABLE IF NOT EXISTS `' . $agentBitmaskTable .'`' .$agentBitmaskFields , []);
        }

        $leadBitmaskTable = 'lead_bitmask_'.$id;
        $leadBitmaskFields = '(`id` INT NOT NULL AUTO_INCREMENT, `user_id` BIGINT NOT NULL, `status` TINYINT(1) DEFAULT 0, `region_index` BIGINT NULL DEFAULT NULL, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `deleted_at` TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (`id`))';

        if ( $id && !DB::getSchemaBuilder()->hasTable( $leadBitmaskTable ) ) {
            DB::statement('CREATE TABLE IF NOT EXISTS `' . $leadBitmaskTable .'`' .$leadBitmaskFields , []);
        }
    }


    /**
     * Создание новой таблицы
     *
     * @return void
     */
    protected function createTable()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS `' . $this->table .'`' .$this->tableFields , []);
//        DB::statement('ALTER TABLE `'.$this->table.'` ADD UNIQUE (`user_id`)');
    }


    /**
     * Смена таблицы
     *
     * @param  integer  $id
     *
     * @return object
     */
    public function changeTable($id){
        $this->table = $this->tablePrefix .(int)$id;
        $this->tableDB = DB::table($this->table);
        return $this->tableDB;
    }


    /**
     * Возвращает конструктор запросов
     *
     * @return object
     */
    public function query_builder(){
        return $this->tableDB;
    }


    /**
     * Возвращает имя таблицы
     *
     * @return object
     */
    public function getTableName(){
        return $this->table;
    }

    /**
     * Возвращает номер таблицы
     *
     * @return integer
     */
    public function getTableNum(){
        return $this->tableNum;
    }


    /**
     * Меняет id пользователя
     * (или устанавливает, если его нет)
     *
     * @return true
     */
    public function setUserID($user_id){
        $this->userID = (int)$user_id;
        return true;
    }


    /**
     * Возвращает данные таблицы (строку) по заданному пользователю
     *
     * был похожий метод getPrice()
     * был похожий метод findMask()
     *
     * @param  integer  $user_id
     *
     * @return object
     */
    // todo $model->status='1'; $model->save();
    public function getStatus($user_id=NULL){ //->status
        $user_id = ( $user_id ) ? $user_id : $this->userID;
        return $this->tableDB->where('user_id','=',$user_id);
    }


    /**
     * Возвращает данные таблицы (строку) по заданному пользователю
     *
     * аналог getStatus() только с более адекватным названием
     *
     * @param  integer  $user_id
     *
     * @return object
     */
    public function getData($user_id=NULL){
        $user_id = ( $user_id ) ? $user_id : $this->userID;

        return $this->where('user_id','=',$user_id);
    }


    /**
     * Устанавливает статус пользователю
     *
     * @param  integer  $status
     * @param  integer  $user_id
     *
     * @return object
     */
    public function setStatus($status=0,$user_id=NULL){
        $user_id = ($user_id)?$user_id:$this->userID;
        return $this->tableDB->where('user_id','=',$user_id)->update(['status'=>$status]);
    }


    /**
     * Установить цену
     *
     * @param  integer  $val
     * @param  integer  $user_id
     *
     * @return object
     */
    public function setPrice($val=0,$user_id=NULL){
        $user_id = ($user_id)?$user_id:$this->userID;
        return $this->tableDB->where('user_id','=',$user_id)->update(['lead_price'=>$val]);
    }


    /**
     * Получение короткой маски
     *
     * возвращает только значения полей маски
     *
     * @param  integer  $user_id
     *
     * @return array
     */
    public function findShortMask($user_id=NULL){

        $user_id = ($user_id) ? $user_id : $this->userID;
        $short_mask=array();

        // строка по id пользователя
        $mask = $this->tableDB->where( 'user_id','=',$user_id )->first();

        // если пользователя нет в таблице - возвращается пустая строка
        if(!$mask) { return $short_mask; }

        // возвращает свойства объекта в массив
        $mask=get_object_vars($mask);

        // перебираем массив, выбирая только нужные данные
        foreach($mask as $field=>$val){

            if(stripos($field,'fb_')!==false){
                $short_mask[preg_replace('/^fb_[\d]+_/','',$field)]=$val;
            }
        }

        return $short_mask;
    }


    /**
     * Получение короткой маски
     * похожа на findShortMask()
     * только не делает запрос к бд, а обрабатывает уже полученные данные
     *
     * возвращает только значения полей маски
     *
     * todo доработать
     *
     * @return array
     */
    public function findShortMaskById(){

        // массив короткой маски
        $short_mask=array();

        // преобразовываем данные в массив
        $mask=$this->toArray();

        // перебираем массив, выбирая только нужные данные
        foreach($mask as $field=>$val){

            if(stripos($field,'fb_')!==false){
                $short_mask[preg_replace('/^fb_[\d]+_/','',$field)]=$val;
            }
        }

        return $short_mask;
    }




    /**
     * Получение короткой маски
     *
     * возвращает только значения полей маски
     *
     * todo доработать
     *
     * @param  integer  $user_id
     *
     * @return array
     */
    public function findFieldsMask($user_id=NULL){

        $user_id = ($user_id) ? $user_id : $this->userID;
        $short_mask=array();

        // строка по id пользователя
        $mask = $this->tableDB->where( 'user_id','=',$user_id )->first();

        // если пользователя нет в таблице - возвращается пустая строка
        if(!$mask) { return $short_mask; }

        // возвращает свойства объекта в массив
        $mask=get_object_vars($mask);

        // перебираем массив, выбирая только нужные данные
        foreach($mask as $field=>$val){

            if(stripos($field,'fb_')!==false){
                $short_mask[$field]=$val;
            }
        }

        return $short_mask;
    }


    /**
     * Получение массива с данными полей фильтра
     *
     * возвращает только значения полей фильтра
     *
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFilterFields(){

        // возвращает свойства объекта в массив
        $mask = collect( $this->toArray() );

        // фильтруем поля маски
        $mask = $mask->filter(function( $item, $key ){
            // возвращаем только поля фильтра
            return stripos($key,'fb_') === 0;
        });

        return $mask;
    }


    /**
     * Получение массива с данными полей дополнительных данных данных
     *
     * возвращает только значения полей дополнительных данных
     *
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAdditionalFields(){

        // возвращает свойства объекта в массив
        $mask = collect( $this->toArray() );

        // фильтруем поля маски
        $mask = $mask->filter(function( $item, $key ){
            // возвращаем только поля фильтра
            return stripos($key,'ad_') === 0;
        });

        return $mask;
    }


    /**
     * Получение данных только fb_ полей из маски лидов
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
    public function findFbMask( $leadId=NULL ){

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
                if(stripos($field,'fb_')!==false){
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
                    if(stripos($field,'fb_')!==false){
                        // сохраняем значение в массиве с индексом лида
                        // и значениями - поля ad_ с значениями
                        $short_mask[$leadId][$field]=$val;
                    }
                });
            });
        }

        return $short_mask;
    }


    /**
     * Получение данных только fb_ полей из маски лидов
     *
     * аналог метода findFbMask() только не делает запрос к БД
     *
     *
     * @return array
     */
    public function findFbMaskById(){

        // возвращаемое значение
        // либо маска одного лида
        // либо массив с масками лидов (ключ - id лида)
        $short_mask=array();

            // перебираем все поля маски лида
            $leadMask = collect($this->attributes);
            $leadMask->each(function( $val, $field ) use( &$short_mask ){

                // выбираем поля с префиксом 'ad_'
                if(stripos($field,'fb_')!==false){
                    // сохраняем значение в массиве (ключ - полное имя поля, значение - значение в поле маски)
                    $short_mask[$field]=$val;
                }
            });

        return $short_mask;
    }


    /**
     * Фильтрация полей fb
     *
     * Получает все данные из таблицы маски
     * через фильтр
     *
     * задаваемый параметр это просто массив (ключ=>значение, 'fb_34_2'=>0)
     *
     * ПРИМЕР ФИЛЬТРАЦИИ:
     * если в качестве данных для выборки
     * задан Agent:
     *
     *    Agent       Lead
     *      0    <->   0    - выбираем
     *      1    <->   1    - выбираем
     *      1    <->   0    - выбираем
     *      0    <->   1    - Пропускаем
     *
     *
     * @param  array  $filter
     *
     * @return object
     *
     *
     * todo это фильтр агента, чтобы найти лида
     */
    public function filterByMask( $filter ){

        // выборка полей по маске
        $list = $this->where(function( $query ) use ( $filter ){

            // выборка по всем полям fb_
            foreach( $filter as $field=>$value ){
                $query->where( $field, '<=', $value );
            }
        });

        return $list;
    }

    /**
     * Фильтрация полей fb
     *
     * Получает все данные из таблицы маски
     * через фильтр
     *
     * задаваемый параметр это просто массив (ключ=>значение, 'fb_34_2'=>0)
     *
     * ПРИМЕР ФИЛЬТРАЦИИ:
     * если в качестве данных для выборки
     * задан Agent:
     *
     *    Agent       Lead
     *      0    <->   0    - выбираем
     *      1    <->   1    - выбираем
     *      1    <->   0    - выбираем
     *      0    <->   1    - Пропускаем
     *
     *
     * @param  array  $filter
     * @param  integer  $author
     *
     * @return object
     *
     *
     * todo это фильтр лида, чтобы найти агентов
     */
    public function filterAgentsByMask( $filter, $author=NULL, $sphere_id = NULL, $lead_id = NULL, $range = 1 ){

        // выборка полей по маске
        $list = $this->where(function( $query ) use ( $filter, $author, $sphere_id, $range, $lead_id ){

            // todo доработать
            if($author){

                // массив id пользователей, по которым нужно исключить выбор лидов
                $excludedUsers = User::excludedUsers($author);

                if($sphere_id) {
                    $excludedAgents = AgentSphere::where('sphere_id', '=' , $sphere_id)
                        ->where('agent_range', '>', $range)->get()->lists('agent_id')->toArray();

                    $excludedUsers = array_merge($excludedUsers, $excludedAgents);
                }
                if($lead_id) {
                    $excludedAgents = Auction::where('lead_id', '=', $lead_id)
                        ->get()
                        ->lists('user_id')
                        ->toArray();

                    $excludedUsers = array_merge($excludedUsers, $excludedAgents);
                }

                //$query->where( 'user_id', '<>', $author );
                $query->whereNotIn( 'user_id', $excludedUsers ); // без лидов, которых занес агент и его продавцы
            }

            // выборка по всем полям fb_
            foreach( $filter as $field=>$value ){

                $query->where( $field, '>=', $value );
            }
        });

        return $list;
    }




    /**
     * Поиск маски пользователя по заданному индексу таблицы
     *
     *
     * @param  integer  $sphere_id
     * @param  integer  $user_id
     *
     * @return object
     */
    public function findSphereMask($sphere_id,$user_id=NULL){
        $user_id = ($user_id) ? $user_id : $this->userID;
        $this->changeTable($sphere_id);
        return $this->getStatus($user_id);
    }


    /**
     * Установка значения опций атрибута
     *
     *
     * под $user_id может быть либо лид,
     * либо пользователь
     *
     * @param  string  $opt_index
     * @param  integer  $user_id
     *
     * @return object
     */
    public function setAttr($opt_index,$user_id=NULL){

        // выбираем пользователя, либо заданного, либо того, который уже есть в модели
        // либо, это лид
        $user_id = ($user_id) ? $user_id : $this->userID;

        // поверка данных
        if (is_array($opt_index)) {
            // если не массив, прекращаем работу

            // переменная с данными для записи
            $values = [];

            // получаем запись из маски по id пользователя (либо лида)
            $mask = $this->tableDB->where('user_id','=',$user_id)->first();

            // проверяем наличие записи
            if( $mask ) {
                // если запись есть

                // вставляем в данные id этой записи
                $values['id']=$mask->id;

            } else {
                // если записи нет

                // создаем и выбираем id этой новой записи
                $values['id'] = $this->tableDB->insertGetId(['user_id'=>$user_id]);
            }

            // получаем массив атрибутов
            // вида fb_attr_opt
            $attributes = $this->attributesAssoc();

            // перебираем все поля атрибутов и проставляем значение
            foreach($attributes as $field=>$index) {
                // если поле заданно на фронтенде записываем его как 1
                // если нет - проставляем значение 0
                $values[$field]=(in_array($index,$opt_index))?1:0;
            }

            $this->tableDB->update($values);
        }
        return $this->tableDB;
    }


    /**
     * Преобразовывает массив атрибутов и опций в массив с ключами
     *
     * @param  array  $rawArray
     *
     * @return array
     */
    public function prepareOptions( $rawArray ){

        // массив с подготовленными данными
        $prepareArray = [];

        // перебираем все атрибуты
        $rawArray = collect($rawArray);
        $rawArray->each(function( $options, $attr ) use ( &$prepareArray ){
            // перебираем все опции атрибута
            $options = collect($options);
            $options->each(function( $option ) use ( $attr, &$prepareArray ){

                // собираем имя поля fb_
                $field = 'fb_' .$attr .'_' .$option;
                // сохраняем поле в массив как ключ
                $prepareArray[ $field ] = 1;
            });
        });

        // возвращаем подготовленный массив
        return $prepareArray;
    }


    /**
     * Установка значения опций атрибута через поля fb_attr_opt
     *
     *
     * под $user_id может быть либо лид,
     * либо пользователь
     *
     * @param  string  $fields
     * @param  integer  $user_id
     *
     * @return object
     */
    public function setFilterOptions( $fields, $user_id=NULL){

        // выбираем пользователя, либо заданного, либо того, который уже есть в модели
        // либо, это лид
        $user_id = ($user_id) ? $user_id : $this->userID;

        // поверка данных
        if (is_array($fields)) {
            // если не массив, прекращаем работу

            // переменная с данными для записи
            $values = [];

            // получаем запись из маски по id пользователя (либо лида)
            $mask = $this->tableDB->where('user_id','=',$user_id)->first();

            // проверяем наличие записи
            if( $mask ) {
                // если запись есть

                // вставляем в данные id этой записи
                $values['id']=$mask->id;

            } else {
                // если записи нет

                // создаем и выбираем id этой новой записи
                $values['id'] = $this->tableDB->insertGetId(['user_id'=>$user_id]);
            }

            // получаем массив атрибутов
            // вида fb_attr_opt
            $attributes = $this->attributesAssoc();

            // преобразовываем поля в коллекцию
            $fields = collect($fields);

            // перебираем все поля атрибутов и проставляем значение
            foreach($attributes as $key => $val) {
                // если поле заданно на фронтенде записываем его как 1
                // если нет - проставляем значение 0
                $values[$key]=( $fields->has($key) ) ? 1 : 0 ;
            }

            // сохраняем
            $this->tableDB->update($values);
        }

        return $this->tableDB;
    }


    /**
     * Установка значений опций полей "ad_" через массив поле=>значение
     *
     *
     * @param array $fieldsData
     * @param integer|NULL $lead_id
     *
     * @return Bitmask
     */
    public function setFbByFields( $fieldsData, $lead_id=NULL ){

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
     * Установка значения опция атрибута по id маски
     * аналог setAttr() только в отличии от него не делает
     * запрос к бд, а работает с уже готовыми данными
     *
     *
     * @param  string  $opt_index
     *
     * @return object
     */
    public function setAttrById($opt_index){

        if (is_array($opt_index)) {

            $attributes = $this->attributesAssoc();
            foreach($attributes as $field=>$index) {
                $this->$field = (in_array($index,$opt_index))?1:0;
            }

            $this->save();
        }

        return $this;
    }


    /**
     * Обнуляет все поля "fb_"
     *
     * обнуление всех полей с префиксом fb_
     *
     * ставит все поля fb_ в 0
     *
     *
     * @return object
     */
    public function resetAllFb()
    {

        // получаем все поля fd_
        $AllAdAttributes = $this->findFbMaskById();

        // массив опций с присвоенными значениями
        $values = [];

        // получаем индексы всех опций заданного атрибута
        $AllAdAttributes = collect($AllAdAttributes);

        // перебираем все поля и устанавливаем в 0
        $AllAdAttributes->each(function( $val, $key ) use( &$values ){

            $this->$key = 0;
        });

        // сохраняем поля в БД
        $this->save();

        return $this;
    }







    /* ---------------------------------- Table structure ---------------------------------- */


    /**
     * Возвращает все поля таблицы
     *
     * todo переименовать $this->attributes['']
     *
     */
    public function attributes() {
        return DB::getSchemaBuilder()->getColumnListing($this->table);
    }


    /**
     * Возвращает ассоциативный массив с именами полей
     *
     */
    public function attributesAssoc() {
        $attributes = DB::getSchemaBuilder()->getColumnListing($this->table);
        $indexes= array();
        foreach($attributes as $field){
            if(stripos($field,'fb_')!==false){
                $indexes[$field]=preg_replace('/^fb_[\d]+_/','',$field);
            }
        }
        return $indexes;
    }


    /**
     * Добавляет к таблице новый столбец (атрибут)
     *
     *
     * @param  integer  $group_index
     * @param  integer  $opt_index
     *
     * @return object
     */
    public function addAttr($group_index,$opt_index){
        if(is_array($opt_index)) {
            foreach($opt_index as $aVal) $this->addAttr($group_index,$aVal);
        } else {
            $index = implode('_', ['fb', $group_index, $opt_index]);
            if (!in_array($index, $this->attributes())) {
                DB::statement('ALTER TABLE `' . $this->table . '` ADD COLUMN `' . $index . '` TINYINT(1) NULL', []);
            }
        }
        return $this->tableDB;
    }


    /**
     * Добавляет к таблице новый столбец c заданным типом
     *
     * По умолчанию установлен тип varchar
     *
     * можно указывать как сами типы так и имена полей
     * имена полей легко можно менять и тобавлять, как и их типы
     *
     *
     *
     * @param  integer  $group_index
     * @param  integer|array  $opt_index
     * @param  string  $field
     * @param  integer  $default
     *
     * @return object
     */
    public function addAttrWithType($group_index,$opt_index, $field='varchar', $default=0){

        // все типы полей которые могут понадобится
        $fieldsType =
            [
                'boolean' => 'TINYINT(1)',
                'varchar' => 'VARCHAR(255)',
                'data' => 'DATE',
                'text' => 'TEXT',
            ];


        // названия полей и какие им типы, которые им соответствуют
        $fieldsParameter =
            [
                'boolean' => $fieldsType['boolean'],
                'varchar' => $fieldsType['varchar'],
                'data' => $fieldsType['data'],
                'text' => $fieldsType['text'],

                'radio' => $fieldsType['boolean'],
                'checkbox' => $fieldsType['boolean'],
                'calendar' => $fieldsType['data'],
                'email' => $fieldsType['varchar'],
                'input' => $fieldsType['varchar'],
                'dropdown' => $fieldsType['varchar'],
                'select' => $fieldsType['varchar'],
                'textarea' => $fieldsType['text'],
            ];


        // создание поля с заданным типом
        if(is_array($opt_index)) {
            foreach($opt_index as $aVal) $this->addAttrWithType($group_index,$aVal, $field);
        } else {
            $index = implode('_', ['fb', $group_index, $opt_index]);
            if (!in_array($index, $this->attributes())) {
                DB::statement('ALTER TABLE `' . $this->table . '` ADD COLUMN `' . $index . '` ' .$fieldsParameter[$field] .' DEFAULT ' .$default .' NOT NULL', []);
            }
        }
        return $this->tableDB;
    }

    /**
     * Добавляет к таблице новый столбец "additional data" ( ad_ ) c заданным типом
     *
     * По умолчанию установлен тип varchar
     *
     * можно указывать как сами типы так и имена полей
     * имена полей легко можно менять и тобавлять, как и их типы
     *
     *
     *
     * @param  integer  $group_index
     * @param  integer|array  $opt_index
     * @param  string  $field
     *
     * @return object
     */
    public function addAb($group_index,$opt_index, $field='varchar')
    {

        // все типы полей которые могут понадобится
        $fieldsType =
            [
                'boolean' => 'TINYINT(1)',
                'varchar' => 'VARCHAR(255)',
                'data' => 'DATE',
                'text' => 'TEXT',
            ];


        // названия полей и какие им типы, которые им соответствуют
        $fieldsParameter =
            [
                'boolean' => $fieldsType['boolean'],
                'varchar' => $fieldsType['varchar'],
                'data' => $fieldsType['data'],
                'text' => $fieldsType['text'],

                'radio' => $fieldsType['boolean'],
                'checkbox' => $fieldsType['boolean'],
                'calendar' => $fieldsType['data'],
                'email' => $fieldsType['varchar'],
                'input' => $fieldsType['varchar'],
                'dropdown' => $fieldsType['varchar'],
                'select' => $fieldsType['varchar'],
                'textarea' => $fieldsType['text'],
            ];


        // создание поля с заданным типом
        if (is_array($opt_index)) {
            foreach ($opt_index as $aVal) $this->addAttrWithType($group_index, $aVal, $field);
        } else {
            $index = implode('_', ['ad', $group_index, $opt_index]);
            if (!in_array($index, $this->attributes())) {
                DB::statement('ALTER TABLE `' . $this->table . '` ADD COLUMN `' . $index . '` ' . $fieldsParameter[$field] . ' NULL', []);
            }
        }

        return $this->tableDB;
    }


    /**
     * Удаляет столбец таблицы (атрибут)
     *
     *
     * @param  integer|array  $group_index
     * @param  integer|array  $opt_index
     *
     * @return object
     */
    public function removeAttr($group_index,$opt_index){
        if(is_array($group_index) && $opt_index==null) {
            foreach($group_index as $item) {
                $delAttr = preg_grep("/^fb_" . $item . "_.*/", $this->attributes());
                foreach($delAttr as $item) {
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '`', []);
                }
            }
        } elseif(is_numeric($group_index) && $opt_index==null){
            $delAttr = preg_grep("/^fb_" . $group_index . "_.*/", $this->attributes());
            foreach($delAttr as $item) {
                DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '`', []);
            }
        }else {
            if (is_array($opt_index)) {
                foreach ($opt_index as $aVal) $this->removeAttr($group_index, $aVal);
            } else {
                $index = implode('_', ['fb', $group_index, $opt_index]);
                if (in_array($index, $this->attributes())) {
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $index . '`', []);
                }
            }
        }
        return $this->tableDB;
    }




    /**
     * Удаляет столбец таблицы c типом "ad_"
     *
     *
     * @param  integer|array  $group_index
     * @param  integer|array  $opt_index
     *
     * @return object
     */
    public function removeAd($group_index,$opt_index){
        if(is_array($group_index) && $opt_index==null) {
            foreach($group_index as $item) {
                $delAttr = preg_grep("/^ad_" . $item . "_.*/", $this->attributes());
                foreach($delAttr as $item) {
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '`', []);
                }
            }
        } elseif(is_numeric($group_index) && $opt_index==null){
            $delAttr = preg_grep("/^ad_" . $group_index . "_.*/", $this->attributes());
            foreach($delAttr as $item) {
                DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '`', []);
            }
        }else {
            if (is_array($opt_index)) {
                foreach ($opt_index as $aVal) $this->removeAd($group_index, $aVal);
            } else {
                $index = implode('_', ['ad', $group_index, $opt_index]);
                if (in_array($index, $this->attributes())) {
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $index . '`', []);
                }
            }
        }
        return $this->tableDB;
    }



    /**
     * todo разобраться
     *
     * устанавливает поля в положение 1
     * не понял назначение метода
     *
     * @param integer $index
     * @param array $hash
     * @param boolean $force
     *
     * @return object
     */
    public function setDefault($index=0, $hash=NULL, $force=NULL){
        if($index==0 || !is_array($hash)) { return false; }
        foreach($hash as $id=>$val) {
            $fname = implode('_', ['fb', $index, $id]);
            $this->tableDB->where($fname,NULL)->update([$fname=>1]);
        }
        return $this->tableDB;
    }


    /**
     * Удаляет таблицу
     *
     */
    public function _delete() {
        //return $this->tableDB->drop();
        return DB::delete('DROP TABLE `'.$this->table.'`');
    }




    /**
     * Копирование атрибутов
     *
     *
     * @param  integer  $group_index
     * @param  integer  $new_opt_index
     * @param  integer  $parent_opt_index
     *
     * @return object
     */
    public function copyAttr($group_index,$new_opt_index,$parent_opt_index){
        DB::statement('UPDATE `'.$this->table.'` SET `'.implode('_', ['fb', $group_index, $new_opt_index]).'`=`'.implode('_', ['fb', $group_index, $parent_opt_index]).'` WHERE 1');
        return $this->tableDB;
    }
}
