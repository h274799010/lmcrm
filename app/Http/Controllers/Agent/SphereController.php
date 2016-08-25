<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use Validator;
use App\Models\Sphere;
use App\Models\AgentBitmask;

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
    public function index()
    {
        // выбираем все активные сферы
        $spheres = Sphere::active()->get();

        // конструктор маски, задаем индекс агента
        $agentMask = new AgentBitmask();
        $agentMask->setUserID($this->uid);

        return view('agent.sphere.index')
            ->with('spheres',$spheres)
            ->with('agentMask',$agentMask);
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
    public function edit( $sphere_id, $mask_id )
    {
        // выбираем сферу
        $data = Sphere::findOrFail($sphere_id);

        // добавляем данные атрибутов
        $data->load('attributes.options');

        // конструктор маски
        $mask = new AgentBitmask($data->id,$this->uid);

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

            // имя маски
            $maskData = [ 'id'=>$mask->id, 'name'=>$mask->name];

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
     * @return Response
     */
    public function update(Request $request, $sphere_id, $mask_id)
    {

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
        $mask = new AgentBitmask( $sphere_id, $this->uid );

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
        $mask->user_id = $this->uid;

        // сохраняем имя таблицы
        $mask->name =$request['maskName'];

        // сохраняем данные в БД
        $mask->save();


        if($request->ajax()){
            return response()->json();
        } else {

            return redirect()->route('agent.sphere.index');
        }
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
