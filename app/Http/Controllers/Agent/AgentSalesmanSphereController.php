<?php

namespace App\Http\Controllers\Agent;

use App\Models\Agent;
use App\Models\Auction;
use App\Models\HistoryBadLeads;
use App\Models\Sphere;
use App\Models\User;
use App\Models\UserMasks;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use App\Models\Salesman;
use App\Models\AgentBitmask;
use Validator;

class AgentSalesmanSphereController extends SphereController
{
    public $salesman;

    public $user;

    public $allSphere;

    public function __construct()
    {
        $this->user = Agent::find(Sentinel::getUser()->id);

        $salesman_id = Route::current()->getParameter('salesman_id');

        $this->salesman = Salesman::find($salesman_id);

        // получаем данные по все именам масок по всем сферам
        $spheres = $this->user->spheresWithMasks($salesman_id)->get();

        $spheres->load('filterAttr', 'leadAttr');

        $wallet = $this->salesman->wallet[0];

        // максимальная цена по маскам
        $maxPrice = 0;

        // добавление статуса и времени
        $spheres->map(function( $item ) use ( $wallet, &$maxPrice ){

            // id сферы
            $sphere_id = $item->id;

            // добавление данных в маску
            $item->masks->map(function($item) use ($sphere_id, &$maxPrice, $wallet){

                // получение данных фильтра маски
                $agentMask = new AgentBitmask($sphere_id);
                $maskItem = $agentMask->find( $item->mask_id );
                if(!$maskItem) {
                    return false;
                }

                if( $maskItem->status == 0){
                    return false;
                }

                // количество лидов, которое агент может купить по этой маске
                $item->leadsCount = floor($wallet->balance/$maskItem->lead_price);


                // добавление статуса
                $item->status = $maskItem->status;
                // добавление даты
                $item->updated_at = $maskItem->updated_at;

                if( $maxPrice < $maskItem->lead_price ){
                    $maxPrice = $maskItem->lead_price;
                }

                return $item;
            });

            return $item;
        });

        $this->allSphere = $spheres;

        // данные по забракованным лидам
        $wasted = $wallet->wasted;

        // Данные по сферам для cookies
        $cookieSpheres = array();
        if($spheres) {
            foreach ($spheres as $key => $sphere) {
                // Имя сферы
                $cookieSpheres[$key]['name'] = $sphere->name;

                // Данные по маскам в сфере
                $cookieSpheres[$key]['masks'] = array();
                foreach ($sphere->masks as $k => $mask) {
                    //$cookieSpheres[$key]['masks'][$k]['status'] = $mask->status;
                    $cookieSpheres[$key]['masks'][$k]['name'] = $mask->name;
                    $cookieSpheres[$key]['masks'][$k]['leadsCount'] = $mask->leadsCount;
                }
            }
        }

        // данные по балансу в шапке
        $balance =
            [
                'wasted' => $wasted,
                'allSpheres' => $cookieSpheres
            ];

        $role = $this->salesman->roles()
            ->where('slug', '!=', 'agent')
            ->where('slug', '!=', 'salesman')
            ->first();
        $userData = array(
            'name' => $this->salesman->first_name.' '.$this->salesman->last_name,
            'role' => false,
            'status' => User::isBanned($this->salesman->id)
        );
        if($role->name) {
            $userData['role'] = $role->name;
        }

        $userIds = array($this->salesman->id);

        $badLeads = HistoryBadLeads::whereIn('depositor_id', $userIds)->count();

        $permissions = $this->salesman->permissions;

        // добавляем данные по балансу на страницу
        view()->share([
            'balance' => $balance,
            'salesman_id' => $this->salesman->id,
            'userData' => $userData,
            'badLeads' => $badLeads,
            'permissions' => $permissions
        ]);

        // переводим данные по балансу в json
        $balanceJSON = json_encode($balance);

        // добавляем на страницу куки с данными по балансу
        Cookie::queue('salesman_balance', $balanceJSON, null, null, null, false, false);
    }

    /**
     * Страница выводит все маски пользователя по сферам
     *
     * @return object
     */
    public function index()
    {
        // получаем данные по все именам масок по всем сферам
        $agentSpheres = $this->allSphere;

        return view('agent.sphere.index')->with( 'agentSpheres', $agentSpheres );

    }

