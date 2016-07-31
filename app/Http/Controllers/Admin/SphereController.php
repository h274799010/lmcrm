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
                if($chrct->has('options')) {
                    $arr['option']=[];
                    foreach($chrct->options()->get() as $eav) {
                        $arr['option'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value];
                    }
                }
                if($chrct->has('validators')) {
                    $arr['option']=[];
                    foreach($chrct->validators()->get() as $eav) {
                        $arr['validate'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value];
                    }
                }
                $lead['values'][]=$arr;
            }

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












        if( !count($request->all()) ) { return response()->json(FALSE); }


        $opt = $request->only('opt');

        /**
         * Выбираем сферу по id, либо, создаем новую, если id нету
         *
         */
        if($id) {
            $group = Sphere::find($id);
            $group->name = $opt['opt']['data']['variables']['name'];
            $group->minLead = $request->get('stat_minLead');
            $group->status = $opt['opt']['data']['variables']['status'];
            $group->openLead = $opt['opt']['data']['variables']['openLead'];
        } else {
            $group = new Sphere(
            [
                'name' => $opt['opt']['data']['variables']['name'],
                'status' => $opt['opt']['data']['variables']['status'],
                'minLead' => $request->get('stat_minLead'),
                'openLead' => $opt['opt']['data']['variables']['openLead'],
            ]);
            $group->save();
        }


        /**
         * Создание или битмасков лида и агента
         */
        // битмас лида
        $leadBitmask = new LeadBitmask($group->id);

        // битмаск агенда
        $agentBitmask = new AgentBitmask($group->id);

