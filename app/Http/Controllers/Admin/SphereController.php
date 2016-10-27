<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Agent;
use App\Models\AgentBitmask;
use App\Models\FormFiltersOptions;
use App\Models\AdditionFormsOptions;

use App\Models\LeadBitmask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\Sphere;
use App\Models\Auction;

use App\Models\SphereStatuses;
use App\Models\SphereFormFilters;


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
        if( !count($request->all()) ) { return response()->json( ['error'=>trans('admin/sphere.errors.required.sphere_name')] ); }

        // данные формы сферы
        $sphereData = $request['opt']['data'];

        // данные формы лида
        $leadData = $request['lead']['data'];

        $sphereStatus = $sphereData['variables']['status'];


        /**
         * todo у лида может не быть атрибутов вообще, поправить этот момент
         *
         */

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
                 */

//                return response()->json(FALSE);

            }elseif( $leadData['variables']==["email" => "", "textarea" => "", "input" => "", "checkbox" => "", "radio" => "", "select" => "", "calendar" => "", "submit" => ""] ){
                // 'variables' состоит из массива с ключами из типов полей

                /*
                 * У лида может и не быть атрибутов
                 */

                $sphereStatus = 0;
                $leadDataAttr = NULL;


            }elseif(isset($leadData['variables'][0])){
                // 'variables' массив элемент с ключом "0" (по идее в других вариантах его быть недолжно)

//                if( count($leadData['variables']) < 3 ){
//                    // если у лида меньше 3 атрибутов
//                    // работа метода останавливается
//
//                    // у лида должно быть не меньше 3 атрибутов
//                    return response()->json(FALSE);
//
//                }else{
//                    // у лида 3 и больше атрибутов
//
//                    /*
//                     * массив просто преобразовывается в коллекцию
//                     * чтобы было проще дальше обрабатывать
//                     */
//
//                    $leadDataAttr = collect( $leadData['variables'] );
//                }

                // todo если все есть - возвращаем просто данные в коллекции
                $leadDataAttr = collect( $leadData['variables'] );


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

            // todo добавить чтобы можно было сохранять и без атрибутов лида
            // у лида должно быть не меньше 3 атрибутов
//            return response()->json(FALSE);
            $sphereStatus = 0;
            $leadDataAttr = NULL;
        }

        // данные формы агента
        $agentData = $request['cform']['data'];


        $agentDataAttr = NULL;

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

                $sphereStatus = 0;
//                return response()->json(FALSE);

            }elseif(count($agentData['variables']) < 3) {
                /*
                 * Если у агента меньше 3-х атрибутов
                 * возвращаем FALSE
                 */
                    $sphereStatus = 0;
                    $agentDataAttr = collect( $agentData['variables'] );
//                return response()->json( [ 'error'=>true, 'message'=>trans('admin/sphere.errors.minAgentForm') ] );
            }elseif( isset($agentData['variables'][0]) ){
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

                $sphereStatus = 0;
                $agentDataAttr = NULL;
//                return response()->json(FALSE);
//                return response()->json([ 'error'=>true, 'message'=>trans('admin/sphere.errors.minAgentForm') ]);
            }

        }else{
            // у агента нет атрибутов
            // работа метода останавливается

            // у агента должно быть не меньше 1 атрибута
            $agentDataAttr = NULL;
            $sphereStatus = 0;

//            return response()->json(FALSE);
        }

        // минимальное количество лидов
        $minLead = $request['stat_minLead'];

        // статусы
        $statusData = ($request['threshold']['data']) ? collect( $request['threshold']['data'] ) : FALSE;


        /** ----- КОНЕЦ ОБРАБОТКИ ДАННЫХ ПОЛУЧЕННЫХ С ФРОНТЕНДА ---------- */


        /** ----- ПРОВЕРКИ НА ОШИБКИ ---------- */

        // переменная указывающая на ошибки, если $error = true работа метода останавливается
        $error = false;
        /* проверка атрибутов формы лида на ошибки */
