<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Psy\Util\Json;

class Region extends Model
{

    /**
     * Название таблицы
     *
     */
    protected $table = "regions";


    /**
     * Получение стран
     *
     */
    public function getCountries()
    {

        return $this->where('parent_region_number', 0);
    }


    /**
     * Получение родительского региона
     *
     */
    public function getParent()
    {

        $this->parent = Region:: where('id', $this['parent_region_id'])->first();

        return $this->parent;
    }


    /**
     * Получение пути к региону
     *
     */
    public function getPath()
    {

        $path = [];

        if ($this['parent_region_id'] > 0) {

            $switch = true;

            $current = $this;

            while ($switch) {

                $current->getParent();

                array_unshift($path, ['id' => $current['parent']['id'], 'name' => $current['parent']['name']]);

                if ($current['parent']['parent_region_id'] == 0) {

                    $switch = false;

                }else{

                    $current = $current['parent'];
                }

            }

//            $path = $this->getParent();

        }

        $this->path = $path;

        return $this->path;
    }


    /**
     * Получение родительского региона
     *
     */
    public function getChild()
    {

        $this->child = Region:: where('parent_region_id', $this['id'])->get();

        return $this->child;
    }


    /**
     * Добавление региона
     *
     * @param  string $regionName
     *
     * @return array
     */
    public function addRegion($regionName)
    {

//        $region = new Region();

//        todo добавление подрегиона в регион

//        todo

        $status = 'success';

        $maxRegionNumber = Region::
        where('parent_region_id', $this['id'])
            ->max('region_number');


        $regionNumber = $maxRegionNumber + 1;


        $region = new Region();

        $region['parent_region_id'] = $this['id'];
        $region['parent_region_number'] = $this['region_number'];
        $region['region_number'] = $regionNumber;
        $region['name'] = $regionName;
        $region['comment'] = '';

        $region->save();


        return ['status' => $status, 'region' => $region];
    }
}
