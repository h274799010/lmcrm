<?php

namespace App\Transformers;

use App\Models\Agent;
use App\Models\Auction;
use App\Models\LeadBitmask;
use App\Models\OpenLeads;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use League\Fractal\TransformerAbstract;

class ObtainedLeadsTransformer extends TransformerAbstract
{
    protected $sphere;

    public function __construct($sphere)
    {
        $this->sphere = $sphere;
    }

    public function transform(Auction $auction)
    {

        $agent = Agent::find(Sentinel::getUser()->id);
        $sphere = $this->sphere;

        // маска лида
        $leadBitmask = new LeadBitmask( $sphere->id );

        $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', $agent->id )->first();

        if( $openLead ){
            // если открыт - блокируем возможность открытия
            $open = view('agent.lead.datatables.obtain_already_open')->render();
        }else {
            // если не открыт - отдаем ссылку на открытия
            $open = view('agent.lead.datatables.obtain_open', ['data' => $auction])->render();
        }

        // проверяем открыт ли этот лид у других агентов
        $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', '<>', $agent->id )->first();

        if( $openLead ){
            // если открыт - блокируем ссылку
            $openAll =  view('agent.lead.datatables.obtain_already_open')->render();
        }else {
            // если не открыт - отдаем ссылку на открытие всех лидов
            $openAll =  view('agent.lead.datatables.obtain_open_all', ['data' => $auction])->render();
        }

        //2016-11-08 15:16:35
        $fields = array(
            0 => view('agent.lead.datatables.obtain_count', [ 'opened'=>$auction['lead']['opened'] ])->render(), //count
            1 => $open, // open
            2 => $openAll, // openAll
            3 => $auction['maskName']->name, // mask
            4 => $auction['lead']['updated_at']->toDateTimeString(), // updated
            5 => $auction['lead']['name'], // name
            6 => ( $auction['lead']->obtainedBy($agent['id'])->count() ) ? $auction['lead']['phone']->phone : trans('site/lead.hidden'), // phone
            7 => ( $auction['lead']->obtainedBy($agent['id'])->count() ) ? $auction['lead']['email'] : trans('site/lead.hidden'), // e-mail
        );

        $recalcFieldsKeys = false;
        if(!Sentinel::hasAccess(['agent.lead.openAll'])) {
            unset($fields[2]);
            $recalcFieldsKeys = true;
        }

        /**  ---  ЗАПОЛНЕНИЕ ПОЛЕЙ fb_ В ТАБЛИЦЕ obtain  ---  */

        // получаем все атрибуты агента
        $agentAttributes = $sphere->filterAttr;

        // маска fb полей лидов
        // массив с ключами и значениями только fb_ полей
        // [ fb_11_2=>1, fb_2_1=>0 ]
        $fdMask = collect($leadBitmask->findFbMask());

        // индекс, столбца таблицы dataTables
        $index = 8;

        // перебираем все атрибуты и выставляем значения по маске лида
        foreach($agentAttributes as $attr){
            // маска текущего лида
            $leadMask = $fdMask[$auction['lead']['id']];


            // выбираем тип текущего атрибута
            $attrType = $attr->_type;


            /** опции этих атрибутов имеют тип option их всегда несколько
            дальше идет фильтрация по маске лида, выбираются опции которые относятся к конкретному лиду */

            // все опции атрибута
            $allOption = $attr->options;

            // переменная с отфильтрованными опциями
            $value = '';

            // фльтруем все опции атрибута по маске атрибута
            foreach($allOption as $opt){

                // полное имя поля fb в таблице маски лида
                $fb_attr_opt = 'fb_' .$opt->attr_id .'_' .$opt->id;

                // если в поле есть значение, добавляем его,
                // если нет - пропускаем
                if( $leadMask[$fb_attr_opt] == 1 ){

                    if( $value=='' ){
                        // если переменная пустая - присваиваем значение
                        $value = $opt->name;

                    }else{
                        // если в переменной уже есть опции - добавляем через запятую
                        $value = $value .', ' .$opt->name;
                    }
                }


            }

            $fVal = view('agent.lead.datatables.obtain_data',['data'=>$value,'type'=>$attrType])->render();

            // добавляем столбец в таблицу
            $fields[$index] = $fVal;

            ++$index;
        }



        /**  ---  ЗАПОЛНЕНИЕ ПОЛЕЙ ad_ В ТАБЛИЦЕ obtain  ---  */

        // получаем все атрибуты лида
        $leadAttributes = $sphere->leadAttr;

        // маска ad полей лидов
        // массив с ключами и значениями только ad_ полей
        // [ ad_11_2=>1, ad_2_1=>'mail@mail.com' ]
        $adMask = collect($leadBitmask->findAdMask());


        // перебираем все атрибуты и выставляем значения по маске лида
        foreach($leadAttributes as $attr){

            // маска текущего лида
            $leadMask = $adMask[ $auction['lead']['id'] ];

            // выбираем тип текущего атрибута
            $attrType = $attr->_type;

            /* - ОБРАБОТКА ОПЦИЙ В ЗАВИСИМОСТИ ОТ ТИПА АТРИБУТА - */
            if( $attrType=='calendar' || $attrType=='email' ){
                // опции этих атрибутов имеют тип field,
                // в таблице опций должна быть только одна запись с этим атрибутом

                // получение имени поля
                $ad_attr_opt = 'ad_' .$attr->id .'_0';

                // присваивем значение поля записанное в мске лида
                $value = $leadMask[$ad_attr_opt];

            }elseif( $attrType=='radio' || $attrType=='checkbox' || $attrType=='select' ){
                // опции этих атрибутов имеют тип option их всегда несколько
                // дальше идет фильтрация по маске лида, выбираются опции которые относятся к лиду

                // все опции атрибута
                $allOption = $attr->options;

                // переменная с отфильтрованными опциями
                $value = '';

                // фльтруем все опции атрибута по маске атрибута
                foreach($allOption as $opt){

                    // полное имя поля ad в таблице маски лида
                    $ad_attr_opt = 'ad_' .$opt->attr_id .'_' .$opt->id;

                    // если в поле есть значение, добавляем его,
                    // если нет - пропускаем
                    if( $leadMask[$ad_attr_opt] == 1 ){

                        if( $value=='' ){
                            // если переменная пустая - присваиваем значение
                            $value = $opt->name;

                        }else{
                            // если в переменной уже есть опции - добавляем через запятую
                            $value = $value .', ' .$opt->name;
                        }
                    }
                }

            }elseif( $attrType=='input' || $attrType=='textarea' ){
                // опции этих атрибутов не имеют запись в таблице опций атрибутов



                // полное имя поля ad в таблице маски лида
                $ad_attr_opt = 'ad_' .$attr->id .'_0';

                // присваивем значение поля записанное в мске лида
                if(isset($leadMask[$ad_attr_opt])){
                    $value = $leadMask[$ad_attr_opt];
                }else{
                    $value = null;
                }

            }else{
                // если не подошло ни одно значение
                // какие то ошибки на фронтенде

                $value = null;
            }

            $adVal = view('agent.lead.datatables.obtain_data',['data'=>$value,'type'=>$attrType])->render();

            $fields[$index] = $adVal;

            ++$index;
        }

        if($recalcFieldsKeys == true) {
            $newFields = array();
            foreach ($fields as $key => $field) {
                if($key <= 1) {
                    $newFields[$key] = $field;
                } else {
                    $newFields[$key - 1] = $field;
                }
            }
            $fields = $newFields;
        }

        return $fields;
    }
}