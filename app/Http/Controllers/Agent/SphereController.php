<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Models\Agent;
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
     * @return object
     */
    public function index()
    {
        // если параметр не задан
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
        $user_id = $this->uid;

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
        $user_id = $this->uid;

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
        $maskName = UserMasks::where('mask_id', '=', $mask_id)
            ->where('sphere_id', '=', $sphere_id)->first();

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
            return redirect()->route('agent.sphere.index');
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
                $maskName = UserMasks::where('mask_id', '=', $maskId)->where('sphere_id', '=', $sphereId)->first();
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

    public function getBalance()
    {
        $agent = Agent::findOrFail( $this->uid );
        dd($agent);
    }

    public function activateMask(Request $request)
    {
        $userMask = UserMasks::find($request->input('mask_id'));

        $userMask->active = $request->input('active');
        $userMask->save();

        return response()->json('success');
    }


}
