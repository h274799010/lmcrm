<?php


namespace App\Models;

use App\Console\Commands\SendLeadsToAuction;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use Illuminate\Support\Facades\Queue;
use MongoDB\Driver\Query;

class Auction extends Model
{
    use SoftDeletes;

    protected $table = "auctions";


    /**
     * Атрибуты, для которых разрешено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [ 'sphere_id', 'user_id', 'lead_id', 'mask_id' ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
//    public $timestamps = false;



    /**
     * Связь с таблицей лидов
     *
     * @return Query
     */
    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }


    /**
     * Связь с таблицей имени маски
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function maskName() {
        return $this->hasOne('App\Models\UserMasks', 'id', 'mask_name_id');
    }


    /**
     * Получение сферы
     *
     */
    public function sphere(){
        return $this->hasOne('App\Models\Sphere', 'id', 'sphere_id');
    }



    /**
     * Добавление данных взятые из битмаска агента
     *
     * todo доработать
     *
     * @param  AgentBitmask  $agentsBitmask
     * @param  integer  $sphere_id
     * @param  integer  $lead_id
     *
     * @return boolean
     */
    public static function addFromBitmask( $agentsBitmask, $sphere_id, $lead_id )
    {
        // переменная запроса
        $query = [];

        // перебираем всех агентов и добавляем данные в таблицу
        $agentsBitmask->each( function( $agent ) use( &$query, $sphere_id, $lead_id ){
            $maskName = UserMasks::where('user_id', '=', $agent['user_id'])->where('mask_id', '=', $agent['id'])->first();

            if(isset($maskName->id)) {
                // формируем запрос
                $query[] = [
                    'sphere_id' => $sphere_id,
                    'lead_id' => $lead_id,
                    'user_id' => $agent['user_id'],
                    'mask_id' => $agent['id'],
                    'mask_name_id' => $maskName->id
                ];
                //Queue::later($accessibility_at, new SendLeadsToAuction($query));
            }

        });

        // делаем запрос (записываем данные в таблицу аукциона)
        return Auction::insert( $query );
        //return true;
    }


    /**
     * Добавление лида на аукцион по id пользователя
     *
     *
     * @param  integer  $user_id
     * @param  integer  $mask_id
     * @param  integer  $sphere_id
     * @param  integer  $lead_id
     *
     * @return boolean
     */
    public static function addByAgentId( $user_id, $mask_id, $sphere_id, $lead_id )
    {
        // получаем id имени маски пользователя
        $maskName = UserMasks::where('user_id', '=', $user_id)->where('mask_id', '=', $mask_id)->first();

        // переменная запроса
        $query[] = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead_id, 'user_id'=>$user_id, 'mask_id'=>$mask_id, 'mask_name_id'=>$maskName->id ];
        //$query = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead_id, 'user_id'=>$user_id, 'mask_id'=>$mask_id, 'mask_name_id'=>$maskName->id, 'accessibility_at' => $accessibility_at ];
        //Queue::later($accessibility_at, new SendLeadsToAuction($query));

        // делаем запрос (записываем данные в таблицу аукциона)
        return Auction::insert( $query );
        //return true;
    }


    /**
     * Добавление данных взятые из битмаска агента
     *
     *
     * @param  integer  $mask_id
     * @param  integer  $sphere_id
     *
     * @return boolean
     */
    public static function addByAgentMask( $mask_id, $sphere_id )
    {

        // конструктор битмаска агента
        $agentBitmask = new AgentBitmask( $sphere_id );

        // конструктор битмаска лида
        $leadBitmask = new LeadBitmask( $sphere_id );

        // выбираем маску
        $agentBitmask = $agentBitmask->find( $mask_id );

        // короткая маска лида ("ключ"=>"значение")
        $agentBitmaskData = $agentBitmask->findFbMaskById();

        // id всех лидов по фильтру
        $list = $leadBitmask->filterByMask( $agentBitmaskData )->lists('user_id');

        // массив id пользователей, по которым нужно исключить выбор лидов
        $excludedUsers = User::excludedUsers($agentBitmask['user_id']);

        // получаем все лиды, помеченные к аукциону, по id из массива, без лидов автора
        $leadsByFilter =
            Lead::
              whereIn('id', $list)                     // все лиды полученыые по маске агента
            ->where('status', 3)                       // котрые помеченны к аукциону
            //->where('agent_id', '<>', $agentBitmask['user_id'])      // без лидов, которые занес агент
            ->whereNotIn('agent_id', $excludedUsers)  // без лидов, которые занес агени и его продавцы
            ->get();



        // переменная запроса
        $query = [];

        // перебираем всех агентов и добавляем данные в таблицу
        $leadsByFilter->each( function( $lead ) use( &$query, $sphere_id, $agentBitmask ){
            $maskName = UserMasks::where('user_id', '=', $agentBitmask['user_id'])->where('mask_id', '=', $agentBitmask['id'])->first();

            // формируем запрос
            $query[] = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead['id'], 'user_id'=>$agentBitmask['user_id'], 'mask_id'=>$agentBitmask['id'], 'mask_name_id'=>$maskName->id ];
            //$query = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead['id'], 'user_id'=>$agentBitmask['user_id'], 'mask_id'=>$agentBitmask['id'], 'mask_name_id'=>$maskName->id];
            //Queue::later($accessibility_at, new SendLeadsToAuction($query));

        });

        if( count($query)>0 ){
            // делаем запрос (записываем данные в таблицу аукциона)
            return Auction::insert( $query );
            //return true;
        }

        return false;
    }

    /**
     * Отправляем на аукцион лидов только по самым дорогим маскам
     *
     * @param $agent_id
     * @param $sphere_id
     * @return bool
     */
    public static function addLeadInExpensiveMasks($agent_id, $sphere_id)
    {
        $user = Sentinel::findById($agent_id);
        if($user->inRole('salesman')) {
            $user = Salesman::find($user->id);
            $agent = $user->agent()->first();
        } else {
            $agent = Agent::find($user->id);
            $user = $agent;
        }

        $agentSphere = AgentSphere::where('agent_id', '=', $agent->id)
            ->where('sphere_id', '=', $sphere_id)
            ->first();

        // Получаем агента для которого редактируется маска
        $user = Agent::find($agent_id);

        // Получаем все маски агента
        $masks = $user->bitmaskAll($sphere_id);

        // конструктор битмаска лида
        $leadBitmask = new LeadBitmask( $sphere_id );

        // Массив со всеми подходящими агенту лидами
        // где ключ - id лида, значение - массив с id масок (по которым лид подходит агенту)
        // lead_id => [masks_ids, ...]
        $allLeads = array();
        foreach ($masks as $masksKey => $mask) {
            // короткая маска лида ("ключ"=>"значение")
            $agentBitmaskData = $mask->findFbMaskById();

            // id всех лидов по фильтру
            $leads = $leadBitmask->filterByMask( $agentBitmaskData )->lists('user_id');

            // массив id пользователей, по которым нужно исключить выбор лидов
            $excludedUsers = User::excludedUsers($mask['user_id']);

            // получаем все лиды, помеченные к аукциону, по id из массива, без лидов автора
            $leadsByFilter =
                Lead::
                whereIn('id', $leads)                     // все лиды полученыые по маске агента
                ->where('status', 3)                       // котрые помеченны к аукциону
                //->where('agent_id', '<>', $agentBitmask['user_id'])      // без лидов, которые занес агент
                ->whereNotIn('agent_id', $excludedUsers);  // без лидов, которые занес агени и его продавцы

            if($agent->inRole('dealmaker')) {
                $excludedLeads = $agent->openLeadsInSphere($sphere_id)->get()->lists('lead_id')->toArray();
                if(count($excludedUsers) > 0) {
                    $leadsByFilter = $leadsByFilter->whereNotIn('id', $excludedLeads);
                }
            }
            if($agent->inRole('leadbayer')) {
                $leadsByFilter = $leadsByFilter->where('specification', '!=', Lead::SPECIFICATION_FOR_DEALMAKER);
            }
             $leadsByFilter = $leadsByFilter->get();
            // массив id лидов подходящих по текущей маске
            $leadsIds = $leadsByFilter->lists('id')->toArray();

            // проходимся по всем лидам и помещаем их в массив $allLeads
            if(count($leadsIds) > 0) {
                foreach ($leadsIds as $leadId) {
                    $allLeads[$leadId][] = $mask->id;
                }
            }
        }

        // массив цен масок
        // где ключ - id маски, значение - цена маски
        // mask_id => mask_price
        $masksPrices = $masks->lists('lead_price', 'id')->toArray();

        // массив id лидов отсортированный по подходящим и самим дорогим маскам
        $leadsInMasks = array();
        // проходимся по всем найденным лидам
        if(count($allLeads) > 0) {
            foreach ($allLeads as $leadId => $leadMasks) {
                // наибольшая цена маски
                $maxPrice = 0;
                // id маски с наибольшей ценой
                $maxId = 0;

                // проходимся по всем, подходящим лиду, маскам
                foreach ($leadMasks as $leadMask) {
                    // если у текущей маски цена наибольшая - перезаписываем переменные
                    // $maxPrice и $maxId данными для этой маски
                    if($masksPrices[$leadMask] > $maxPrice) {
                        $maxPrice = $masksPrices[$leadMask];
                        $maxId = $leadMask;
                    }
                }

                // добавляем id лида в массив для маски с наибольшей ценой
                $leadsInMasks[$maxId][] = $leadId;
            }
        }

        foreach ($masks as $mask) {
            if(isset($leadsInMasks[$mask->id]) && count($leadsInMasks[$mask->id]) > 0) {
                // Удаляем текушие аукционы по данной маске
                Auction::where('user_id', '=', $agent_id)->where('mask_id', '=', $mask->id)->delete();

                // Список лидов по данной маске
                $leads = Lead::whereIn('id', $leadsInMasks[$mask->id])->get();

                // переменная запроса
                $query = [];

                // перебираем всех агентов и добавляем данные в таблицу
                $leads->each( function( $lead ) use( &$query, $sphere_id, $mask, $agentSphere ){
                    $maskName = UserMasks::where('user_id', '=', $mask['user_id'])->where('mask_id', '=', $mask['id'])->first();

                    if($agentSphere->agent_range <= $lead->current_range) {
                        // формируем запрос
                        $query[] = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead['id'], 'user_id'=>$mask['user_id'], 'mask_id'=>$mask['id'], 'mask_name_id'=>$maskName->id ];
                    }

                    //$query = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead['id'], 'user_id'=>$mask['user_id'], 'mask_id'=>$mask['id'], 'mask_name_id'=>$maskName->id, 'accessibility_at' => $accessibility_at ];
                    //Queue::later($accessibility_at, new SendLeadsToAuction($query));

                });

                if( count($query)>0 ){
                    // делаем запрос (записываем данные в таблицу аукциона)
                    Auction::insert( $query );
                    //return true;
                }

            }
        }

        return true;
    }


    /**
     * Удаление данных по id лида
     *
     *
     * @param  integer  $lead_id
     *
     * @return boolean
     */
    public static function removeByLead( $lead_id )
    {
        Auction::where( 'lead_id', $lead_id)->update(['status' => 1]);
        return Auction::where( 'lead_id', $lead_id)->delete();
    }


    /**
     * Удаление данных по id сферы и id маски
     *
     *
     * @param  integer  $sphere_id
     * @param  integer  $mask_id
     *
     * @return boolean
     */
    public static function removeBySphereMask( $sphere_id, $mask_id)
    {
        Auction::where( 'sphere_id', $sphere_id)->where( 'mask_id', $mask_id)->update(['status' => 1]);
        return Auction::where( 'sphere_id', $sphere_id)->where( 'mask_id', $mask_id)->delete();
    }
}