//    Непонял, зачем присваивать имя таблицы битмаск объекту sphere
//        $group->table_name = $bitMask->getTableName();
        $group->save();

        $data = $request->only('lead');
        $new_chr = $data['lead']['data']['variables'];

        /**
         * Удаление атрибута Лида, если его статус == 'DELETE'
         */
        if($new_chr) {
            foreach ($new_chr as $index => $leadAttr) {
                if (isset($leadAttr['_status'])) {
                    if ($leadAttr['_status'] == 'DELETE') {
                        $group->leadAttr()->where('id', '=', $leadAttr['id'])->delete();
                        unset($new_chr[$index]);
                    }
                }
            }
        }


        if($new_chr) {
            foreach ($new_chr as $attr) {
                if (isset($attr['id']) && $attr['id']) {
                    $leadAttr = SphereAdditionForms::find($attr['id']);
                    $leadAttr->update($attr);
                } else {
                    if (!is_array($attr)) {
                        continue;
                    }
                    $leadAttr = new SphereAdditionForms($attr);
                    $group->leadAttr()->save($leadAttr);
                }

                $eoptions = array();
                if (isset($attr['option'])) {
                    if (isset($attr['option']['id'])) {
                        $attr['option'] = [$attr['option']];
                    }
                    $eoptions = $attr['option'];
                }

                if (count($eoptions)) {

                    $new_options = [];
                    for ($i = 0; $i < count($eoptions); $i++) {
                        if ($eoptions[$i]['id']) $new_options[] = $eoptions[$i]['id'];
                    }

                    $old_options = $leadAttr->options()->lists('id')->all();
                    if ($deleted = (array_diff($old_options, $new_options))) {
                        $leadAttr->options()->whereIn('id', $deleted)->delete();
                    }

                    foreach ($eoptions as $optVal) {
                        if ($optVal['id']) {
                            $chr_options = AdditionFormsOptions::find($optVal['id']);
//                            $chr_options->ctype = 'lead';
                            $chr_options->_type = 'option';
                            $chr_options->name = $optVal['val'];
                            //$chr_options->value = (isset($optVal['vale'])) ? $optVal['vale'] : NULL;
                            $chr_options->save();
                        } else {
                            $chr_options = new AdditionFormsOptions();
//                            $chr_options->ctype = 'lead';
                            $chr_options->_type = 'option';
                            $chr_options->name = $optVal['val'];
                            //$chr_options->value = (isset($optVal['vale'])) ? $optVal['vale'] : NULL;

                            $leadAttr->options()->save($chr_options);

                            // добавление нового столбца в таблицу agent_bitmask_X
                            $leadBitmask->addAttr($leadAttr->id, $chr_options->id);

                            if (isset($optVal['parent'])) {
                                // копирование атрибутов
                                $agentBitmask->copyAttr
                                (
                                    $leadAttr->id,
                                    $chr_options->id,
                                    /*parent*/
                                    $optVal['parent']
                                );
                            }
                        }
                    }
                }


                $eoptions = array();
                if (isset($attr['validate'])) {
                    if (isset($attr['validate']['id'])) {
                        $attr['validate'] = [$attr['validate']];
                    }
                    $eoptions = $attr['validate'];
                }

                if (count($eoptions)) {

                    $new_options = [];
                    for ($i = 0; $i < count($eoptions); $i++) {
                        if ($eoptions[$i]['id']) $new_options[] = $eoptions[$i]['id'];
                    }

                    $old_options = $leadAttr->options()->lists('id')->all();
                    if ($deleted = (array_diff($old_options, $new_options))) {
                        $leadAttr->options()->whereIn('id', $deleted)->delete();
                    }

                    foreach ($eoptions as $optVal) {
                        if ($optVal['id']) {
                            $chr_options = AdditionFormsOptions::find($optVal['id']);
//                            $chr_options->ctype = 'lead';
                            $chr_options->_type = 'validate';
                            $chr_options->name = $optVal['val'];
                            $chr_options->value = (isset($optVal['vale'])) ? $optVal['vale'] : NULL;
                            $chr_options->save();
                        } else {
                            $chr_options = new AdditionFormsOptions();
//                            $chr_options->ctype = 'lead';
                            $chr_options->_type = 'validate';
                            $chr_options->name = $optVal['val'];
                            $chr_options->value = (isset($optVal['vale'])) ? $optVal['vale'] : NULL;

                            $leadAttr->validators()->save($chr_options);
                        }
                    }
                }
            }

            $data = $request->only('threshold');
            $new_chr = $data['threshold']['data'];
            $rId = [];
            if ($new_chr) {
                $nId = [];
                foreach ($new_chr as $i => $attr) {
                    if (isset($attr['id'])) {
                        $nId[] = $attr['id'];
                    }
                }
                $oId = $group->statuses()->lists('id')->all();
                $rId = array_diff($oId, $nId);
                if (count($rId)) {
                    $group->statuses()->whereIn('id', $rId)->delete();
                }
            } else {
                $group->statuses()->delete();
            }

            if ($new_chr) foreach ($new_chr as $attr) {
                if (isset($attr['id']) && $attr['id']) {
                    if (!in_array($attr['id'], $rId)) {
                        $status = SphereStatuses::find($attr['id']);
                        $status->stepname = $attr['val'];
                        $status->minmax = $attr['vale'][0];
                        $status->percent = $attr['vale'][1];
                        $status->position = (isset($attr['position'])) ? $attr['position'] : 0;
                        $status->save();
                    }
                } else {
                    if (!is_array($attr)) {
                        continue;
                    }
                    $status = new SphereStatuses();
                    $status->stepname = $attr['val'];
                    $status->minmax = $attr['vale'][0];
                    $status->percent = $attr['vale'][1];
                    $status->position = (isset($attr['position'])) ? $attr['position'] : 0;
                    $group->statuses()->save($status);
                }
            }

            $data = $request->only('cform');
            $new_chr = $data['cform']['data']['variables'];
            if ($new_chr) foreach ($new_chr as $index => $characteristic) {
                if (isset($characteristic['_status'])) {
                    if ($characteristic['_status'] == 'DELETE') {
                        $group->attributes()->where('id', '=', $characteristic['id'])->delete();

                        // удаление столбца
                        $leadBitmask->removeAttr([(int)$characteristic['id']], null);
                        unset($new_chr[$index]);
                    }
                }
            }

            if ($new_chr) foreach ($new_chr as $attr) {
                if (isset($attr['id']) && $attr['id']) {
                    $characteristic = SphereFormFilters::find($attr['id']);
                    $characteristic->update($attr);
                } else {
                    if (!is_array($attr)) {
                        continue;
                    }
                    $characteristic = new SphereFormFilters((array)$attr);
                    $group->attributes()->save($characteristic);
                }
                if (isset($attr['option'])) {
                    $new_options = [];
                    if (isset($attr['option']['id'])) {
                        $attr['option'] = [$attr['option']];
                    }
                    for ($i = 0; $i < count($attr['option']); $i++) {
                        if ($attr['option'][$i]['id']) $new_options[] = $attr['option'][$i]['id'];
                    }

                    $old_options = $characteristic->options()->lists('id')->all();
                    if ($deleted = (array_diff($old_options, $new_options))) {
                        $characteristic->options()->whereIn('id', $deleted)->delete();

                        // Удаление столбца из таблицы agent_bitmask
                        $agentBitmask->removeAttr($characteristic->id, $deleted);
                    }

                    $default_value = [];
                    foreach ($attr['option'] as $optVal) {
                        if ($optVal['id']) {
                            $chr_options = FormFiltersOptions::find($optVal['id']);
//                            $chr_options->ctype = 'agent';
                            $chr_options->name = $optVal['val'];
                            //$chr_options->icon = (isset($optVal['vale'][1])) ? $optVal['vale'][1] : NULL;
                            $chr_options->save();
                        } else {
                            $chr_options = new FormFiltersOptions();
//                            $chr_options->ctype = 'agent';
                            $chr_options->name = $optVal['val'];
                            //$chr_options->icon = (isset($optVal['vale'][1])) ? $optVal['vale'][1] : NULL;
                            $characteristic->options()->save($chr_options);

                            // добавление нового столбца в таблицу agent_bitmask_X
                            $agentBitmask->addAttr($characteristic->id, $chr_options->id);

                            if (isset($optVal['parent'])) {
                                // копирование атрибутов
                                $agentBitmask->copyAttr
                                (
                                    $characteristic->id,
                                    $chr_options->id,
                                    /*parent*/
                                    $optVal['parent']
                                );
                            }
                        }
                        $default_value[$chr_options->id] = (isset($optVal['vale'][0]) && $optVal['vale'][0]) ? 1 : 0;
                    }

                    // Установка поля в положение по умолчанию (1)
                    $agentBitmask->setDefault($characteristic->id, $default_value);
                    $characteristic->default_value = implode('', array_values($default_value));
                    $characteristic->save();
                }
            }
        }
//        return response()->json(TRUE);
// todo убрать
        return $request->all();
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