<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Agent;
use App\Models\AgentBitmask;
use App\Models\FormFiltersOptions;
use App\Models\AdditionFormsOptions;

use App\Models\LeadBitmask;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\Sphere;


    use App\Models\SphereFormFilters;


use App\Models\SphereAdditionForms;

use App\Models\SphereStatuses;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Datatables;

class SphereController extends AdminController {
    public function __construct()
    {
        view()->share('type', 'sphere');
    }

    public function show() {
        return $this->index();
    }
    /*
   * Display a listing of the resource.
   *
   * @return Response
   */
    public function index()
    {
        // Show the page
        return view('admin.sphere.index');
    }

    /**
     * Show the form for edit the resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        return view('admin.sphere.create_edit')->with('fid',$id);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.sphere.create_edit')->with('fid',0);
    }

    /**
     * Send config to page builder for creating/editing a resource.
     *
     * @return JSON
     */
    public function get_config($id)
    {
        $data = [
            "renderType"=>"dynamicAttributes",
            "id"=>null,
            "targetEntity"=>"SphereForm",
            "values"=>[ ],
            "settings"=>[
                "view"=>[
                    "show"=>"form.attributes",
                    "edit"=>"modal.attributes"
                ],
                "fields" => ["checkbox","radio","select"],
                "form.attributes"=>[],
                "button"=>"Add field"
            ]
        ];
        $lead = [
            "renderType"=>"dynamicForm",
            "id"=>null,
            "targetEntity"=>"SphereAdditionForms",
#            "values"=>[
#                ["id"=>0,"_type"=>'input',"label"=>'Name',"position"=>1],
#                ["id"=>0,"_type"=>'email',"label"=>'E-mail',"position"=>2],
#                ["id"=>0,"_type"=>'input',"label"=>'Pnone',"position"=>3],
#            ],
            "settings"=>[
                "view"=>[
                    "show"=>"form.dynamic",
                    "edit"=>"modal.dynamic"
                ],
                "form.dynamic"=>[],
                "button"=>"Add field"
            ]
        ];
        $settings = [
            "targetEntity"=>"SphereSettings",
            "_settings"=>[
                "label"=>'Options',
            ],
            "variables"=>[
                'name'=>[
                    "renderType"=>"single",
                    'name' => 'text',
                    'values'=>'',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => 'Form name',
                        "type"=>'text'
                    ]
                ],
                "status"=>[
                    "renderType"=>"single",
                    'name' => 'status',
                    'values'=>'',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => 'Status',
                        "type"=>'select',
                        'option'=>[['key'=>1,'value'=>'on'],['key'=>0,'value'=>'off']],
                    ]
                ],

                "openLead"=>
                [
                    "renderType"=>"single",

                    'name' => 'openLead',

                    'values'=>'',

                    "attributes" =>
                    [
                        "type"=>'text',
                        "class" => 'form-control',
                        "data-integer"=>true,
                    ],

                    "settings"=>
                    [
                        "label" => 'Max lead to open',
                        "type"=>'text',
                    ],

                    "values"=>3,
                ],
            ],
        ];

        $threshold = [
            "renderType"=>"single",
            'name' => 'status',
            'values'=>'',

            "attributes" =>
            [
                "type"=>'text',
                "class" => 'form-control',
            ],

            "values"=>
            [
                ["id"=>0,"val"=>'bad lead',"vale"=>[1,15],"position"=>1],
            ],

            "settings"=>
            [
                "label" => 'Form name',
                "type"=>'statuses',
                'option'=>[],
                'stat'=>
                [
                    'minLead'=>10
                ]
            ]
        ];

        if($id) {
            $group = Sphere::find($id);
            $data['id']=$id;
            $settings['variables']['name']['values'] = $group->name;
            $settings['variables']['status']['values'] = $group->status;
            $settings['variables']['openLead']['values'] = $group->openLead;

            foreach($group->attributes()->get() as $chrct) {
                $arr=[];
                $arr['id'] = $chrct->id;
                $arr['_type'] = $chrct->_type;
                $arr['label'] = $chrct->label;
                $arr['icon'] = $chrct->icon;
                //$arr['required'] = ($chrct->required)?1:0;
                $arr['position'] = $chrct->position;
                if($chrct->has('options')) {
                    $def_val = bindec($chrct->default_value);
                    $flag=1<<(strlen($chrct->default_value)-1);
                    $offset = 0;
                    $arr['option']=[];
                    foreach($chrct->options()->get() as $eav) {
                        $arr['option'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>[($def_val & ($flag>>$offset))?1:0,$eav->icon]];
                        $offset++;
                    }
                }
                $data['values'][]=$arr;
            }

            if($group->has('leadAttr')) { $lead['values']=array(); }
            foreach($group->leadAttr()->get() as $chrct) {
                $arr=[];
                $arr['id'] = $chrct->id;
                $arr['_type'] = $chrct->_type;
                $arr['label'] = $chrct->label;
                $arr['icon'] = $chrct->icon;
                //$arr['required'] = ($chrct->required)?1:0;
                $arr['position'] = $chrct->position;

                $arr['option']=[];
                if($chrct->has('options')) {
//                    $arr['option']=[];
                    foreach($chrct->options()->get() as $eav) {
                        $arr['option'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value];
                    }
                }

                if($chrct->has('validators')) {
//                    $arr['option']=[];
                    foreach($chrct->validators()->get() as $eav) {
                        $arr['validate'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value];
                    }
                }
                $lead['values'][]=$arr;
//                dd($chrct->validators()->get());
            }

            // todo удалить
//            dd($lead);

            if($group->has('statuses')) { $threshold['values']=array(); }
            foreach($group->statuses()->get() as $chrct) {
                $arr=[];
                $arr['id'] = $chrct->id;
                $arr['val'] = $chrct->stepname;
                $arr['vale'] = [$chrct->minmax,$chrct->percent];
                $arr['position'] = $chrct->position;
                $threshold['values'][]=$arr;
            }
            $threshold['settings']['stat']['minLead']=$group->minLead;
        }

        $data=['opt'=>$settings,"cform"=>$data,'lead'=>$lead,'threshold'=>$threshold];
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        return $this->update($request, false);
    }


    /**
     * Create a newly created, update existing resource in storage.
     *
     * @param  Request  $request
     * @param  integer  $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {

        /**
         *  ===+==-  Структура Request  -==+===
         *
         *
         * opt    <-- данные сферы
         *  |
         *   `data
         *     |
         *     |`targetEntity    <-- значение "SphereSettings"
         *     |
         *      `variables    <-- данные формы сферы
         *        |
         *        |`name    <-- название сферы
         *        |`status    <-- статус сферы (On/Off) значение соответственно (1/0)
         *         `openLead    <-- максимальное количество открытых лидов
         *
         *
         * lead    <-- данные формы лидов
         *  |
         *   `data
         *     |
         *     |`targetEntity    <-- значение "SphereForm"
         *     |
         *      `variables    <-- атрибуты Лидов
         *        |
         *        |`0    <-- атрибута Лида с типом "email"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (email)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |   `placeholder    <-- данные по умолчанию в поле
         *        |
         *        |
         *        |`1    <-- атрибута Лида с типом "textarea"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (textarea)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |  |`placeholder    <-- данные по умолчанию в поле
         *        |  |
         *        |  |`minHeight    <-- минимальная высота (незнаю зачем этот параметр)
         *        |  |
         *        |   `validate    <-- валидация (непонятный параметр и его назначение)
         *        |     |
         *        |     |`0    <-- параметр валидации
         *        |     | |
         *        |     | |`id    <-- идентификатор
         *        |     | |`val    <-- значение (email, url...)
         *        |     |  `vale    <-- параметр пустой, непонятно что означает
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`2    <-- атрибута Лида с типом "input"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (input)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |  |`placeholder    <-- данные по умолчанию в поле
         *        |  |
         *        |  |`minHeight    <-- минимальная высота (незнаю зачем этот параметр)
         *        |  |
         *        |   `validate    <-- валидация (непонятный параметр и его назначение)
         *        |     |
         *        |     |`0    <-- параметр валидации
         *        |     | |
         *        |     | |`id    <-- идентификатор
         *        |     | |`val    <-- значение (email, url...)
         *        |     |  `vale    <-- параметр пустой, непонятно что означает
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`3    <-- атрибута Лида с типом "checkbox"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (checkbox)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |  |`placeholder    <-- данные по умолчанию в поле
         *        |  |
         *        |   `option    <-- поля checkbox
         *        |     |
         *        |     |`0    <-- поле
         *        |     | |
         *        |     | |`id    <-- идентификатор
         *        |     |  `val    <-- значение поля checkbox
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`4    <-- атрибута Лида с типом "radio"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (radio)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |  |`placeholder    <-- данные по умолчанию в поле
         *        |  |
         *        |   `option    <-- поля radio
         *        |     |
         *        |     |`0    <-- поле
         *        |     | |
         *        |     | |`id    <-- идентификатор
         *        |     |  `val    <-- значение поля radio
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`5    <-- атрибута Лида с типом "dropDown"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (select)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |  |`placeholder    <-- данные по умолчанию в поле
         *        |  |
         *        |   `option    <-- поля dropDown
         *        |     |
         *        |     |`0    <-- поле
         *        |     | |
         *        |     | |`id    <-- идентификатор
         *        |     |  `val    <-- значение поля dropDown
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`6    <-- атрибута Лида с типом "calendar"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (calendar)
         *        |  |
         *        |  |`label    <-- подпись поля (не имя)
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |   `position    <-- порядковый номер
         *        |
         *        |
         *        |`...
         *        |
         *
         *
         *
         * cform    <-- данные формы агентов
         *  |
         *   `data
         *     |
         *     |`targetEntity    <-- значение "SphereForm"
         *     |
         *      `variables    <-- атрибуты Агентов
         *        |
         *        |`0    <-- атрибут Агента с типом "checkbox"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (checkbox)
         *        |  |
         *        |  |`label    <-- подпись поля
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |   `option    <-- опции атрибута Агента
         *        |     |
         *        |     |`0    <-- индекс опции
         *        |     |  |
         *        |     |  |`id    <-- id опции, если опция только создана, ее id=0
         *        |     |  |
         *        |     |  |`val    <-- значение опции (в БД пишется в поле name)
         *        |     |  |
         *        |     |   `vale    <-- значения (о или 1 или еще что)
         *        |     |     |
         *        |     |      `1    <-- везде значение "1", непонятно назначение этого поля
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`1    <-- атрибут Агента с типом "radio"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (radio)
         *        |  |
         *        |  |`label    <-- подпись поля
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |   `option    <-- опции атрибута Агента
         *        |     |
         *        |     |`0    <-- индекс опции
         *        |     |  |
         *        |     |  |`id    <-- id опции, если опция только создана, ее id=0
         *        |     |  |
         *        |     |  |`val    <-- значение опции (в БД пишется в поле name)
         *        |     |  |
         *        |     |   `vale    <-- значения (о или 1 или еще что)
         *        |     |     |
         *        |     |      `1    <-- везде значение "1", непонятно назначение этого поля
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`2    <-- атрибут Агента с типом "dropDown"
         *        |  |
         *        |  |`id    <-- идентификатор
         *        |  |
         *        |  |`_type    <-- тип поля (select)
         *        |  |
         *        |  |`label    <-- подпись поля
         *        |  |
         *        |  |`icon    <-- иконка (пока пустое поле)
         *        |  |
         *        |  |`position    <-- порядковый номер
         *        |  |
         *        |   `option    <-- опции атрибута Агента
         *        |     |
         *        |     |`0    <-- индекс опции
         *        |     |  |
         *        |     |  |`id    <-- id опции, если опция только создана, ее id=0
         *        |     |  |
         *        |     |  |`val    <-- значение опции (в БД пишется в поле name)
         *        |     |  |
         *        |     |   `vale    <-- значения (о или 1 или еще что)
         *        |     |     |
         *        |     |      `1    <-- везде значение "1", непонятно назначение этого поля
         *        |     |
         *        |     |`...
         *        |     |
         *        |
         *        |
         *        |`...
         *        |
         *
         *
         * threshold    <-- данные с вкладки статусов
         *  |
         *   `data
         *     |
         *     |`0    <-- один из статосов (как я понял)
         *     |  |
         *     |  |`id    <-- идентификатор
         *     |  |
         *     |  |`val    <-- по умолчанию стоит "bad lead"
         *     |  |
         *     |  |`position    <-- позиция (порядковый номер)
         *     |  |
         *     |   `vale    <-- значения
         *     |     |
         *     |     |`0    <-- похоже это переключатель min/max
         *     |      `1    <-- количественное значение
         *     |
         *     |`...
         *     |
         *
         *
         * stat_minLead    <-- значение "1" (непонял назначение)
         *
         *
         */


        /** ----- ОБРАБОТКА ДАННЫХ ПОЛУЧЕННЫХ С ФРОНТЕНДА ---------- */

        // если Request пустой, возвращаем false
        if( !count($request->all()) ) { return response()->json(FALSE); }

        // данные формы сферы
        $sphereData = $request['opt']['data'];

        // данные формы лида
        $leadData = $request['lead']['data'];

        // проверка атрибутов лида
        // массив атрибутов лида может выглядеть по разному, на каждый вариант своя обработка
        if(isset($leadData['variables'])){
            // переменная существует (ее может и не быть)

            if(isset($leadData['variables']['_type'])){
                // наличие переменной '_type' в массиве

                /*
                 * если у лида только одни атрибут,
                 * он может просто находится в 'variables', без индексов,
                 * (при этом у него всегда бует переменная '_type')
                 *
                 * у лида должно быть не меньше 3 атрибутов
                 * работа метода останавливается
                 */

                return response()->json(FALSE);

            }elseif( $leadData['variables']==["email" => "", "textarea" => "", "input" => "", "checkbox" => "", "radio" => "", "select" => "", "calendar" => "", "submit" => ""] ){
                // 'variables' состоит из массива с ключами из типов полей

                /*
                 * когда у лида нет вообще атрибутов (собственно, это ошибка, такого быть не должно)
                 * переменная 'variables' может принимать такой вид (описанный выше)
                 *
                 * работа метода останавливается
                 */

                return response()->json(FALSE);

            }elseif(isset($leadData['variables'][0])){
                // 'variables' массив элемент с ключом "0" (по идее в других вариантах его быть недолжно)

                if( count($leadData['variables']) < 3 ){
                    // если у лида меньше 3 атрибутов
                    // работа метода останавливается

                    // у лида должно быть не меньше 3 атрибутов
                    return response()->json(FALSE);

                }else{
                    // у лида 3 и больше атрибутов

                    /*
                     * массив просто преобразовывается в коллекцию
                     * чтобы было проще дальше обрабатывать
                     */

                    $leadDataAttr = collect( $leadData['variables'] );
                }



            }else{
                // ничего из вышеперечисленного не подошло

                /*
                 * если ничего вышеперечисленное не подошло
                 * нужно либо добавить обработчик,
                 * либо это ошибка фронтенда
                 *
                 * работа метода останавливается
                 */

                return response()->json(FALSE);
            }

        }else{
            // у лида нет атрибутов
            // работа метода останавливается

            // у лида должно быть не меньше 3 атрибутов
            return response()->json(FALSE);
        }

        // данные формы агента
        $agentData = $request['cform']['data'];

        // проверка атрибутов агента
        // массив атрибутов агента может выглядеть по разному, на каждый вариант своя обработка
        if(isset($agentData['variables'])){
            // переменная существует (ее может и не быть)

            if(isset($agentData['_type'])) {
                // наличие переменной '_type' в массиве

                /*
                 * если у агента только одни атрибут,
                 * он приходит с фронтенда просто как массив 'variables', без индексов,
                 * (при этом у него всегда бует переменная '_type')
                 * в этом случае переменная просто помещается в массив
                 */

                $agentDataAttr = collect( [ $agentData['variables'] ] );

            }elseif( $agentData['variables']==["email" => "", "textarea" => "", "input" => "", "checkbox" => "", "radio" => "", "select" => "", "calendar" => "", "submit" => ""] ) {
                // 'variables' состоит из массива с ключами из типов полей
                // агент должен иметь не меньше одного атрибута
                // работа метода останавливается

                /*
                 * когда у агента нет атрибутов
                 * переменная 'variables' может принимать такой вид (описанный выше)
                 * агент должен иметь не меньше одного атрибута
                 * поэтому в этом случае возвращается FALSE
                 * дальнейшая работа метода прекращается
                 */

                return response()->json(FALSE);

            } elseif( isset($agentData['variables'][0]) ){
                // 'variables' массив у которого есть хотя бы один атрибут

                /*
                 * массив просто преобразовывается в коллекцию
                 * чтобы было проще дальше обрабатывать
                 */

                $agentDataAttr = collect( $agentData['variables'] );

            }else{
                // ничего из вышеперечисленного не подошло

                /*
                 * если ничего вышеперечисленное не подошло
                 * нужно либо добавить обработчик,
                 * либо это ошибка фронтенда
                 *
                 * работа метода останавливается
                 */

                return response()->json(FALSE);
            }

        }else{
            // у агента нет атрибутов
            // работа метода останавливается

            // у агента должно быть не меньше 1 атрибута
            return response()->json(FALSE);
        }

        // минимальное количество лидов
        $minLead = $request['stat_minLead'];

        // статусы
        $statusData = ($request['threshold']['data']) ? collect( $request['threshold']['data'] ) : FALSE;


        /** ----- КОНЕЦ ОБРАБОТКИ ДАННЫХ ПОЛУЧЕННЫХ С ФРОНТЕНДА ---------- */


        /** ----- ПРОВЕРКИ НА ОШИБКИ ---------- */

        // переменная указывающая на ошибки, если $error = true работа метода останавливается
        $error = false;
        $ddd = ''; // todo удалить
        /* проверка атрибутов формы лида на ошибки */
        if( $leadDataAttr ){

            /* у атрибутов с типом checkbox, radio, select обязательно должны быть опции,
               хотя бы одна */
            // перебрать атрибуты лидов и агентов и проверить опции по соответствующим типам
            $leadDataAttr->each(function( $attr ) use( &$error, &$ddd ){

                // если у атрибута тип checkbox, radio или select
                if( ($attr['_type']=='checkbox') || ($attr['_type']=='radio') || ($attr['_type']=='select') ){
                    // и при этом у него нет опций, либо их количество равно 0
                    if( !isset($attr['option']) || (count($attr['option']) == 0) ){
                        // помечаем ошибку
                        $error = true;

                        $ddd = $attr['_type'];
                    }
                }
            });
        }

        /* проверка атрибутов формы агента на ошибки */
        if( $agentDataAttr ){

            /* у атрибутов с типом checkbox, radio, select обязательно должны быть опции,
               хотя бы одна */
            // перебрать атрибуты лидов и агентов и проверить опции по соответствующим типам
            $agentDataAttr->each(function( $attr ) use( &$error ){

                // если у атрибута тип checkbox, radio или select
                if( ($attr['_type']=='checkbox') && ($attr['_type']=='radio') && ($attr['_type']=='select') ){
                    // и при этом у него нет опций, либо их количество равно 0
                    if( !isset($attr['option']) && (count($attr['option']) == 0) ){
                        // помечаем ошибку
                        $error = true;
                    }
                }
            });
        }

        // если есть ошибка, функция вернет ошибку и остановится