//        if( $leadDataAttr ){
//
//            /* у атрибутов с типом checkbox, radio, select обязательно должны быть опции,
//               хотя бы одна */
//            // перебрать атрибуты лидов и агентов и проверить опции по соответствующим типам
//            $leadDataAttr->each(function( $attr ) use( &$error ){
//
//                // если у атрибута тип checkbox, radio или select
//                if( ($attr['_type']=='checkbox') || ($attr['_type']=='radio') || ($attr['_type']=='select') ){
//                    // и при этом у него нет опций, либо их количество равно 0
//                    if( !isset($attr['option']) || (count($attr['option']) == 0) ){
//                        // помечаем ошибку
//                        $error = true;
//                    }
//                }
//            });
//        }

        /* проверка атрибутов формы агента на ошибки */
//        if( $agentDataAttr ){
//
//            /* у атрибутов с типом checkbox, radio, select обязательно должны быть опции,
//               хотя бы одна */
//            // перебрать атрибуты лидов и агентов и проверить опции по соответствующим типам
//            $agentDataAttr->each(function( $attr ) use( &$error ){
//
//                // если у атрибута тип checkbox, radio или select
//                if( ($attr['_type']=='checkbox') && ($attr['_type']=='radio') && ($attr['_type']=='select') ){
//                    // и при этом у него нет опций, либо их количество равно 0
//                    if( !isset($attr['option']) && (count($attr['option']) == 0) ){
//                        // помечаем ошибку
//                        $error = true;
//                    }
//                }
//            });
//        }

        // если есть ошибка, функция вернет ошибку и остановится
