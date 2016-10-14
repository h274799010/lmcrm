<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Models\UserMasks;
use Validator;
use App\Models\Sphere;
use App\Models\AgentBitmask;
use App\Models\Auction;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;

class SphereController extends AgentController {

    /**
     * Страница выводит все маски пользователя по сферам
     *
     * @return Response
     */
    public function index($salesman_id = false)
    {
        if(isset($salesman_id) && $salesman_id !== false) {
            $user_id = $salesman_id;
        } else {
            $user_id = $this->uid;
        }

        // выбираем все активные сферы
        $spheres = Sphere::active()->get();

        // конструктор маски, задаем индекс агента
        $agentMask = new AgentBitmask();
        $agentMask->setUserID($user_id);

        if(isset($salesman_id) && $salesman_id !== false) {
            return view('agent.sphere.index')
                ->with('spheres',$spheres)
                ->with('agentMask',$agentMask)
                ->with('salesman_id', $salesman_id);
        } else {
            return view('agent.sphere.index')
                ->with('spheres',$spheres)
                ->with('agentMask',$agentMask);
        }
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
            $maskData = [ 'id'=>0, 'name'=>''];

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
            $maskData = [ 'id'=>$mask->id, 'name'=>$maskName->name];

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


        // Сохраняем имя маски
        // Если имя маски уже есть - находим ее
        $maskName = UserMasks::where('mask_id', '=', $mask_id)->first();

        // Если имени этой маски нет - создаем новое
        if(!isset($maskName->id)) {
            $maskName = new UserMasks();
        }
        // Имя маски
        $maskName->name = $request['maskName'];
        // id агента
        $maskName->user_id = $user_id;
        // id сферы
        $maskName->sphere_id = $sphere_id;
        // id маски
        $maskName->mask_id = $mask->id;
        // Сохраняем данные в БД
        $maskName->save();

        // удаление всех лидов с текущей маской из таблицы аукциона
        Auction::removeBySphereMask( $sphere_id, $mask_id );

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
