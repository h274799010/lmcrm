<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Models\Salesman;
use App\Models\UserMasks;
use Validator;
use App\Models\Sphere;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\AgentSphere;
use Cookie;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;

class SphereController extends AgentController {


    /**
     * Страница выводит все маски пользователя по сферам
     *
     *
     * @param  boolean|integer  $salesman_id
     *
     * @return Response
     */
    public function index( $salesman_id = false )
    {

        // проверка заданного параметра $salesman_id
        if(isset($salesman_id) && $salesman_id !== false) {

            // получаем данные по все именам масок по всем сферам
            $agentSpheres = $this->user->spheresWithMasks( $salesman_id )->get();

            $salesman = Salesman::find( $salesman_id );
            $wallet = $salesman->wallet[0];

            // максимальная цена по маскам
            $maxPrice = 0;

            // добавление статуса и времени
            $agentSpheres->map(function( $item ) use ( $wallet, &$maxPrice ){

                // id сферы
                $sphere_id = $item->id;

                // добавление данных в маску
                $item->masks->map(function($item) use ($sphere_id, &$maxPrice, $wallet){

                    // получение данных фильтра маски
                    $agentMask = new AgentBitmask($sphere_id);
                    $maskItem = $agentMask->find( $item->mask_id );

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

            // минимальное количество лидо которое может купить агент
            // сколько агент может купить лидов по маске с максимальным прайсом
            $minLeadsToBuy = ( $maxPrice && $wallet )?floor($wallet->balance/$maxPrice):0;

            // данные по забракованным лидам
            $wasted = $wallet->wasted;

            // данные по балансу в шапке
            $balance =
                [
                    'wasted' => $wasted,
                    'minLeadsToBuy' => $minLeadsToBuy,
                    'allSpheres' => $agentSpheres
                ];



            // добавляем данные по балансу на страницу
            view()->share('balance', $balance);

            // переводим данные по балансу в json
            $balanceJSON = json_encode($balance);

            // добавляем на страницу куки с данными по балансу
            Cookie::queue('salesman_balance', $balanceJSON, null, null, null, false, false);

        } else {

            // если параметр не задан
            $agentSpheres = $this->allSphere;
        }


        return view('agent.sphere.index')
            ->with( 'agentSpheres', $agentSpheres )
            ->with( 'salesman_id', $salesman_id );

    }


    /**
     * Страница редактирования маски агента
     *
     *
     * @param  integer  $sphere_id
     * @param  integer  $mask_id
     *
     * @return Response
     */
    public function edit( $sphere_id, $mask_id, $salesman_id = false )
    {
        if(isset($salesman_id) && $salesman_id !== false) {
            $user_id = $salesman_id;
        } else {
            $user_id = $this->uid;
        }

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

        if(isset($salesman_id) && $salesman_id !== false) {
            return view('agent.sphere.edit')
                ->with( 'sphere', $data )
                ->with( 'maskData', $maskData )
                ->with( 'mask', $mask )
                ->with('salesman_id', $salesman_id);
        } else {

            return view('agent.sphere.edit')
                ->with( 'sphere', $data )
                ->with( 'maskData', $maskData )
                ->with( 'mask', $mask );
        }
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
     * @return Response
     */
    public function update(Request $request, $sphere_id, $mask_id, $salesman_id = false)
    {
        if(isset($salesman_id) && $salesman_id !== false) {
            $user_id = $salesman_id;
        } else {
            $user_id = $this->uid;
        }

        // валидация
        $validator = Validator::make($request->all(), [
            'options.*' => 'integer',
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
            if( ($mask[$field] === 0 && !in_array($index, $options)) || ($mask[$field] === 1 && in_array($index, $options)) ) {
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
            if(isset($salesman_id) && $salesman_id !== false) {
                return redirect()->route('agent.salesman.sphere.index', ['salesman_id' => $salesman_id]);
            } else {
                return redirect()->route('agent.sphere.index');
            }
        }
    }


    /**
     * Уладение маски
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function removeMask( Request $request )
    {
        // id пользователя
        $userId = $this->uid;
        // id сферы
        $sphereId = $request->sphere_id;
        // id маски
        $maskId = $request->mask_id;

        // конструктор маски по id сферы и id агента
        $mask = new AgentBitmask( $sphereId, $userId );

        // находим маску по id маски
        $mask = $mask->find( $maskId );

        // если такая маска есть
        if( $mask ){

            // удаляем маску
            $mask->delete();

            // провераем есть ли такая маска
            $testMask = new AgentBitmask( $sphereId, $userId );
            $testMask = $testMask->find( $maskId );

            if( $testMask ){
                // если маска есть ( неудалена )

                // возвращаем 'notDeleted'
                return 'notDeleted';

            }else{
                // если маски нет (т.е. маска успешно удалена)

                // удаление всех лидов с текущей маской из таблицы аукциона
                Auction::removeBySphereMask( $sphereId, $maskId );

                // удаление имени маски
                $maskName = UserMasks::where('mask_id', '=', $mask->id)->first();
                if($maskName->id) {
                    $maskName->delete();
                }


                // сообщаем что маска удалена, возвращаем 'deleted'
                return 'deleted';
            }
        }

        return response()->json( FALSE );
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail($this->uid)->leads()->whereIn([$id])->delete();
        return response()->route('agent.lead.index');
    }


}
