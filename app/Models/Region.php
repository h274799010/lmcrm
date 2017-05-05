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

                array_unshift($path, [
                    'id' => $current['parent']['id'],
                    'name' => $current['parent']['name'],
                    'region_number' => $current['parent']['region_number']
                ]);

                if ($current['parent']['parent_region_id'] == 0) {

                    $switch = false;

                } else {

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


    /**
     * Возвращает индекс региона
     *
     */
    public function getIndex()
    {


        // получение полного пути к текущему региону
        $path = $this->getPath();

        // добавляем в путь текущий регион
        $path[] = [
            'id' => $this['id'],
            'name' => $this['name'],
            'region_number' => $this['region_number']
        ];

        // заносим имеющиеся данные в массив индекса
        $indexArray[0] = $path[0]['region_number'];
        $indexArray[1] = isset($path[1]) ? $path[1]['region_number'] : 0;
        $indexArray[2] = isset($path[2]) ? $path[2]['region_number'] : 0;
        $indexArray[3] = isset($path[3]) ? $path[3]['region_number'] : 0;
        $indexArray[4] = isset($path[4]) ? $path[4]['region_number'] : 0;
        $indexArray[5] = isset($path[5]) ? $path[5]['region_number'] : 0;
        $indexArray[6] = isset($path[6]) ? $path[6]['region_number'] : 0;

        // переводим значения номеров в строку
        foreach ($indexArray as $key => $item) {
            $indexArray[$key] = strval($item);
        }

        // проверка массива индекса
        $indexArray[0] = str_pad($indexArray[0], 2, '0', STR_PAD_LEFT);
        $indexArray[1] = str_pad($indexArray[1], 2, '0', STR_PAD_LEFT);
        $indexArray[2] = str_pad($indexArray[2], 3, '0', STR_PAD_LEFT);
        $indexArray[3] = str_pad($indexArray[3], 3, '0', STR_PAD_LEFT);
        $indexArray[4] = str_pad($indexArray[4], 3, '0', STR_PAD_LEFT);
        $indexArray[5] = str_pad($indexArray[5], 3, '0', STR_PAD_LEFT);
        $indexArray[6] = str_pad($indexArray[6], 2, '0', STR_PAD_LEFT);

        // об'эдинение номеров в строку
        $indexString = implode('', $indexArray);

        // преобразование строкового индекса в число
        $index = (int)$indexString;


        $this->index = $index;

        return $index;
    }


    /**
     * Парсит индекс региона
     *
     * @param  integer $index
     *
     * @return array
     */
    public static function parseIndex($index)
    {

        $index = strval($index);

        if (strlen($index) == 17) {
            $index = '0' . $index;
        }

        $rawIndexArr = str_split($index);

        // разбиваем массив на части и преобразовываем в int
        $indexArray[0] = intval($rawIndexArr[0] . $rawIndexArr[1]);
        $indexArray[1] = intval($rawIndexArr[2] . $rawIndexArr[3]);
        $indexArray[2] = intval($rawIndexArr[4] . $rawIndexArr[5] . $rawIndexArr[6]);
        $indexArray[3] = intval($rawIndexArr[7] . $rawIndexArr[8] . $rawIndexArr[9]);
        $indexArray[4] = intval($rawIndexArr[10] . $rawIndexArr[11] . $rawIndexArr[12]);
        $indexArray[5] = intval($rawIndexArr[13] . $rawIndexArr[14] . $rawIndexArr[15]);
        $indexArray[6] = intval($rawIndexArr[16] . $rawIndexArr[17]);


        $path = [];
        $region = [];
        $child = false;

        foreach ($indexArray as $key => $val) {

            // проверка по ключу
            if ($key == 0) {
                // если это первый элемент

                $currentRegion = Region::
                where('parent_region_id', 0)
                    ->where('region_number', $val)
                    ->first();

                if ($indexArray[$key + 1] == 0) {

                    $region = [
                        'id' => $currentRegion['id'],
                        'parent_id' => $currentRegion['parent_region_id'],
                        'name' => $currentRegion['name']
                    ];

                    $child = $currentRegion->getChild();

                    break;

                } else {

                    $path[] = [
                        'id' => $currentRegion['id'],
                        'parent_id' => $currentRegion['parent_region_id'],
                        'name' => $currentRegion['name']
                    ];
                }

            } else {
                // если это не первый элемент

                $currentRegion = Region::
                where('parent_region_id', $path[$key - 1]['id'])
                    ->where('region_number', $val)
                    ->first();

                if ($key == 6) {

                    $region = [
                        'id' => $currentRegion['id'],
                        'parent_id' => $currentRegion['parent_region_id'],
                        'name' => $currentRegion['name']
                    ];

                    $child = $currentRegion->getChild();

                    break;

                } else {

                    if ($indexArray[$key + 1] == 0) {

                        $region = [
                            'id' => $currentRegion['id'],
                            'parent_id' => $currentRegion['parent_region_id'],
                            'name' => $currentRegion['name']
                        ];

                        $child = $currentRegion->getChild();

                        break;

                    } else {

                        $path[] = [
                            'id' => $currentRegion['id'],
                            'parent_id' => $currentRegion['parent_region_id'],
                            'name' => $currentRegion['name']
                        ];
                    }

                }


            }

        }


        $arrayRegion = [

            'path' => $path,
            'region' => $region,
            'child' => $child

        ];

//        dd($arrayRegion);

        return $arrayRegion;
    }

}
