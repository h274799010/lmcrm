
var app = angular.module('app', [])

    .controller('SphereCtrl', function ( $scope, $http, $compile ) {


        /** Атрибуты агента */

        // редактор атрибутов
        var attrEditorData = {
            // блок выбора типа атрибута агента
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
            },
            lead:
            {
                // блок выбора типа атрибута лида
                typeSelection: false,
                // селектор типа лида по умолчанию
                selectedType: 0,
                // кнопка сохранения атрибута лида
                saveButton: false,
                // индекс редактируемого атрибута
                currentIndex: 0,
                // типы редактора
                editors:
                {
                    // Е-mail
                    email:
                    {
                        // переключатель (включить/выключить редактор)
                        switch: false,
                        // данные
                        data:
                        {
                            id: 0,
                            _type: '',
                            label: '',
                            icon: '',
                            position: '',
                            option: []
                        }
                    },

                    // выборочный тип (select, radio, checkBox)
                    selective:
                    {
                        // переключатель (включить/выключить редактор)
                        switch: false,
                        data:
                        {
                            id: 0,
                            _type: '',
                            label: '',
                            icon: '',
                            position: '',
                            option: []
                        }
                    },

                    // Календарь
                    calendar:
                    {
                        // переключатель (включить/выключить редактор)
                        switch: false,
                        data:
                        {
                            id: 0,
                            _type: '',
                            label: '',
                            icon: '',
                            position: '',
                            option: []
                        }
                    },

                    // Textarea
                    textarea:
                    {
                        // переключатель (включить/выключить редактор)
                        switch: false,
                        data:
                        {
                            id: 0,
                            _type: '',
                            label: '',
                            icon: '',
                            position: '',
                            option: [],
                            validate: []
                        }
                    },

                    // Textinput
                    textinput:
                    {
                        // переключатель (включить/выключить редактор)
                        switch: false,
                        data:
                        {
                            id: 0,
                            _type: '',
                            label: '',
                            icon: '',
                            position: '',
                            option: [],
                            validate: []
                        }
                    }
                }
            }
        };

        // подключаем данные
        $scope.attrEditor = JSON.parse( JSON.stringify( attrEditorData ) );

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

            // заносим данные атрибута агента в модель редактора
            $scope.attrEditor.agentAttrData = JSON.parse( JSON.stringify( attr ) );

            // показываем редактор
            $scope.attrEditor.editor = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.saveButton = true;
            // показывает модальное окно
            $('#modal-page').modal();
        };

        // действие по выбору селекта агента
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


        /** Дополнительные поля лида */

        /**
         * Показ модального окна создания атрибута лида
         *
         */
        $scope.leadAddAttrShow = function(){

            // показывает селект с выбором типа атрибута лида
            $scope.attrEditor.lead.typeSelection = true;
            // показывает модальное окно
            $('#modal-page').modal();
        };

        /**
         * Новый атрибут лида с типами "select", "radio" и "checkBox"
         *
         */
        function showLeadSelectiveNewAttr( type ){

            // модель агента
            //$scope.attrEditor.agentAttrData =
            //{
            //    id: 0,
            //    _type: type,
            //    label: $scope.attrEditor.selectedTypeName[ type ],
            //    icon: '',
            //    position: $scope.data.cform.values.length + 1,
            //    option: []
            //};

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;
            // показываем редактор
            $scope.attrEditor.lead.editors.selective.switch = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.lead.saveButton = true;
        }

        /**
         * Новый атрибут лида с типом "email"
         *
         */
        function showLeadEmailNewAttr( type ){
            // модель агента
            //$scope.attrEditor.agentAttrData =
            //{
            //    id: 0,
            //    _type: type,
            //    label: $scope.attrEditor.selectedTypeName[ type ],
            //    icon: '',
            //    position: $scope.data.cform.values.length + 1,
            //    option: []
            //};

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;
            // показываем редактор
            $scope.attrEditor.lead.editors.email.switch = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.lead.saveButton = true;
        }

        /**
         * Новый атрибут лида с типом "calendar"
         *
         */
        function showLeadCalendarNewAttr( type ){
            // модель агента
            //$scope.attrEditor.agentAttrData =
            //{
            //    id: 0,
            //    _type: type,
            //    label: $scope.attrEditor.selectedTypeName[ type ],
            //    icon: '',
            //    position: $scope.data.cform.values.length + 1,
            //    option: []
            //};

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;
            // показываем редактор
            $scope.attrEditor.lead.editors.calendar.switch = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.lead.saveButton = true;
        }

        /**
         * Новый атрибут лида с типом "TextArea"
         *
         */
        function showLeadTextareaNewAttr( type ){
            // модель агента
            //$scope.attrEditor.agentAttrData =
            //{
            //    id: 0,
            //    _type: type,
            //    label: $scope.attrEditor.selectedTypeName[ type ],
            //    icon: '',
            //    position: $scope.data.cform.values.length + 1,
            //    option: []
            //};

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;
            // показываем редактор
            $scope.attrEditor.lead.editors.textarea.switch = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.lead.saveButton = true;
        }

        /**
         * Новый атрибут лида с типом "TextInput"
         *
         */
        function showLeadTextinputNewAttr( type ){
            // модель агента
            //$scope.attrEditor.agentAttrData =
            //{
            //    id: 0,
            //    _type: type,
            //    label: $scope.attrEditor.selectedTypeName[ type ],
            //    icon: '',
            //    position: $scope.data.cform.values.length + 1,
            //    option: []
            //};

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;
            // показываем редактор
            $scope.attrEditor.lead.editors.textinput.switch = true;
            // показываем кнопку "сохранить"
            $scope.attrEditor.lead.saveButton = true;
        }




        /**
         * Редактирование атрибута лида
         *
         */
        $scope.showLeadEditAttr = function( attr ){

            // сохраняем индекс атрибута
            $scope.attrEditor.lead.currentIndex = $scope.data.lead.values.indexOf( attr );

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;

            // действия в зависимости от типа атрибута
            switch ( attr._type ){

                case 'email':
                    // данные атрибута с типом 'email'
                    $scope.attrEditor.lead.editors.email.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.email.switch = true;
                    break;

                case 'textarea':
                    // todo
                    $scope.attrEditor.lead.editors.textarea.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.textarea.switch = true;
                    break;

                case 'input':
                    // todo
                    $scope.attrEditor.lead.editors.textinput.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.textinput.switch = true;
                    break;

                case 'checkbox':
                case 'radio':
                case 'select':
                    // todo
                    $scope.attrEditor.lead.editors.selective.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.selective.switch = true;
                    break;

                case 'calendar':
                    // todo
                    $scope.attrEditor.lead.editors.calendar.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.calendar.switch = true;
                    break;

                default:
                    break;
            }

            // показываем кнопку "сохранить"
            $scope.attrEditor.lead.saveButton = true;
            // показывает модальное окно
            $('#modal-page').modal();
        };



        /**
         * Действие по выбору селекта типа атрибута лида
         *
         */
        $scope.leadSelectedTypeAction = function(){

            // проверка выбранно что-то в селекте или нет
            if($scope.attrEditor.lead.selectedType != 0){
                // если выбранно

                // выбор типа формы редактора в завичимости от типа атрибута
                switch ( $scope.attrEditor.lead.selectedType ) {

                    case 'email':
                        showLeadEmailNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    case 'textarea':
                        showLeadTextareaNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    case 'textinput':
                        showLeadTextinputNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    case 'checkbox':
                    case 'radio':
                    case 'select':
                        showLeadSelectiveNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    case 'calendar':
                        showLeadCalendarNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    default:
                        break;
                }
            }
        };


        /** Общее */

        // действия при закрытия модального окна добавления атрибутов
        // по идее обнуление всех переменных модели
        $('#modal-page').on('hidden.bs.modal', function (e) {

            // возвращаем данные редактора в начальное состояние
            // редактор атрибутов
            $scope.attrEditor = JSON.parse( JSON.stringify( attrEditorData ) );
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