//        if($error){ return response()->json(FALSE); } todo вернуть
        if($error){ return $ddd; }

        /** ----- КОНЕЦ ПРОВРОК НА ОШИБКИ ---------- */



        /**
         * Выбираем сферу по id, либо, создаем новую
         *
         */
        if($id) {
            $sphere = Sphere::find($id);
            $sphere->name = $sphereData['variables']['name'];
            $sphere->minLead = $minLead;
            $sphere->status = $sphereData['variables']['status'];
            $sphere->openLead = $sphereData['variables']['openLead'];
        } else {
            $sphere = new Sphere(
            [
                'name' => $sphereData['variables']['name'],
                'status' => $sphereData['variables']['status'],
                'minLead' => $minLead,
                'openLead' => $sphereData['variables']['openLead'],
            ]);
        }

        $sphere->save();



        /**
         * Битмаски лида и анента
         * либо создаем, либо подключаемся к существующим
         */
        // битмаск лида
        $leadBitmask = new LeadBitmask($sphere->id);
        // битмаск агента
        $agentBitmask = new AgentBitmask($sphere->id);



        /**
         * Обработка атрибутов лида
         */
        // если у формы лида заданны атрибуты
        // перебираем их и обрабатываем
        if($leadDataAttr){

            // создаем/обновляем атрибут с его опциями
            $leadDataAttr->each(function( $attr )  use( $sphere, &$leadBitmask ){


            // ЕСЛИ '_status'=='DELETE' УДАЛЯЕМ АТРИБУТ

                // проверяем есть ли задание на удаление
                if( isset( $attr['_status'] ) ){
                    if( $attr['_status'] == 'DELETE' ){
                        // удаляем атрибут из БД

                        // выбираем атрибут по его id
                        $dbAttribute = $sphere->leadAttr()->where('id', '=', $attr['id']);

                        // удаление всех опций атрибутов
                        $dbAttribute->each(function($option){
                            $option->allFormsOptions()->delete();
                        });

                        // удаление атрибута
                        $dbAttribute->delete();

                        // удаление полей в маске атрибута
                        $leadBitmask->removeAttr($attr['id'], null);
                        // останавливаем дальнейшую обработку
                        return false;
                    }
                }


            // СОЗДАЕМ НОВЫЙ АТРИБУТ ЛИБО ОБНОВЛЯЕМ УЖЕ СУЩЕСТВУЮЩИЙ

                // если у атрибуте есть id и он НЕ равен '0'
                if (isset($attr['id']) && $attr['id']) {
                    // выбираем его
                    $leadAttr = SphereAdditionForms::find($attr['id']);
                    // и обновляем
                    $leadAttr->update($attr);

                } else {
                    // если атрибута нет или он равен 0 создаем его
                    $leadAttr = new SphereAdditionForms($attr);
                    $sphere->leadAttr()->save($leadAttr);
                }


            // ДЕЙСТВИЯ НАД ДАННЫМИ АТРИБУТА В ЗАВИСИМОСТИ ОТ ЕГО ТИПА
            //    - типы 'checkbox','radio', 'select'
            //    - типы 'textarea', 'input'
            //    - типы 'email', 'calendar'


                if( ($attr['_type']=='checkbox') || ($attr['_type']=='radio') || ($attr['_type']=='select') ){
                    // обработка атрибутов с типом 'checkbox','radio', 'select'
                    // у этого атрибута должна быть хотя бы одна опция
                    // в начале метода стоит проверка,
                    // если опций нет - метод вернет ошибку еще в начале (до этого места не дойдет)

                    // перебираем все опции и либо создаем новую,
                    // либо обновляем существующую запись опции
                    $optionCollection = collect($attr['option']);
                    $optionCollection->each(function( $option ) use ( &$leadAttr, &$leadBitmask ){

                        if($option['id']){
                            // у опции ЕСТЬ id, т.е. опция уже есть в БД

                            // выбираем данные опции из БД
                            $dbOption = AdditionFormsOptions::find($option['id']);
                            // присваиваем опции новые значения
                            $dbOption->_type = 'option';
                            $dbOption->name = $option['val'];
                            $dbOption->value = (isset($option['vale'])) ? $option['vale'] : '';
                            // сохраняем
                            $dbOption->save();

                        }else{
                            // у опции НЕТ id
                            // (создание новой зписи и полей в битмаске соответственно)

                            // создание новой опции
                            $newOption = new AdditionFormsOptions();
                            // присваиваем опции новые значения
                            $newOption->_type = 'option';
                            $newOption->name = $option['val'];
                            $newOption->value = (isset($option['vale'])) ? $option['vale'] : '';
                            // сохраняем
                            $leadAttr->options()->save($newOption);

                            // создаем новый столбец в БД
                            $leadBitmask->addAttrWithType($leadAttr->id, $newOption->id, $leadAttr->_type);

                            /** смысл этой конструкции я не понял */
//                            if (isset($option['parent'])) {
//                                // копирование атрибутов
//                                $leadBitmask->copyAttr
//                                (
//                                    $leadAttr->id,
//                                    $newOption->id,
//                                    /*parent*/
//                                    $option['parent']
//                                );
//                            }
                        }
                    });

                }elseif( ($attr['_type']=='textarea') || ($attr['_type']=='input') ){
                    // обработка атрибутов с типом 'textarea' и 'input'
                    // атрибут этого типа может иметь валидации, а может и не иметь
                    // если валидации есть - они записываются в таблицу опций,
                    // если нет - НЕ записываются

                    if( isset($attr['validate']) ) {
                        // у атрибута есть поле валидации

                        // обработка значений переменной
                        // данные могут быть либо в одном массиве, либо в массиве с ключами
                        if(isset($attr['validate']['val'])){
                            // если один массив помещаем его в массив
                            // т.е. будет массив с одним ключоьм "0"
                            // создаем объект collect

                            $validateCollection = collect( [ $attr['validate'] ] );

                        }else{
                            // массив с ключами
                            // просто создаем объект collect

                            $validateCollection = collect( $attr['validate'] );
                        }

                        // перебираем все валидации и либо создаем новую,
                        // либо обновляем существующую запись валидации
                        $validateCollection->each(function ($validate) use (&$leadAttr) {

                            if ($validate['id']) {
                                // у валидации ЕСТЬ id, т.е. валидация уже есть в БД

                                // выбираем данные валидации из БД
                                $dbValidate = AdditionFormsOptions::find($validate['id']);
                                // присваиваем валидации новые значения
                                $dbValidate->_type = 'validate';
                                $dbValidate->name = $validate['val'];
                                $dbValidate->value = (isset($validate['vale'])) ? $validate['vale'] : '';

                                // сохраняем
                                $dbValidate->save();

                            } else {
                                // у валидации НЕТ id
                                // (создание новой зписи)

                                // создание новой валидации
                                $newValidate = new AdditionFormsOptions();
                                // присваиваем валидации новые значения
                                $newValidate->_type = 'validate';
                                $newValidate->name = $validate['val'];
                                $newValidate->value = (isset($validate['vale'])) ? $validate['vale'] : '';
                                // сохраняем
                                $leadAttr->validators()->save($newValidate);
                            }
                        });
                    }

                }elseif( ($attr['_type']=='email') || ($attr['_type']=='calendar') ){
                    // обработка атрибутов с типом 'email' и 'calendar'
                    // у атрибута нет ни опций, ни валидаций
                    // в БД опций записываются с _type=field

                    // атрибут должен иметь опцию в таблице опций лида
                    // чтобы его можно было занести в таблицу битмаска

                    // получаем запись поля в таблице addition_forms_options по id атрибута
                    // по идее эта запись в таблице должна быть только одна
                    $field = $leadAttr->field()->first();

                    if( $field==true ){
                        // запись уже есть в бд

                        // выбираем данные поля из БД
                        $dbField = AdditionFormsOptions::find($field['id']);
                        // присваиваем полю новые значения
                        $dbField->_type = 'field';
                        $dbField->name = '';
                        $dbField->value = '';
                        // сохраняем
                        $dbField->save();

                    }else{
                        // у поля НЕТ id
                        // (создание новой зписи)

                        // создание новой валидации
                        $newField = new AdditionFormsOptions();
                        // присваиваем валидации новые значения
                        $newField->_type = 'field';
                        $newField->name = '';
                        $newField->value = '';
                        // сохраняем
                        $leadAttr->options()->save($newField);

                        // создаем новый столбец в БД
                        $leadBitmask->addAttrWithType($leadAttr->id, $newField->id, $leadAttr->_type);
                    }
                }

                return true;
            });

        }



        /**
         * Обработка атрибутов агента
         */
        if($agentDataAttr){

            // перебираем все атрибуты, создаем/обновляем его данные
            $agentDataAttr->each(function( $attr )  use( $sphere, &$agentBitmask ) {


            // ЕСЛИ '_status'=='DELETE' УДАЛЯЕМ АТРИБУТ

                // проверяем есть ли задание на удаление
                if (isset($attr['_status'])) {
                    if ($attr['_status'] == 'DELETE') {
                        // удаляем атрибуд из БД

                        // выбираем атрибут по его id
                        $dbAttribute = $sphere->attributes()->where('id', '=', $attr['id']);

                        // удаление всех опций атрибутов
                        $dbAttribute->each(function($option){
                            $option->options()->delete();
                        });

                        // удаление атрибута
                        $dbAttribute->delete();

                        // удаление полей в маске атрибута
                        $agentBitmask->removeAttr($attr['id'], null);
                        // останавливаем дальнейшую обработку
                        return false;
                    }
                }


            // СОЗДАЕМ НОВЫЙ АТРИБУТ ЛИБО ОБНОВЛЯЕМ УЖЕ СУЩЕСТВУЮЩИЙ

                // если у атрибуте есть id и он НЕ равен '0'
                if (isset($attr['id']) && $attr['id']) {
                    // выбираем его
                    $agentAttr = SphereFormFilters::find($attr['id']);
                    // и обновляем
                    $agentAttr->update($attr);

                } else {
                    // если атрибута нет или он равен 0 создаем его
                    $agentAttr = new SphereFormFilters($attr);
                    $sphere->attributes()->save($agentAttr);
                }


            // ОБРАБОТКА ОПЦИЙ АТРИБУТА (предполагается что у атрибута есть только опции)

                if (isset($attr['option'])) {
                    // контрольная проверка наличия опций
                    // по идее опции должны быть, в начале метода стоит проверка

                    // перебираем все опции и либо создаем новую,
                    // либо обновляем существующую запись опции
                    $optionCollection = collect($attr['option']);
                    $optionCollection->each(function ($option) use (&$agentAttr, &$agentBitmask) {

                        if ($option['id']) {
                            // у опции ЕСТЬ id, т.е. опция уже есть в БД

                            // выбираем данные опции из БД
                            $dbOption = FormFiltersOptions::find($option['id']);
                            // присваиваем опции новые значения
                            $dbOption->name = $option['val'];
                            // сохраняем
                            $dbOption->save();

                        } else {
                            // у опции НЕТ id
                            // (создание новой зписи и полей в битмаске соответственно)

                            // создание новой опции
                            $newOption = new FormFiltersOptions();
                            // присваиваем опции новые значения
                            $newOption->name = $option['val'];
                            // сохраняем
                            $agentAttr->options()->save($newOption);

                            // создаем новый столбец в БД
                            $agentBitmask->addAttrWithType($agentAttr->id, $newOption->id, $agentAttr->_type);

                            /** смысл этой конструкции я не понял */
//                            if (isset($option['parent'])) {
//                                // копирование атрибутов
//                                $agentBitmask->copyAttr
//                                (
//                                    $agentAttr->id,
//                                    $newOption->id,
//                                    /*parent*/
//                                    $option['parent']
//                                );
//                            }
                        }
                    });

                }

                return true;
            });

        }



        /**
         * Обработка статусов
         */
        if($statusData){

        // УДАЛЕНИЕ СТАТУСА ИЗ БД
            // если с фронтенда не пришел статус с таким id как у статуса в БД - статус удаляется

            // все статусы сферы из базы данных
            $dbStatuses =  $sphere->statuses;

            // перебираем все id статуса в БД
            $dbStatuses->each(function( $dbStatus ) use( $statusData ) {

                // переключатель, если true статус удаляется из БД
                $delete = true;

                // перебираем все полученные статусы с фронтенда
                $statusData->each(function( $newStatus ) use( $dbStatus, &$delete ) {
                    // если в таблице статусов есть запись с таким же id как и на фронтенде
                    // переключатель $delete переводится в состояние false
                    if($newStatus['id'] == $dbStatus['id']){
                        $delete=false;
                    }
                });

                // если $delete==false - запись не удаляется, иначе - удаляется
                if($delete){ $dbStatus->delete(); }
            });


        // СОЗДАЕМ НОВЫЙ СТАТУС ЛИБО ОБНОВЛЯЕМ УЖЕ СУЩЕСТВУЮЩИЙ

            // перебираем все атрибуты, создаем/обновляем его данные
            $statusData->each(function($status) use($sphere){

                // если у атрибуте есть id и он НЕ равен '0'
                if (isset($status['id']) && $status['id']) {
                    // выбираем его
                    $dbStatus = SphereStatuses::find($status['id']);

                    $dbStatus->stepname = $status['val'];
                    $dbStatus->minmax = $status['vale'][0];
                    $dbStatus->percent = $status['vale'][1];
                    $dbStatus->position = (isset($status['position'])) ? $status['position'] : 0;


                    // и обновляем
                    $dbStatus->save();

                } else {
                    // если атрибута нет или он равен 0 создаем его
                    $newStatus = new SphereStatuses();
                    $newStatus->stepname = $status['val'];
                    $newStatus->minmax = $status['vale'][0];
                    $newStatus->percent = $status['vale'][1];
                    $newStatus->position = (isset($status['position'])) ? $status['position'] : 0;
                    $sphere->statuses()->save($newStatus);
                }
            });
        }

        return response()->json(TRUE);
    }



    public function destroy($id){

        $group = Sphere::find($id);

        $leadBitmask = new LeadBitmask($group->id);
        $agentBitmask = new AgentBitmask($group->id);

        $leadBitmask->_delete();
        $agentBitmask->_delete();

        $group->delete();
        return redirect()->route('admin.sphere.index');
    }

    /**
     * Show a list of all the languages posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function data()
    {
        $chr = Sphere::select(['id','name', 'status', 'created_at']);

        return Datatables::of($chr)
            ->edit_column('status', function($model) { return view('admin.sphere.datatables.status',['status'=>$model->status]); } )
            ->add_column('actions', function($model) { return view('admin.sphere.datatables.control',['id'=>$model->id]); } )
            ->remove_column('id')
            ->make();
    }

    public function filtration(){
        $spheres = Sphere::active()->get();
        $collection = array();
        foreach($spheres as $sphere){
            $mask = new SphereMask($sphere->id);
            $collection[$sphere->id] = $mask->query_builder()
                ->join('users','users.id','=','user_id')
                ->where('status','=',0)
                ->get();
        }
        return view('admin.sphere.reprice')
            ->with('collection',$collection)
            ->with('spheres',Sphere::active()->lists('name','id'));
    }

    public function filtrationEdit($sphere,$agent_id){
        $sphere = Sphere::findOrFail($sphere);
        $mask = new SphereMask($sphere->id);
        $bitmask = $mask->findShortMask($agent_id);

        return view('admin.sphere.reprice_edit')
            ->with('sphere',$sphere)
            ->with('agent_id',$agent_id)
            ->with('mask',$bitmask)
            ->with('price',$mask->getPrice($agent_id));
    }

    public function filtrationUpdate(Request $request,$sphere,$id){
        $sphere = Sphere::findOrFail($sphere);
        $mask = new SphereMask($sphere->id);
        $mask->setUserID($id);

        $mask->setPrice($request->input('price',0));
        $mask->setStatus(1);

        return redirect()->route('admin.sphere.reprice');
    }
}