//        if($error){ return response()->json(FALSE); }

        if(empty($sphereData['variables']['name'])) {
            return response( ['error'=>trans('admin/sphere.errors.required.sphere_name')] );
        }

        /** ----- КОНЕЦ ПРОВЕРОК НА ОШИБКИ ---------- */

        // Считаем интервалы времени прибывания лида на укционе и времени на статус bad

        $months = $sphereData['variables']['lead_auction_expiration_interval_month'];
        $days = $sphereData['variables']['lead_auction_expiration_interval_days'];
        $hours = $sphereData['variables']['lead_auction_expiration_interval_hours'];
        $minutes = $sphereData['variables']['lead_auction_expiration_interval_minutes'];

        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $interval_auction = $now->addMinutes($minutes)->addHours($hours)->addDays($days)->addMonths($months);
        $interval_auction = $interval_auction->timestamp - $timestamp;

        $months = $sphereData['variables']['lead_bad_status_interval_month'];
        $days = $sphereData['variables']['lead_bad_status_interval_days'];
        $hours = $sphereData['variables']['lead_bad_status_interval_hours'];
        $minutes = $sphereData['variables']['lead_bad_status_interval_minutes'];

        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $interval_bad = $now->addMinutes($minutes)->addHours($hours)->addDays($days)->addMonths($months);
        $interval_bad = $interval_bad->timestamp - $timestamp;

        $months = $sphereData['variables']['range_show_lead_interval_month'];
        $days = $sphereData['variables']['range_show_lead_interval_days'];
        $hours = $sphereData['variables']['range_show_lead_interval_hours'];
        $minutes = $sphereData['variables']['range_show_lead_interval_minutes'];

        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $interval_range = $now->addMinutes($minutes)->addHours($hours)->addDays($days)->addMonths($months);
        $interval_range = $interval_range->timestamp - $timestamp;

        if( !$minLead || !$sphereData['variables']['openLead'] || !$interval_auction || !$interval_bad
            || !$interval_range || !$sphereData['variables']['price_call_center'] || !$sphereData['variables']['max_range'] ) {

            $sphereStatus = 0;
        }

        /**
         * Выбираем сферу по id, либо, создаем новую
         *
         */
        if($id) {
            $sphere = Sphere::find($id);
            $sphere->name = $sphereData['variables']['name'];
            $sphere->minLead = $minLead;
            $sphere->status = $sphereStatus;
            $sphere->openLead = $sphereData['variables']['openLead'];
            $sphere->lead_auction_expiration_interval = $interval_auction;
            $sphere->lead_bad_status_interval = $interval_bad;
            $sphere->range_show_lead_interval = $interval_range;
            $sphere->price_call_center = $sphereData['variables']['price_call_center'];
            $sphere->max_range = $sphereData['variables']['max_range'];
        } else {
            $sphere = new Sphere();
            $sphere->name = $sphereData['variables']['name'];
            $sphere->minLead = $minLead;
            $sphere->status = $sphereStatus;
            $sphere->openLead = $sphereData['variables']['openLead'];
            $sphere->lead_auction_expiration_interval = $interval_auction;
            $sphere->lead_bad_status_interval = $interval_bad;
            $sphere->range_show_lead_interval = $interval_range;
            $sphere->price_call_center = $sphereData['variables']['price_call_center'];
            $sphere->max_range = $sphereData['variables']['max_range'];
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
                        $leadBitmask->removeAd($attr['id'], null);
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

                    // для атрибутов с типами: input, textArea, email и calendar
                    // в битмаске создается поле только с индексом атрибута
                    // вместо индекса опции ставится "0"
                    // ad_attrId_0 ( ad_54_0 )
                    if( ($attr['_type']=='textarea') || ($attr['_type']=='input') || ($attr['_type']=='email') || ($attr['_type']=='calendar') ){
                        $leadBitmask->addAb($leadAttr->id, 0, $leadAttr->_type);
                    }

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

                    if( isset($attr['option']) ){

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
                                // (обновляем существующую запись)

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
                }

                return true;
            });

        }



        /**
         * Обработка атрибутов агента
         */
        if($agentDataAttr){

            // перебираем все атрибуты, создаем/обновляем его данные
            $agentDataAttr->each(function( $attr )  use( $sphere, &$agentBitmask, &$leadBitmask ) {


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

                        // удаление полей в битмаске агента и лида
                        $agentBitmask->removeAttr($attr['id'], null);
                        $leadBitmask->removeAttr($attr['id'], null);

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
                    $attr = $sphere->attributes()->save($agentAttr);
                }


            // УДАЛЕНИЕ ОПЦИЙ АТРИБУТА

                //dd($attr);

                if( $attr['id'] != 0 ){
                    $AttrOptionsInDB = FormFiltersOptions::where( 'attr_id', $attr['id'] )->get();

                    if( $AttrOptionsInDB && isset( $attr['option'] ) ){

                        // если в атрибуте только одна опция то она помещается в option без массива
                        // чтобы обработка была правильной, просто помещаем его в массив
                        if( isset( $attr['option']['id']) ){
                            $attr['option'] = [ $attr['option'] ];
                        }


                        $siteOptions = [];

                        foreach( $attr['option'] as $option){

                            $siteOptions[ $option['id'] ] = $option;

                        }

//                    dd( $siteOptions );


                        $AttrOptionsInDB->each(function( $optionInDB ) use ( $siteOptions ){

                            if( !isset( $siteOptions[ $optionInDB->id ] ) ){

//                            dd( $optionInDB->id );
                                $optionInDB->delete();
                            }


                        });

                    }
                }



            // ОБРАБОТКА ОПЦИЙ АТРИБУТА (предполагается что у атрибута есть только опции, валидаций и прочего нет)

                if (isset($attr['option'])) {
                    // контрольная проверка наличия опций
                    // по идее опции должны быть, в начале метода стоит проверка

                    // если в атрибуте только одна опция то она помещается в option без массива
                    // чтобы обработка была правильной, просто помещаем его в массив
                    if( isset( $attr['option']['id']) ){
                        $attr['option'] = [ $attr['option'] ];
                    }

                    // перебираем все опции и либо создаем новую,
                    // либо обновляем существующую запись опции
                    $optionCollection = collect($attr['option']);
                    $optionCollection->each(function ($option) use (&$agentAttr, &$agentBitmask, &$leadBitmask) {

                        if ( $option['id'] ) {
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

                            // создаем новые столбцы в битмасе агента и лида
                            $agentBitmask->addAttrWithType($agentAttr->id, $newOption->id, 'boolean');
                            $leadBitmask->addAttrWithType($agentAttr->id, $newOption->id, 'boolean');


                            /** смысл этой конструкции я не понял */
                            if (isset($option['parent'])) {
                                // копирование атрибутов
                                $agentBitmask->copyAttr
                                (
                                    $agentAttr->id,
                                    $newOption->id,
                                    /*parent*/
                                    $option['parent']
                                );
                            }
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
            //$collection[$sphere->id] = $mask->where('status', '=', 0)->with('user')->get();
            $collection[$sphere->id] = $mask->whereIn('status', [0, 1])->with('user')->get();
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
            ->with('price', $mask->status);
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

        return redirect()->route('admin.sphere.reprice');
    }
}