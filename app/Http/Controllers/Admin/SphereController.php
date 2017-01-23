<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Agent;
use App\Models\AgentBitmask;
use App\Models\AgentSphere;
use App\Models\FormFiltersOptions;
use App\Models\AdditionFormsOptions;

use App\Models\LeadBitmask;
use App\Models\UserMasks;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\Sphere;
use App\Models\Auction;
use App\Models\SphereStatusTransitions;


use App\Models\SphereStatuses;
use App\Models\SphereFormFilters;
use App\Models\SphereAdditionalNotes;

use App\Models\SphereAdditionForms;
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
                "button"=>trans('admin/sphere.button_add_field')
            ]
        ];
        $lead = [
            "renderType"=>"dynamicForm",
            "id"=>null,
            "targetEntity"=>"SphereAdditionForms",
            "values"=>[],
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
                "button"=>trans('admin/sphere.button_add_field')
            ]
        ];

        $month = $days = $hours = $minutes = array();

        for($i = 0; $i <= 60; $i++) {
            if ($i < 13) {
                $month[] = ['key'=>$i,'value'=>$i];
            }
            if ($i < 32) {
                $days[] = ['key'=>$i,'value'=>$i];
            }
            if ($i < 25) {
                $hours[] = ['key'=>$i,'value'=>$i];
            }
            if ($i < 61) {
                $minutes[] = ['key'=>$i,'value'=>$i];
            }
        }

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
                        "required" => "required"
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.sphere_name'),
                        "type"=>'text'
                    ]
                ],
                'price_call_center'=>[
                    "renderType"=>"single",
                    'name' => 'price_call_center',
                    'values'=>'',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                        "data-integer"=>true
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.price_call_center'),
                        "type"=>'text'
                    ]
                ],
                "openLead"=>
                    [
                        "renderType"=>"single",

                        'name' => 'openLead',

                        "values"=>3,

                        "attributes" =>
                            [
                                "type"=>'text',
                                "class" => 'form-control',
                                "data-integer"=>true,
                            ],

                        "settings"=>
                            [
                                "label" => trans('admin/sphere.max_lead'),
                                "type"=>'text',
                            ],
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
                        "label" => trans('admin/sphere.status'),
                        "type"=>'select',
                        'option'=>[['key'=>1,'value'=>'on'],['key'=>0,'value'=>'off']],
                    ]
                ],

                // Селекты для выбора времени жизни лида на аукционе

                "lead_auction_expiration_interval_label"=>[
                    "renderType"=>"single",
                    'name' => '',
                    'values'=>'',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.lead_auction_expiration_interval'),
                        "type"=>'label',
                        'option'=>'',
                    ]
                ],

                "lead_auction_expiration_interval_month"=>[
                    "renderType"=>"single",
                    'name' => 'lead_auction_expiration_interval_month',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.month'),
                        "type"=>'select',
                        'option'=>$month,
                    ]
                ],

                "lead_auction_expiration_interval_days"=>[
                    "renderType"=>"single",
                    'name' => 'lead_auction_expiration_interval_days',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.days'),
                        "type"=>'select',
                        'option'=>$days,
                    ]
                ],

                "lead_auction_expiration_interval_hours"=>[
                    "renderType"=>"single",
                    'name' => 'lead_auction_expiration_interval_hours',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.hours'),
                        "type"=>'select',
                        'option'=>$hours,
                    ]
                ],

                "lead_auction_expiration_interval_minutes"=>[
                    "renderType"=>"single",
                    'name' => 'lead_auction_expiration_interval_minutes',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.minutes'),
                        "type"=>'select',
                        'option'=>$minutes,
                    ]
                ],

                // END: Селекты для выбора времени жизни лида на аукционе

                // Селекты для выбора времени за которое можно поставить статус bad
                "lead_bad_status_interval_label"=>[
                    "renderType"=>"single",
                    'name' => 'lead_bad_status_interval_month',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.lead_bad_status_interval'),
                        "type"=>'label',
                        'option'=>$month,
                    ]
                ],

                "lead_bad_status_interval_month"=>[
                    "renderType"=>"single",
                    'name' => 'lead_bad_status_interval_month',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.month'),
                        "type"=>'select',
                        'option'=>$month,
                    ]
                ],

                "lead_bad_status_interval_days"=>[
                    "renderType"=>"single",
                    'name' => 'lead_bad_status_interval_days',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.days'),
                        "type"=>'select',
                        'option'=>$days,
                    ]
                ],

                "lead_bad_status_interval_hours"=>[
                    "renderType"=>"single",
                    'name' => 'lead_bad_status_interval_hours',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.hours'),
                        "type"=>'select',
                        'option'=>$hours,
                    ]
                ],

                "lead_bad_status_interval_minutes"=>[
                    "renderType"=>"single",
                    'name' => 'lead_bad_status_interval_minutes',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.minutes'),
                        "type"=>'select',
                        'option'=>$minutes,
                    ]
                ],
                // END: Селекты для выбора времени за которое можно поставить статус bad

                'max_range'=>[
                    "renderType"=>"single",
                    'name' => 'max_range',
                    'values'=>'',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control'
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.max_range'),
                        "type"=>'text'
                    ]
                ],

                // Период времени, по истечению которого лиды на аукционе будут доступны агенту рангом ниже
                "range_show_lead_interval_label"=>[
                    "renderType"=>"single",
                    'name' => 'range_show_lead_interval_month',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.range_show_lead_interval'),
                        "type"=>'label',
                        'option'=>$month,
                    ]
                ],

                "range_show_lead_interval_month"=>[
                    "renderType"=>"single",
                    'name' => 'range_show_lead_interval_month',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.month'),
                        "type"=>'select',
                        'option'=>$month,
                    ]
                ],

                "range_show_lead_interval_days"=>[
                    "renderType"=>"single",
                    'name' => 'range_show_lead_interval_days',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.days'),
                        "type"=>'select',
                        'option'=>$days,
                    ]
                ],

                "range_show_lead_interval_hours"=>[
                    "renderType"=>"single",
                    'name' => 'range_show_lead_interval_hours',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.hours'),
                        "type"=>'select',
                        'option'=>$hours,
                    ]
                ],

                "range_show_lead_interval_minutes"=>[
                    "renderType"=>"single",
                    'name' => 'range_show_lead_interval_minutes',
                    'values'=>'',
                    'group_class'=>'select-group',
                    "attributes" => [
                        "type"=>'text',
                        "class" => 'form-control',
                    ],
                    "settings"=>[
                        "label" => trans('admin/sphere.minutes'),
                        "type"=>'select',
                        'option'=>$minutes,
                    ]
                ],
                // END: Период времени, по истечению которого лиды на аукционе будут доступны агенту рангом ниже
            ],
        ];

        $threshold = [
            "renderType"=>"single",
            'name' => 'status',
//            'values'=>'',

            "attributes" =>
            [
                "type"=>'text',
                "class" => 'form-control',
            ],

            "values"=>
            [
                1 => [],
                2 => [],
                3 => [],
                4 => [],

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

        $statusTransitions = SphereStatusTransitions::where('sphere_id', $id)->get();

        $notes = [];

        if($id) {
            $group = Sphere::find($id);
            $data['id']=$id;
            $settings['variables']['name']['values'] = $group->name;
            $settings['variables']['status']['values'] = $group->status;
            $settings['variables']['openLead']['values'] = $group->openLead;

            $now = Carbon::now();
            $auction_expiration = Carbon::createFromTimestamp(time() + $group->lead_auction_expiration_interval);

            $month = $auction_expiration->diffInMonths($now);
            $days = $auction_expiration->subMonth($month)->diffInDays($now);
            $hours = $auction_expiration->subDays($days)->diffInHours($now);
            $minutes = $auction_expiration->subHours($hours)->diffInMinutes($now);

            $settings['variables']['lead_auction_expiration_interval_month']['values'] = $month;
            $settings['variables']['lead_auction_expiration_interval_days']['values'] = $days;
            $settings['variables']['lead_auction_expiration_interval_hours']['values'] = $hours;
            $settings['variables']['lead_auction_expiration_interval_minutes']['values'] = $minutes;

            $now = Carbon::now();
            $bad_expiration = Carbon::createFromTimestamp(time() + $group->lead_bad_status_interval);

            $month = $bad_expiration->diffInMonths($now);
            $days = $bad_expiration->subMonth($month)->diffInDays($now);
            $hours = $bad_expiration->subDays($days)->diffInHours($now);
            $minutes = $bad_expiration->subHours($hours)->diffInMinutes($now);

            $settings['variables']['lead_bad_status_interval_month']['values'] = $month;
            $settings['variables']['lead_bad_status_interval_days']['values'] = $days;
            $settings['variables']['lead_bad_status_interval_hours']['values'] = $hours;
            $settings['variables']['lead_bad_status_interval_minutes']['values'] = $minutes;

            $settings['variables']['price_call_center']['values'] = $group->price_call_center;

            $settings['variables']['max_range']['values'] = $group->max_range;

            $now = Carbon::now();
            $range_expiration = Carbon::createFromTimestamp(time() + $group->range_show_lead_interval);

            $month = $range_expiration->diffInMonths($now);
            $days = $range_expiration->subMonth($month)->diffInDays($now);
            $hours = $range_expiration->subDays($days)->diffInHours($now);
            $minutes = $range_expiration->subHours($hours)->diffInMinutes($now);

            $settings['variables']['range_show_lead_interval_month']['values'] = $month;
            $settings['variables']['range_show_lead_interval_days']['values'] = $days;
            $settings['variables']['range_show_lead_interval_hours']['values'] = $hours;
            $settings['variables']['range_show_lead_interval_minutes']['values'] = $minutes;


            //$settings['variables']['pending_time']['values'] = $group->pending_time;
            //$settings['variables']['pending_type']['values'] = $group->pending_type;

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
                        $arr['option'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value, 'position'=>$eav->position];
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
                        $arr['option'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value, 'position'=>$eav->position];
                    }
                }

                $arr['validate']=[];
                if($chrct->has('validators')) {
//                    $arr['option']=[];
                    foreach($chrct->validators()->get() as $eav) {
                        $arr['validate'][]=['id'=>$eav->id,'val'=>$eav->name,'vale'=>$eav->value];
                    }
                }
                $lead['values'][]=$arr;
//                dd($chrct->validators()->get());
            }

