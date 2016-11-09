
var app = angular.module('app', [])

    .controller('SphereCtrl', function ( $scope, $http, $compile ) {


        /** Атрибуты агента */

        // редактор атрибутов
        $scope.attrEditor = {
            // блок выбора типа атрибута
            typeSelection: false,
            // селектор типа агента по умолчанию
            agentSelectedType: 0,
            // названия типов
            selectedTypeName:
            {
                checkbox: 'CheckBox',
                radio: 'Radio',
                select: 'Dropdown'
            },
            // сам редактор
            editor: false,
            // кнопка сохранения
            saveButton: false,
            // индекс редактируемого атрибута агента
            agentAttrIndex: 'null',
            // шаблон данных атрибута агента
            agentAttrData:
            {
                id: 0,
                _type: '',
                label: '',
                icon: '',
                position: '',
                option: []
            }
        };

        /**
         * Показ модального окна создания атрибута агента
         *
         */
        $scope.agentAddAttrShow = function(){

            // показывает селект с выбором типа атрибута агента
            $scope.attrEditor.typeSelection = true;
            // показывает модальное окно
            $('#modal-page').modal();
        };

        // функция отображения редактора атрибутов агента
        function showAgentNewAttrEditor( type ){

            // модель агента
            $scope.attrEditor.agentAttrData =
            {
                id: 0,
                _type: type,
                label: $scope.attrEditor.selectedTypeName[ type ],
                icon: '',
                position: $scope.data.cform.values.length + 1,
                option: []
            };

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.typeSelection = false;
            // показываем редактор
            $scope.attrEditor.editor = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.saveButton = true;
        }

        // форма редактирования атрибута агента
        $scope.editAgentAttr = function( attr ){

            // сохраняем индекс атрибута
            $scope.attrEditor.agentAttrIndex = $scope.data.cform.values.indexOf(attr);

            // заносим модель агента в модель редактора
            $scope.attrEditor.agentAttrData = JSON.parse( JSON.stringify( attr ) );

            // показываем редактор
            $scope.attrEditor.editor = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.saveButton = true;
            // показывает модальное окно
            $('#modal-page').modal();
        };

        // действие по выбору селекта
        $scope.selectedTypeAction = function(){

            // проверка выбранно что-то в селекте или нет
            if($scope.attrEditor.agentSelectedType != 0){
                // если селектор выбран
                // показываем редактор агента с нужными данными
                showAgentNewAttrEditor( $scope.attrEditor.agentSelectedType );
            }
        };

        // добавление опции атрибуту агента
        $scope.addAgentOption = function(){

            // создаем новую опцию
            var newOption =
            {
                id: 0,       // id статуса
                val: '',     // имя
                vale:       // значения
                    [
                        0,     // подумат над значениями при сохранении переключателя
                        null   // незнаю что за значение
                    ]
                //position: attr.option.length + 1 // позиция
            };

            // добавляем статус в модель
            $scope.attrEditor.agentAttrData.option.push( newOption );
        };

        // удаление опции атрибута
        $scope.deleteAgentOption = function( option ){

            // проверка, была ли опция уже сохранен на сервере
            // (есть или нет id)
            if( option.id == 0){
                // если статус еще небыл сохранен на сервере
                // просто удаляем его

                // находим индекс элемента
                var index = $scope.attrEditor.agentAttrData.option.indexOf(option);
                // удаляем элемент
                $scope.attrEditor.agentAttrData.option.splice(index, 1);

            }else{
                // если статус уже сохранен на сервере

                // добавляем в модель статуса элемент delete
                option.delete = true;
            }
        };

        // удаление опции атрибута
        $scope.deleteAgentAttr = function( attr ){

            console.log(attr);

            // проверка, была ли опция уже сохранен на сервере
            // (есть или нет id)
            if( attr.id == 0){
                // если статус еще небыл сохранен на сервере
                // просто удаляем его

                // находим индекс элемента
                var index = $scope.data.cform.values.indexOf(attr);
                // удаляем элемент
                $scope.data.cform.values.splice(index, 1);

            }else{
                // если статус уже сохранен на сервере

                // добавляем в модель статуса элемент delete
                attr.delete = true;
            }
        };

        // сохранение атрибута агента в модели
        $scope.saveAgentAttr = function(){

            // проверка атрибута, новый или изменить существующий
            if( $scope.attrEditor.agentAttrIndex == 'null' ){
                // атрибут новый, только создается

                // создаем новый атрибут
                $scope.data.cform.values.push( $scope.attrEditor.agentAttrData );

            }else{
                // атрибут уже есть в модели, его нужно просто обновить

                // изменяем содержание атрибута
                $scope.data.cform.values[ $scope.attrEditor.agentAttrIndex ] = JSON.parse( JSON.stringify( $scope.attrEditor.agentAttrData ) );
            }

            // убираем модальное окно
            $('#modal-page').modal('hide');
        };


        /** Общее */

        // действия при закрытия модального окна добавления атрибутов
        // по идее обнуление всех переменных модели
        $('#modal-page').on('hidden.bs.modal', function (e) {

            // возвращаем данные редактора в начальное состояние
            // редактор атрибутов
            $scope.attrEditor = {
                // блок выбора типа атрибута
                typeSelection: false,
                // селектор типа агента по умолчанию
                agentSelectedType: 0,
                // названия типов
                selectedTypeName:
                {
                    checkbox: 'CheckBox',
                    radio: 'Radio',
                    select: 'Dropdown'
                },
                // сам редактор
                editor: false,
                // кнопка сохранения
                saveButton: false,
                // индекс редактируемого атрибута агента
                agentAttrIndex: 'null',
                // шаблон данных атрибута агента
                agentAttrData:
                {
                    id: 0,
                    _type: '',
                    label: '',
                    icon: '',
                    position: '',
                    option: []
                }
            };
            $scope.$apply($scope.attrEditor);
        });



        /** Статусы */

        /**
         * Форма добавления нового статуса
         *
         *
         */
        $scope.addStatus = function(){

            // создаем новый статус
            var newStatus =
            {
                id:0,       // id статуса
                val:'',     // имя
                vale:       // значения
                    [
                        0,  // значения переключателя min/max
                        0   // процент
                    ],
                position: $scope.data.threshold.values.length + 1 // позиция
            };

            // добавляем статус в модель
            $scope.data.threshold.values.push( newStatus );
        };

        /**
         * Удаление статуса
         *
         *
         * при удалении элемент скрывается
         * и ему добавляется элемент delete
         * на сервере этот элемент будет удален из базы
         */
        $scope.deleteStatus = function( status ){

            // проверка, был ли статус уже сохранен на сервере
            // (есть или нет id)

            if( status.id == 0){
                // если статус еще небыл сохранен на сервере
                // просто удаляе его

                // находим индекс элемента
                var index = $scope.data.threshold.values.indexOf(status);
                // удаляем элемент
                $scope.data.threshold.values.splice(index, 1);

            }else{
                // если статус уже сохранен на сервере

                // добавляем в модель статуса элемент delete
                status.delete = true;
            }
        };




        $scope.log = function(){
            console.log( $scope.data.cform.values);
        };


        /** Обмен данными с сервером */

        /**
         * Получение данных по сфере с сервера и добавление на страницу
         *
         *
         * некоторые данные приводятся к нужным типам
         */
        $http.get( confUrl )
            .success(function ( data ) {
                // преобразование данных и добавление на страницы

                /** переключатель на статусе не понимает 1 и 0, приходится преобразовывать в булев тип */
                // преобразовываем данные в булев тип
                $.each(data.threshold.values, function( key, val){
                    val.vale[0] = (val.vale[0] == 1);
                });

                /** Преобразовываем поля int в строки */
                // срок пребывания на аукциона
                data.opt.variables.lead_auction_expiration_interval_month.values = String( data.opt.variables.lead_auction_expiration_interval_month.values );
                data.opt.variables.lead_auction_expiration_interval_days.values = String( data.opt.variables.lead_auction_expiration_interval_days.values );
                data.opt.variables.lead_auction_expiration_interval_hours.values = String( data.opt.variables.lead_auction_expiration_interval_hours.values );
                data.opt.variables.lead_auction_expiration_interval_minutes.values = String( data.opt.variables.lead_auction_expiration_interval_minutes.values );
                // срок на присвоение лиду bad_lead
                data.opt.variables.lead_bad_status_interval_month.values = String( data.opt.variables.lead_bad_status_interval_month.values );
                data.opt.variables.lead_bad_status_interval_days.values = String( data.opt.variables.lead_bad_status_interval_days.values );
                data.opt.variables.lead_bad_status_interval_hours.values = String( data.opt.variables.lead_bad_status_interval_hours.values );
                data.opt.variables.lead_bad_status_interval_minutes.values = String( data.opt.variables.lead_bad_status_interval_minutes.values );
                // срок на показывание лида по рангам
                data.opt.variables.range_show_lead_interval_month.values = String( data.opt.variables.range_show_lead_interval_month.values );
                data.opt.variables.range_show_lead_interval_days.values = String( data.opt.variables.range_show_lead_interval_days.values );
                data.opt.variables.range_show_lead_interval_hours.values = String( data.opt.variables.range_show_lead_interval_hours.values );
                data.opt.variables.range_show_lead_interval_minutes.values = String( data.opt.variables.range_show_lead_interval_minutes.values );

                // отдаем модель
                $scope.data = data;
            })
            .error(function ( data ) {
                // сообщение об ошибке при получении данных
                alert('error');
            });

        /**
         * Отправка данных на сервер для сохранения
         *
         */
        $scope.saveData = function(){

            // данные для отправки на сервер
            var data = $scope.data ;

            // отправка токена
            var config = {
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            };

            // запрос на сервер для обработки и сохранения данных
            $http.post( saveDataUrl, data, config)
                .success(function (data, status, headers, config) {
                    alert('Ok');
                })
                .error(function (data, status, header, config) {
                    alert('Error');
                });

        };

    });