    /**
     * Страница редактирования маски агента
     *
     *
     * @param  integer  $sphere_id
     * @param  integer  $mask_id
     *
     * @return object
     */
    public function edit( $sphere_id, $mask_id )
    {
        $user_id = $this->salesman->id;

        // выбираем сферу
        $data = Sphere::findOrFail($sphere_id);

        // добавляем данные атрибутов
        $data->load('attributes.options');

        // конструктор маски
        $mask = new AgentBitmask( $data->id, $user_id );

        // если $mask_id = 0, значить это будет новая запись в маске
        if( $mask_id == 0 ){

            // делаем поля маски пустыми
            $maskData = [ 'id'=>0, 'name'=>'', 'description'=>''];

            // массив маски тоже пустой
            $mask = [];

        }else{

            // ищем маску по id
            $mask = $mask->find($mask_id);

            // если маска не найдена - редирект на главную страницу
            if( $mask==NULL ){ return redirect('/'); }

            // получаем имя маски
            $maskName = UserMasks::where('mask_id', $mask->id)->where('sphere_id', $sphere_id)->first();

            // имя маски
            $maskData = [ 'id'=>$mask->id, 'name'=>$maskName->name, 'description'=>$maskName->description];

            // находим короткую маску
            $mask = $mask->findShortMaskById();

        }

        return view('agent.sphere.edit')
            ->with( 'sphere', $data )
            ->with( 'maskData', $maskData )
            ->with( 'mask', $mask );
    }

    /**
     * Сохранение данных маски агента в БД
     *
     * если $mask_id = 0 создается новая запись в базе данных
     * если нет - выбирается и редактируется старая
     *
     *
     * @param  Request  $request
     * @param  integer  $sphere_id
     * @param  integer  $mask_id
     *
     * @return object
     */
    public function update(Request $request, $sphere_id, $mask_id)
    {
        $user_id = $this->salesman->id;

        // валидация
        $validator = Validator::make($request->all(), [
            'options.*' => 'integer',
            'options' => 'required',
        ]);

        // если данные не прошли валидацию - выходим
        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        // конструктор маски
        $mask = new AgentBitmask( $sphere_id, $user_id );

        // если маска заданна (т.е. она уже есть в бд) находим ее
        if( $mask_id!=0 ){

            // ищем маску по id
            $mask = $mask->find($mask_id);

            // задаем номер таблице битмаска
            $mask->changeTable($sphere_id);
        }

        // массив с опциями
        $options=array();
        // если опции есть - сохраняем их
        if ($request->has('options')) {
            $options=$request->only('options')['options'];
        }

        // Получаем опции сферы
        $maskOptions = $mask->attributesAssoc();
        // Флаг ( false - опции не изменились, true - опции изменились )
        $flagOptions = false;
        foreach ($maskOptions as $field => $index) {
            if( ($mask[$field] == 0 && !in_array($index, $options)) || ($mask[$field] == 1 && in_array($index, $options)) ) {
                // Если значение опции в маске совпадает со значением из $request - опция не изменилась (маску не пересохраняем)
                $flagOptions = false;
            } else {
                // В противном случае пересохраняем маску
                $flagOptions = true;
                break;
            }
        }


        if($flagOptions === true) {
            // сохраняем атрибуты
            $mask->setAttrById($options);

            // выставляем статус в 0
            $mask->status = 0;

            // устанавливаем пользователя (после find() некоторые данные конструктора теряются)
            $mask->user_id = $user_id;

            // сохраняем имя таблицы
            //$mask->name =$request['maskName'];

            // сохраняем данные в БД
            $mask->save();
        }


        // Сохраняем имя маски
        // Если имя маски уже есть - находим ее
        $maskName = UserMasks::where('mask_id', '=', $mask_id)->first();

        // Если имени этой маски нет - создаем новое
        if(!isset($maskName->id)) {
            $maskName = new UserMasks();
        }
        // Имя маски
        $maskName->name = $request['maskName'];
        // Имя маски
        $maskName->description = $request['maskDescription'];
        // id агента
        $maskName->user_id = $user_id;
        // id сферы
        $maskName->sphere_id = $sphere_id;
        // id маски
        $maskName->mask_id = $mask->id;
        // Сохраняем данные в БД
        $maskName->save();

        if($flagOptions === true) {
            // удаление всех лидов с текущей маской из таблицы аукциона
            Auction::removeBySphereMask( $sphere_id, $mask_id );
        }

        if($request->ajax()){
            return response()->json();
        } else {
            return redirect()->route('agent.salesman.sphere.index', ['salesman_id' => $this->salesman->id]);
        }
    }
}