//            if($group->has('statuses')) { $threshold['values']=array(); }
            foreach($group->statuses()->get() as $chrct) {
                $arr=[];
//                $arr['id'] = $chrct->id;
//                $arr['val'] = $chrct->stepname;
//                $arr['vale'] = [$chrct->minmax,$chrct->percent];
//                $arr['position'] = $chrct->position;

                $arr['id'] = $chrct->id;
                $arr['type'] = $chrct->type;
                $arr['stepname'] = $chrct->stepname;
                $arr['comment'] = $chrct->comment;
                $arr['position'] = $chrct->position;


                $threshold['values'][ $arr['type'] ][]=$arr;
            }
            $threshold['settings']['stat']['minLead']=$group->minLead;

            // добавление заметок в массив данных сферы
            foreach($group->additionalNotes()->get() as $dbNote) {
                $arr=[];
                $arr['id'] = $dbNote->id;
                $arr['note'] = $dbNote->note;
                $notes[]=$arr;
            }


        }

        $data=['opt'=>$settings,"cform"=>$data,'lead'=>$lead,'threshold'=>$threshold, 'notes'=>$notes, 'statusTransitions'=>$statusTransitions];
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
         *        |`pending_time <-- промежуток времени, за который можно поставить статус bad lead
         *        |`pending_type <-- тип pending_time (0 - минуты, 1 - часы, 2 - дни)
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
        // если Request пустой, возвращаем сообщение с ошибкой
        if( !count($request->all()) ) { return response()->json( ['error'=>trans('admin/sphere.errors.required.name')] ); }

        // данные формы сферы
        $sphereData = $request['opt'];

        // данные формы лида
        $leadData = $request['lead'];

        // данные формы агента
        $agentData = $request['cform'];

        // данные агента
        $agentDataAttr = isset($agentData['values']) ? $agentData['values'] : false;

        // данные лида
        $leadDataAttr = isset($leadData['values']) ? $leadData['values'] : false;

        // минимальное количество лидов
        $minLead = $request['threshold']['settings']['stat']['minLead'];

        // статусы
        $statusData = ($request['threshold']) ? collect( $request['threshold'] ) : FALSE;

        $statusTransitions = collect($request['statusTransitions']);

        // заметки по сфере
        $notes = ( count($request['notes']) != 0) ? collect($request['notes']) : collect();


        /** ----- КОНЕЦ ОБРАБОТКИ ДАННЫХ ПОЛУЧЕННЫХ С ФРОНТЕНДА ---------- */

        // Считаем интервалы времени прибывания лида на укционе и времени на статус bad

        $months = $sphereData['variables']['lead_auction_expiration_interval_month']['values'];
        $days = $sphereData['variables']['lead_auction_expiration_interval_days']['values'];
        $hours = $sphereData['variables']['lead_auction_expiration_interval_hours']['values'];
        $minutes = $sphereData['variables']['lead_auction_expiration_interval_minutes']['values'];

        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $interval_auction = $now->addMinutes($minutes)->addHours($hours)->addDays($days)->addMonths($months);
        $interval_auction = $interval_auction->timestamp - $timestamp;

        $months = $sphereData['variables']['lead_bad_status_interval_month']['values'];
        $days = $sphereData['variables']['lead_bad_status_interval_days']['values'];
        $hours = $sphereData['variables']['lead_bad_status_interval_hours']['values'];
        $minutes = $sphereData['variables']['lead_bad_status_interval_minutes']['values'];

        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $interval_bad = $now->addMinutes($minutes)->addHours($hours)->addDays($days)->addMonths($months);
        $interval_bad = $interval_bad->timestamp - $timestamp;

        $months = $sphereData['variables']['range_show_lead_interval_month']['values'];
        $days = $sphereData['variables']['range_show_lead_interval_days']['values'];
        $hours = $sphereData['variables']['range_show_lead_interval_hours']['values'];
        $minutes = $sphereData['variables']['range_show_lead_interval_minutes']['values'];

        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $interval_range = $now->addMinutes($minutes)->addHours($hours)->addDays($days)->addMonths($months);
        $interval_range = $interval_range->timestamp - $timestamp;

        /**
         * Выбираем сферу по id, либо, создаем новую
         *
         */
        if($id) {
            $sphere = Sphere::find($id);
            $sphere->name = $sphereData['variables']['name']['values'];
            $sphere->minLead = $minLead;
            $sphere->status = $sphereData['variables']['status']['values'];
            $sphere->openLead = $sphereData['variables']['openLead']['values'];
            $sphere->lead_auction_expiration_interval = $interval_auction;
            $sphere->lead_bad_status_interval = $interval_bad;
            $sphere->range_show_lead_interval = $interval_range;
            $sphere->price_call_center = $sphereData['variables']['price_call_center']['values'];
            $sphere->max_range = $sphereData['variables']['max_range']['values'];
        } else {
            $sphere = new Sphere();
            $sphere->name = $sphereData['variables']['name']['values'];
            $sphere->minLead = $minLead;
            $sphere->status = $sphereData['variables']['status']['values'];
            $sphere->openLead = $sphereData['variables']['openLead']['values'];
            $sphere->lead_auction_expiration_interval = $interval_auction;
            $sphere->lead_bad_status_interval = $interval_bad;
            $sphere->range_show_lead_interval = $interval_range;
            $sphere->price_call_center = $sphereData['variables']['price_call_center']['values'];
            $sphere->max_range = $sphereData['variables']['max_range']['values'];
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
         *
         * если у формы лида заданны атрибуты
         * перебираем их и обрабатываем
         */
        if($leadDataAttr){

            // преобразовываем массив в коллекцию
            $leadDataAttr = collect($leadDataAttr);
            // создаем/обновляем атрибут с его опциями
            $leadDataAttr->each(function( $attr )  use( $sphere, &$leadBitmask ){


            // СОЗДАЕМ НОВЫЙ АТРИБУТ, ОБНОВЛЯЕМ УЖЕ СУЩЕСТВУЮЩИЙ ЛИБО УДАЛЯЕМ

                // если у атрибуте есть id и он НЕ равен '0'
                if (isset($attr['id']) && $attr['id']) {

                    // выбор действия над атрибутом, либо удаляется, либо обновляется
                    if( isset($attr['delete']) ){
                        // если задан 'delete' - удаляем атрибут из БД

                        // выбираем атрибут по его id
                        $dbAttribute = $sphere->leadAttr()->where('id', '=', $attr['id']);

                        // удаление всех опций атрибутов
                        $dbAttribute->each(function($option){
                            $option->allFormsOptions()->delete();
                        });

                        // удаление атрибута
                        $dbAttribute->delete();

                        // удаление полей в маске атрибута
                        $leadBitmask->removeAd($attr['id'], null);

                        // останавливаем дальнейшую обработку
                        return true;

                    }else{
                        // обновляем значение атрибута

                        // выбираем его
                        $leadAttr = SphereAdditionForms::find($attr['id']);
                        // и обновляем
                        $leadAttr->update($attr);
                    }


                } else {
                    // если атрибута нет или он равен 0 создаем его
                    $leadAttr = new SphereAdditionForms($attr);
                    $sphere->leadAttr()->save($leadAttr);

                    // для атрибутов с типами: input, textArea, email и calendar
                    // в битмаске создается поле только с индексом атрибута
                    // вместо индекса опции ставится "0"
                    // ad_attrId_0 ( ad_54_0 )
                    if( ($attr['_type']=='textarea') || ($attr['_type']=='input') || ($attr['_type']=='email') || ($attr['_type']=='calendar') ){
                        $leadBitmask->addAb($leadAttr->id, 0, $leadAttr->_type);
                    }

                }


            // ДЕЙСТВИЯ НАД ОПЦИЯМИ АТРИБУТА В ЗАВИСИМОСТИ ОТ ЕГО ТИПА
            //    - типы 'checkbox','radio', 'select'
            //    - типы 'textarea', 'input'
            //    - типы 'email', 'calendar'

                if( ($attr['_type']=='checkbox') || ($attr['_type']=='radio') || ($attr['_type']=='select') ){
                    // обработка атрибутов с типом 'checkbox','radio', 'select'
                    // у этого атрибута должна быть хотя бы одна опция
                    // в начале метода стоит проверка,
                    // если опций нет - метод вернет ошибку еще в начале (до этого места не дойдет)

                    if( isset($attr['option']) ){

                        // перебираем все опции и либо создаем новую,
                        // либо обновляем существующую запись опции
                        $optionCollection = collect($attr['option']);
                        $optionCollection->each(function( $option ) use ( $attr, &$leadAttr, &$leadBitmask ){

                            if($option['id']){
                                // у опции ЕСТЬ id, т.е. опция уже есть в БД

                                if(isset($option['delete'])){
                                    // удаление опции атрибута

                                    // удаляем опцию в маске лида
                                    $leadBitmask->removeAd($leadAttr->id, $option['id']);

                                    // удаляем опцию в таблице
                                    AdditionFormsOptions::where('id', $option['id'])->delete();

                                }else {
                                    // выбираем данные опции из БД
                                    $dbOption = AdditionFormsOptions::find($option['id']);
                                    // присваиваем опции новые значения
                                    $dbOption->_type = 'option';
                                    $dbOption->name = $option['val'];
                                    // $dbOption->value = (isset($option['vale'])) ? $option['vale'] : '';
                                    // todo добавить позиционирование и vale
                                    $dbOption->value = 0;

                                    $dbOption->position = $option['position'];

                                    // сохраняем
                                    $dbOption->save();
                                }

                            }else{
                                // у опции НЕТ id
                                // (создание новой зписи и полей в битмаске соответственно)

                                // создание новой опции
                                $newOption = new AdditionFormsOptions();
                                // присваиваем опции новые значения
                                $newOption->_type = 'option';
                                $newOption->name = $option['val'];
                                // todo добавить позиционирование и vale
                                $newOption->value = 0;
                                // сохраняем
                                $leadAttr->options()->save($newOption);

                                // создаем новый столбец "ad_" в БД
                                $leadBitmask->addAb($leadAttr->id, $newOption->id, $leadAttr->_type);

                                /** копирование опции атрибута */
                                if (isset($option['parent'])) {
                                    // копирование атрибутов
                                    $leadBitmask->copyAttr
                                    (
                                        $leadAttr->id,
                                        $newOption->id,
                                        /*parent*/
                                        $option['parent']
                                    );
                                }
                            }
                        });
                    }

                }elseif( ($attr['_type']=='textarea') || ($attr['_type']=='input') ){
                    // обработка атрибутов с типом 'textarea' и 'input'
                    // атрибут этого типа может иметь валидации, а может и не иметь
                    // если валидации есть - они записываются в таблицу опций,
                    // если нет - НЕ записываются

                    if( isset($attr['validate']) ) {
                        // у атрибута есть поле валидации

                        $validateCollection = collect( $attr['validate'] );

                        // перебираем все валидации и либо создаем новую,
                        // либо обновляем существующую запись валидации
                        $validateCollection->each(function ($validate) use (&$leadAttr) {

                            if ($validate['id']) {
                                // у валидации ЕСТЬ id, т.е. валидация уже есть в БД

                                if(isset($validate['delete'])) {
                                    // удаление опции атрибута

                                    // удаляем опцию в таблице
                                    AdditionFormsOptions::where('id', $validate['id'])->delete();

                                }else {
                                    // (обновляем существующую запись)

                                    // выбираем данные валидации из БД
                                    $dbValidate = AdditionFormsOptions::find($validate['id']);
                                    // присваиваем валидации новые значения
                                    $dbValidate->_type = 'validate';
                                    $dbValidate->name = $validate['val'];
                                    $dbValidate->value = $validate['vale'];

                                    // сохраняем
                                    $dbValidate->save();
                                }

                            } else {
                                // у валидации НЕТ id
                                // (создание новой зписи)

                                // создание новой валидации
                                $newValidate = new AdditionFormsOptions();
                                // присваиваем валидации новые значения
                                $newValidate->_type = 'validate';
                                $newValidate->name = $validate['val'];
                                $newValidate->value = $validate['vale'];
                                // сохраняем
                                $leadAttr->validators()->save($newValidate);
                            }
                        });
                    }
                }

                return true;
            });
        }


        /**
         * Обработка атрибутов агента
         */
        if($agentDataAttr){

            // преобразовываем массив в коллекцию
            $agentDataAttr = collect($agentDataAttr);
            // перебираем все атрибуты, создаем/обновляем его данные
            $agentDataAttr->each(function( $attr )  use( $sphere, &$agentBitmask, &$leadBitmask ) {


            // СОЗДАЕМ НОВЫЙ АТРИБУТ, ОБНОВЛЯЕМ УЖЕ СУЩЕСТВУЮЩИЙ ЛИБО УДАЛЯЕМ

                // если у атрибуте есть id и он НЕ равен '0'
                if (isset($attr['id']) && $attr['id']) {


                    // выбор действия над атрибутом, либо удаляется, либо обновляется
                    if( isset($attr['delete']) ){
                        // если задан 'delete' - удаляем атрибут из БД

                        // выбираем атрибут по его id
                        $attribute = $sphere->attributes()->where('id', '=', $attr['id']);

                        // удаление всех опций атрибутов
                        $attribute->each(function( $attr ){
                            $attr->options()->delete();
                        });

                        // удаление атрибута
                        $attribute->delete();

                        // удаление полей в битмаске агента и лида
                        $agentBitmask->removeAttr($attr['id'], null);
                        $leadBitmask->removeAttr($attr['id'], null);

                        // останавливаем дальнейшую обработку
                        return true;

                    }else{
                        // обновляем значение атрибута

                        // выбираем его
                        $agentAttr = SphereFormFilters::find($attr['id']);
                        // и обновляем
                        $agentAttr->update($attr);
                    }

                } else {
                    // если атрибута нет или он равен 0 создаем его
                    $agentAttr = new SphereFormFilters($attr);
                    $sphere->attributes()->save($agentAttr);
                }


            // ОБРАБОТКА ОПЦИЙ АТРИБУТА (предполагается что у атрибута есть только опции, валидаций и прочего нет)

                if (isset($attr['option'])) {
                    // контрольная проверка наличия опций

                    // перебираем все опции и либо создаем новую,
                    // либо обновляем существующую запись опции
                    // или удаляем, если есть указание на удаление
                    $optionCollection = collect($attr['option']);
                    $optionCollection->each(function ($option) use (&$agentAttr, &$agentBitmask, &$leadBitmask) {

                        if ( $option['id'] ) {
                            // у опции ЕСТЬ id, т.е. опция уже есть в БД

                            if(isset($option['delete'])){

                                // удаляем опцию в масках лида и агента
                                $agentBitmask->removeAttr($agentAttr->id, $option['id']);
                                $leadBitmask->removeAttr($agentAttr->id, $option['id']);

                                // удаляем опцию в таблице
                                FormFiltersOptions::where('id', $option['id'])->delete();

                            }else{
                                // выбираем данные опции из БД
                                $dbOption = FormFiltersOptions::find($option['id']);
                                // присваиваем опции новые значения
                                $dbOption->name = $option['val'];
                                $dbOption->value = $option['vale'];
                                $dbOption->position = $option['position'];
                                // сохраняем
                                $dbOption->save();
                            }

                        } else {
                            // у опции НЕТ id
                            // (создание новой зписи и полей в битмаске соответственно)

                            // создание новой опции
                            $newOption = new FormFiltersOptions();
                            // присваиваем опции новые значения
                            $newOption->name = $option['val'];
                            $newOption->value = ($option['vale']) ? 1 : 0;
                            $newOption->position = $option['position'];
                            // сохраняем
                            $agentAttr->options()->save($newOption);

                            // создаем новые столбцы в битмасе агента и лида
                            $agentBitmask->addAttrWithType($agentAttr->id, $newOption->id, 'boolean', $newOption->value);
                            $leadBitmask->addAttrWithType($agentAttr->id, $newOption->id, 'boolean', $newOption->value);


                            /** копирование данных, если опция была создана ветвлением */
                            if (isset($option['parent'])) {
                                // копирование атрибутов
                                $agentBitmask->copyAttr
                                (
                                    $agentAttr->id,
                                    $newOption->id,
                                    /*parent*/
                                    $option['parent']
                                );

                                $leadBitmask->copyAttr
                                (
                                    $agentAttr->id,
                                    $newOption->id,
                                    /*parent*/
                                    $option['parent']
                                );
                            }
                        }
                    });

                } else {
                    $sphere->status = 0;
                    $sphere->save();
                }

                return true;
            });
        }


        // массив соответствий внешнего статуса статусу по БД
        $statusOuterId = [ 0=>0 ];


        /**
         * Обработка статусов
         */
        if($statusData){
        // СОЗДАЕМ НОВЫЙ СТАТУС ЛИБО ОБНОВЛЯЕМ УЖЕ СУЩЕСТВУЮЩИЙ, ИЛИ УДАЛЯЕМ, ЕСЛИ ЕСТЬ УКАЗАНИЕ НА УДАЛЕНИЕ

            // в эту переменную собираются id статусов сферы, которые нужно удалить
            $deleteId = false;

            // преобразовываем данные в коллекцию
            $collectStatusData = collect($statusData['values']);

            // перебираем все типы статусов, создаем/обновляем его данные
            $collectStatusData->each(function($statusesTypes) use( $sphere, &$deleteId, &$statusOuterId ){

                // преобразовываем статусы в коллекцию
                $statusesTypes = collect($statusesTypes);
                // обрабатываем каждый статус
                $statusesTypes->each(function($status) use( $sphere, &$deleteId, &$statusOuterId ){

                    // если у атрибуте есть id и он НЕ равен '0'
                    if (isset($status['id']) && $status['id']) {

                        // проверка на удаление, если нет указание на удаление - просто сохраняем новые данные
                        if( isset( $status['delete'] ) ){
                            // если задан элемент delete (указание на удаление)

                            // заносим id статуса в список на удаление
                            // после всего цикла все элементы из этого списка будут удалены
                            $deleteId[] = $status['id'];

                        }else{
                            // выбираем его
                            $dbStatus = SphereStatuses::find($status['id']);

                            // заносим туда данные
                            $dbStatus->stepname = $status['stepname'];
                            $dbStatus->type = $status['type'];
                            $dbStatus->comment = $status['comment'];
                            $dbStatus->position = (isset($status['position'])) ? $status['position'] : 0;

                            // и обновляем
                            $dbStatus->save();

                            // записываем соответствие внешнего id  и id статуса в массив $statusOuterId
                            $statusOuterId[ $status['outerId'] ] = $dbStatus->id;
                        }

                    } else {
                        // если атрибута нет или он равен 0 создаем его
                        $newStatus = new SphereStatuses();
                        $newStatus->stepname = $status['stepname'];
                        $newStatus->type = $status['type'];
                        $newStatus->comment = $status['comment'];
                        $newStatus->position = (isset($status['position'])) ? $status['position'] : 0;

                        $statusData = $sphere->statuses()->save($newStatus);

                        // записываем соответствие внешнего id  и id статуса в массив $statusOuterId
                        $statusOuterId[ $status['outerId'] ] = $statusData->id;
                    }
                });
            });

            // если есть статусы сферы для удаления
            if($deleteId){
                // удаляем статусы
                SphereStatuses::whereIn('id', $deleteId)->delete();
                // удаляем все транзиты в которых этот статус указан как предыдущий
                SphereStatusTransitions::whereIn('previous_status_id', $deleteId)->delete();
                // удаляем все транзиты в которых этот статус указан как текущий статус
                SphereStatusTransitions::whereIn('status_id', $deleteId)->delete();
            }
        }


        // в эту переменную собираются удаленные транзиты статусов
        $transitionsDeletedId = false;

        /**
         * Обработка транзитов по статусам
         */
        $statusTransitions->each(function($transit) use ($statusOuterId, $sphere, &$transitionsDeletedId){

            // проверяем существует ли уже запись с таким транзитом
            if($transit['id'] != 0 ){
                // если существует

                // проверяем на пометку на удаление
                if( isset($transit['delete']) ){
                    // если есть пометка на удаление

                    // заносим id транзита в список на удаление
                    // после всего цикла все элементы из этого списка будут удалены
                    $transitionsDeletedId[] = $transit['id'];

                }else{
                    // если пометки на удаление нет

                    // обновляем запись транзита статусов
                    $dbTransit = SphereStatusTransitions::find($transit['id']);

                    $dbTransit->position = $transit['position'];

                    $dbTransit->transition_direction = $transit['transition_direction'];
                    $dbTransit->rating_1 = $transit['rating_1'];
                    $dbTransit->rating_2 = $transit['rating_2'];
                    $dbTransit->rating_3 = $transit['rating_3'];
                    $dbTransit->rating_4 = $transit['rating_4'];
                    $dbTransit->rating_5 = $transit['rating_5'];
                    $dbTransit->save();
                }

            }else{
                // если не существует

                $newTransit = new SphereStatusTransitions();
                $newTransit->sphere_id = $sphere->id;
                $newTransit->previous_status_id = $statusOuterId[ $transit['outer_previous_status_id'] ];
                $newTransit->status_id = $statusOuterId[ $transit['outer_status_id'] ];

                $newTransit->position = $transit['position'];

                $newTransit->transition_direction = $transit['transition_direction'];
                $newTransit->rating_1 = $transit['rating_1'];
                $newTransit->rating_2 = $transit['rating_2'];
                $newTransit->rating_3 = $transit['rating_3'];
                $newTransit->rating_4 = $transit['rating_4'];
                $newTransit->rating_5 = $transit['rating_5'];

                $newTransit->save();
            }
        });


        // если есть статусы сферы для удаления
        if($transitionsDeletedId){
            // удаляем транзиты
            SphereStatusTransitions::whereIn('id', $transitionsDeletedId)->delete();
        }


        /**
         * Обработка заметок
         */
        // в эту переменную собираются id заметок, которые нужно удалить
        $deleteId = false;
        // перебираем все заметки и обрабатываем
        $notes->each(function($note) use( $sphere, &$deleteId ){

            // если у атрибуте есть id и он НЕ равен '0'
            if ( $note['id'] != 0) {

                // проверка на удаление, если нет указание на удаление - просто сохраняем новые данные
                if( isset( $note['delete'] ) ){
                    // если задан элемент delete (указание на удаление)

                    // заносим id заметки в список на удаление
                    // после всего цикла все элементы из этого списка будут удалены
                    $deleteId[] = $note['id'];

                }else{
                    // выбираем запись заметки
                    $dbNote = SphereAdditionalNotes::find($note['id']);

                    // заносим в запись новые данные
                    $dbNote->note = $note['note'];

                    // и обновляем
                    $dbNote->save();
                }

            } else {
                // если записи нет или он равен 0 создаем его
                $newNote = new SphereAdditionalNotes();
                $newNote->sphere_id = $sphere['id'];
                $newNote->note = $note['note'];
                $sphere->additionalNotes()->save($newNote);
            }
        });

        // если есть заметки для удаления
        if($deleteId){
            // удаляем их
            SphereAdditionalNotes::whereIn('id', $deleteId)->delete();
        }


        // валидация, проверка на ошибки
        // если есть ошибки, переключатель сферы переходит в off
        if( $sphere->status ) {
            $errors = $this->sphereValidate($sphere->id);

            $errorsToString = json_encode($errors);

            return response()->json( [ 'status'=>'error', 'errors' => $errorsToString, 'route'=>route('admin.sphere.edit', ['id'=>$sphere->id])] );
        }

        return response()->json([ 'status'=>'true', 'route'=>route('admin.sphere.edit', ['id'=>$sphere->id]) ]);
    }



    public function destroy($id){

        $group = Sphere::find($id);

        // удаление привязки агента к сфере
        AgentSphere::where('sphere_id', $id)->delete();

        // удаление лидов сферы с таблица аукциоан
        Auction::where( 'sphere_id', $id )->delete();

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
            ->edit_column('status', function($model) { return view('admin.sphere.datatables.status',['model'=>$model]); } )
            ->add_column('actions', function($model) { return view('admin.sphere.datatables.control',['id'=>$model->id]); } )
            ->remove_column('id')
            ->make();
    }

    public function changeStatus(Request $request)
    {
        $sphere = Sphere::find($request->input('id'));

        if((bool)$request->input('status')) {
            $errors = $this->sphereValidate($request->input('id'));

            if($errors) {
                return response()->json( [ 'errors'=>$errors, 'message'=>trans('admin/sphere.status_not_changed') ] );
            }
        }

        $sphere->status = (bool)$request->input('status');
        $sphere->save();

        return response()->json( [ 'errors'=>false, 'message'=>trans('admin/sphere.status_changed') ] );
    }

    /**
     * Проверка сферы на возможность активации
     *
     * @param $sphere_id
     * @return array|bool
     */
    private function sphereValidate($sphere_id)
    {
        $sphere = Sphere::find($sphere_id);

        $required = config('sphere.required');

        $errors = array();

        // Проверка основных полей сферы
        foreach ($required as $field => $flag) {
            if( empty($sphere[$field]) && $flag === true ) {
                $errors[$field] = trans('admin/sphere.errors.required.'.$field);
            }
        }

        // Проверка атрибутова "Agent form"
        $sphereAttr = $sphere->attributes()->get();

        if( count($sphereAttr) < config('sphere.agentForm.min_attributes') ) {
            $errors['min_attributes'] = trans('admin/sphere.errors.agentForm.min_attributes');
        }

        if( count($sphereAttr) ) {
            foreach ($sphereAttr as $attr) {
                if( $attr->has('options') ) {
                    $options = $attr->options()->get();
                    if( count($options) < config('sphere.agentForm.min_options') ) {
                        $errors['min_options'] = trans('admin/sphere.errors.agentForm.min_options');
                    }
                } else {
                    $errors['min_options'] = trans('admin/sphere.errors.agentForm.min_options');
                }
            }
        } else {
            $errors['min_options'] = trans('admin/sphere.errors.agentForm.min_options');
        }

        if( count($errors) > 0 ) {
            $sphere->status = 0;
            $sphere->save();
            return $errors;
        }

        return false;
    }


    /**
     * Вывод всех масок агентов, у которых статус = 0
     * Update: Вывод всех масок агентов, независимо от статуса
     */
    public function filtration(){

        // выбираем активные сферы
        $spheres = Sphere::active()->get();

        // все неактивные маски пользователей
        $collection = array();

        // перебираем все сферы и находим их маски
        foreach($spheres as $sphere){

            // маска по сфере
            $mask = new AgentBitmask($sphere->id);

            // добавляем в массив (с ключем id сферы) все неактинвые маски сферы с агентами масок
            $collection[$sphere->id] = $mask->where('status', '=', 0)->with('user')->get();
        }

        return view('admin.sphere.reprice')
            ->with('collection',$collection)
            ->with('spheres',Sphere::active()->lists('name','id'));
    }

    public function filtrationAll()
    {
        // выбираем активные сферы
        $spheres = Sphere::active()->get();

        // все неактивные маски пользователей
        $collection = array();

        // перебираем все сферы и находим их маски
        foreach($spheres as $sphere){

            // маска по сфере
            $mask = new AgentBitmask($sphere->id);

            // добавляем в массив (с ключем id сферы) все неактинвые маски сферы с агентами масок
            $collection[$sphere->id] = $mask->whereIn('status', [0,1])->with('user')->get();
        }

        return view('admin.sphere.reprice')
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

        // возвращаем номер таблицы в маску
        $mask->changeTable($sphere->id);

        // находим короткую маску
        $bitmask = $mask->findShortMaskById();


        return view('admin.sphere.reprice_edit')
            ->with('sphere', $sphere)
            ->with('mask_id', $mask_id)
            ->with('mask', $bitmask)
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

        $userMask = UserMasks::where('sphere_id', '=', $sphere)->where('mask_id', '=', $mask->id)->first();
        $userMask->active = 1;
        $userMask->save();

        // добавлаем лиды агенту в таблицу аукциона (если есть лиды по маске)
        //Auction::addByAgentMask( $mask_id, $sphere );

        // обновляем данные аукциона по агенту
        // выводим на аукцион лидов по самим дорогим маскам
        Auction::addLeadInExpensiveMasks($mask->user_id, $sphere);

        return redirect()->route('admin.sphere.reprice');
    }
}