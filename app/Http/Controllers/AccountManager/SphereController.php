<?php

namespace App\Http\Controllers\AccountManager;

use App\Http\Controllers\AccountManagerController;

use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\AgentBitmask;
use App\Models\Sphere;
use App\Models\Auction;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

use App\Models\UserMasks;
use Illuminate\Http\Request;
use Datatables;

class SphereController extends AccountManagerController {
    public function __construct()
    {
        view()->share('type', 'sphere');
    }



    /**
     * Вывод всех масок агентов, у которых статус = 0
     * Update: Вывод всех масок агентов, независимо от статуса
     */
    public function filtration(){
        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        // выбираем активные сферы
        $spheres = $accountManager->spheres()->get();

        // все неактивные маски пользователей
        $collection = array();

        // перебираем все сферы и находим их маски
        foreach($spheres as $sphere){

            // маска по сфере
            $mask = new AgentBitmask($sphere->id);

            // добавляем в массив (с ключем id сферы) все неактинвые маски сферы с агентами масок
            $collection[$sphere->id] = $mask->where('status', '=', 0)->with('user')->get();
        }

        return view('accountManager.sphere.reprice')
            ->with('collection',$collection)
            ->with('spheres',Sphere::active()->lists('name','id'));
    }

    public function filtrationAll()
    {
        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        // выбираем активные сферы
        $spheres = $accountManager->spheres()->get();

        // все неактивные маски пользователей
        $collection = array();

        // перебираем все сферы и находим их маски
        foreach($spheres as $sphere){

            // маска по сфере
            $mask = new AgentBitmask($sphere->id);

            // добавляем в массив (с ключем id сферы) все неактинвые маски сферы с агентами масок
            $collection[$sphere->id] = $mask->whereIn('status', [0,1])->with('user')->get();
        }

        return view('accountManager.sphere.all')
            ->with('collection',$collection)
            ->with('spheres',Sphere::active()->lists('name','id'));
    }




    /**
     * Страница редактирования маски пользователя администратором
     * (на этой странице администратор задает прайс агента)
     *
     *
     * @param  integer  $sphere
     * @param  integer  $user_id
     * @param  integer  $mask_id
     *
     * @return object
     */
    public function filtrationEdit( $sphere, $user_id, $mask_id){

        // находим сферу по id
        $sphere = Sphere::findOrFail($sphere);

        // конструктор маски агента
        $mask = new AgentBitmask($sphere->id, $user_id);

        // ищем маску в таблице по ее id
        $mask = $mask->find($mask_id);

        $user = Agent::find($user_id);

        // возвращаем номер таблицы в маску
        $mask->changeTable($sphere->id);

        // находим короткую маску
        $bitmask = $mask->findShortMaskById();


        return view('accountManager.sphere.reprice_edit')
            ->with('sphere', $sphere)
            ->with('mask_id', $mask_id)
            ->with('mask', $bitmask)
            ->with('user', $user)
            ->with('price', $mask);
    }



    /**
     * Сохранение прайса агента и установка статуса
     *
     *
     * @param  Request  $request
     * @param  integer  $sphere
     * @param  integer  $mask_id
     *
     * @return object
     */
    public function filtrationUpdate( Request $request, $sphere, $mask_id )
    {

        // конструктор битмаска агента
        $mask = new AgentBitmask($sphere);

        // выбираем маску
        $mask = $mask->find($mask_id);

        // возвращаем номер таблицы в маску
        $mask->changeTable($sphere);

        // устанавливаем прайс агента
        $mask->lead_price = $request['price'];

        // устанавливаем статус
        $mask->status = 1;

        // сохранение изменений
        $mask->save();

        // добавлаем лиды агенту в таблицу аукциона (если есть лиды по маске)
        Auction::addByAgentMask( $mask_id, $sphere );

        return redirect()->route('accountManager.sphere.reprice');
    }
}