<?php

namespace App\Models;

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

    // конструктор запросов
    public $tableDB = NULL;

    // отключаем метки времени
    public $timestamps = false;


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
        $this->tableNum = $id;
        $this->tablePrefix = $tablePrefix;
        $this->table = $this->tablePrefix .(int)$id;
        $this->tableFields = $tableFields;

        if ( $id && !DB::getSchemaBuilder()->hasTable( $this->table ) ) {
            $this->createTable();
        }
        $this->tableDB = DB::table($this->table);
        if($userID) { $this->userID=$userID; }

        parent::__construct($attributes);

        return $this->table;
    }


    /**
     * Создание новой таблицы
     *
     * @return void
     */
    protected function createTable()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS `' . $this->table .'`' .$this->tableFields , []);
        DB::statement('ALTER TABLE `'.$this->table.'` ADD UNIQUE (`user_id`)');
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
    public function getStatus($user_id=NULL){
        $user_id = ( $user_id ) ? $user_id : $this->userID;
        return $this->tableDB->where('user_id','=',$user_id);
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
     * todo разобраться
     * непонял смысл
     *
     */
    public function obtain( $user_id=NULL ){
        $user_id = ($user_id) ? (int)$user_id : $this->userID;
        $attributes = $this->attributes();
        $list = DB::table(DB::raw('`'.$this->table.'` as `t1`'))

            ->join(DB::raw('`'.$this->table.'` as `t2`'),function($join) use ($attributes){

                foreach($attributes as $attr){
                    if(stripos($attr,'fb_')!==false) {
                        $join->on('t1.'.$attr,'>=','t2.'.$attr);
                    }
                }
            })

            ->where('t1.user_id','=',$user_id)
            ->where('t1.status','=','1')
            ->where('t2.user_id','<>',$user_id) ///??? need refactoring
            ->select('t2.*');

        return $list;
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
     * Установка значения атрибута
     *
     *
     * @param  string  $opt_index
     * @param  integer  $user_id
     *
     * @return object
     */
    public function setAttr($opt_index,$user_id=NULL){
        $user_id = ($user_id) ? $user_id : $this->userID;
        if (is_array($opt_index)) {
            $values = array();
            $mask = $this->tableDB->where('user_id','=',$user_id)->first();
            if($mask) {
                $values['id']=$mask->id;
            } else {
                $values['id'] = $this->tableDB->insertGetId(['user_id'=>$user_id]);
            }
            $attributes = $this->attributesAssoc();
            foreach($attributes as $field=>$index) {
                $values[$field]=(in_array($index,$opt_index))?1:0;
            }

            $this->tableDB->update($values);
        }
        return $this->tableDB;
    }




    /* ---------------------------------- Table structure ---------------------------------- */


    /**
     * Возвращает все поля таблицы
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
     * @todo поменить ссылки на этот метод в системе и удалить
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
     *
     * @return object
     */
    public function addAttrWithType($group_index,$opt_index, $field='varchar'){

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
                DB::statement('ALTER TABLE `' . $this->table . '` ADD COLUMN `' . $index . '` ' .$fieldsParameter[$field] .' NULL', []);
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
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '', []);
                }
            }
        } elseif(is_numeric($group_index) && $opt_index==null){
            $delAttr = preg_grep("/^fb_" . $group_index . "_.*/", $this->attributes());
            foreach($delAttr as $item) {
                DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '', []);
            }
        }else {
            if (is_array($opt_index)) {
                foreach ($opt_index as $aVal) $this->removeAttr($group_index, $aVal);
            } else {
                $index = implode('_', ['fb', $group_index, $opt_index]);
                if (in_array($index, $this->attributes())) {
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $index . '', []);
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
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '', []);
                }
            }
        } elseif(is_numeric($group_index) && $opt_index==null){
            $delAttr = preg_grep("/^ad_" . $group_index . "_.*/", $this->attributes());
            foreach($delAttr as $item) {
                DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $item . '', []);
            }
        }else {
            if (is_array($opt_index)) {
                foreach ($opt_index as $aVal) $this->removeAd($group_index, $aVal);
            } else {
                $index = implode('_', ['ad', $group_index, $opt_index]);
                if (in_array($index, $this->attributes())) {
                    DB::statement('ALTER TABLE `' . $this->table . '` DROP COLUMN `' . $index . '', []);
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
     * todo разобраться
     *
     * Этот класс недописан,
     * что от него ожидалось получить?
     */
    public function getAppends() {

        return $this->hasOne();
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
