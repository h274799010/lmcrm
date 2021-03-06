

/**
 * Счетчик внешних статусов
 *
 */
var sphereStatusesCount = 0;



// todo сделать такой же в обратную сторону
/**
 * Переменная с соответствиями id статусов
 *
 * Показывает какой id статуса соответствует
 * внешнему статусу outerId
 */
var statusToOuterId = { 0:0, '-2':'-2' };

var outerIdToStatus = { 0:0 };

var app = angular.module('app', ['angular-sortable-view'])

    .controller('SphereCtrl', function ( $scope, $http ) {


        /** Модель редактора атрибутов */

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
                select: 'Dropdown',
                email: 'E-mail',
                textarea: 'Text Area',
                input: 'Text Input',
                calendar: 'Calendar'
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
                seadminlectedType: 0,
                // кнопка сохранения атрибута лида
                saveButton: false,
                // тип редактируемого атрибута
                currentType: '',
                // индекс редактируемого атрибута
                currentIndex: 'null',
                // дополнительное поле валидации лида
                additionalValidationFieldDisabled: {
                    '': true,
                    0: true,
                    email: true,
                    url: true,
                    number: true,
                    date: true,
                    digits: true,
                    dateISO: true,
                    creditcard: true,
                    min: false,
                    max: false,
                    minlength: false,
                    maxlength: false,
                    equalTo: false

                },
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


        /** todo Переменные ошибок */

        $scope.errorSwitch = false;

        $scope.errorSwitchOff = function(){
            $scope.errorSwitch = false;
        };

        /** Атрибуты агента */

        // подключаем данные, клонируем модель чтобы не перебивать данные
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
                id: 0,         // id статуса
                val: '',       // имя
                vale: true,    // значения
                position: $scope.attrEditor.agentAttrData.option.length + 1 // позиция
            };

            // добавляем статус в модель
            $scope.attrEditor.agentAttrData.option.push( newOption );
        };

        // деление опции атрибута агента
        $scope.addAgentBranch = function( parent ){

            // создаем новую опцию
            var newOption =
            {
                id: 0,       // id статуса
                val: '',     // имя
                vale: true,      // значения
                position: $scope.attrEditor.agentAttrData.option.length + 1, // позиция
                parent: $scope.attrEditor.agentAttrData.option[parent].id
            };

            // добавляем атрибут в модель
            $scope.attrEditor.agentAttrData.option.splice( parent+1, 0, newOption );

            //$scope.positioning($scope.attrEditor.agentAttrData.option);
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

            // модель атрибута лида с типом 'selective'
            $scope.attrEditor.lead.editors.selective.data =
            {
                id: 0,
                _type: type,
                label: $scope.attrEditor.selectedTypeName[ type ],
                icon: '',
                position: $scope.data.lead.values.length + 1,
                option: []
            };

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

            // модель атрибута лида с типом 'email'
            $scope.attrEditor.lead.editors.email.data =
            {
                id: 0,
                _type: type,
                label: $scope.attrEditor.selectedTypeName[ type ],
                icon: '',
                position: $scope.data.lead.values.length + 1,
                option: []
            };

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

            // модель атрибута лида с типом 'calendar'
            $scope.attrEditor.lead.editors.calendar.data =
            {
                id: 0,
                _type: type,
                label: $scope.attrEditor.selectedTypeName[ type ],
                icon: '',
                position: $scope.data.lead.values.length + 1,
                option: []
            };

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

            // модель атрибута лида с типом 'textarea'
            $scope.attrEditor.lead.editors.textarea.data =
            {
                id: 0,
                _type: type,
                label: $scope.attrEditor.selectedTypeName[ type ],
                icon: '',
                position: $scope.data.lead.values.length + 1,
                option: [],
                validate: []
            };

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

            // модель атрибута лида с типом 'textinput'
            $scope.attrEditor.lead.editors.textinput.data =
            {
                id: 0,
                _type: type,
                label: $scope.attrEditor.selectedTypeName[ type ],
                icon: '',
                position: $scope.data.lead.values.length + 1,
                option: [],
                validate: []
            };

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
            // сохраняем текущий тип атрибута
            $scope.attrEditor.lead.currentType = attr._type;

            // выключаем показ селекта с выбором типа атрибута
            $scope.attrEditor.lead.typeSelection = false;

            // действия в зависимости от типа атрибута
            switch ( attr._type ){

                case 'email':
                    // заносим в модель данные атрибута с типом 'email'
                    $scope.attrEditor.lead.editors.email.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.email.switch = true;
                    break;

                case 'textarea':
                    // заносим в модель данные атрибута с типом 'textarea'
                    $scope.attrEditor.lead.editors.textarea.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.textarea.switch = true;
                    break;

                case 'input':
                    // заносим в модель данные атрибута с типом 'input'
                    $scope.attrEditor.lead.editors.textinput.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.textinput.switch = true;
                    break;

                case 'checkbox':
                case 'radio':
                case 'select':
                    // заносим в модель данные атрибута с типом 'checkbox', 'radio' или 'select'
                    $scope.attrEditor.lead.editors.selective.data = JSON.parse( JSON.stringify( attr ) );
                    // показываем редактор
                    $scope.attrEditor.lead.editors.selective.switch = true;
                    break;

                case 'calendar':
                    // заносим в модель данные атрибута с типом 'calendar'
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
         * Сохранение атрибута лида
         *
         */
        $scope.saveLeadAttr = function(){

            // проверка атрибута, новый или изменить существующий
            if( $scope.attrEditor.lead.currentIndex == 'null' ){
                // атрибут новый, только создается

                // действие в зависимости от типа атрибута
                switch ( $scope.attrEditor.lead.currentType ){

                    case 'email':
                        // создаем новый атрибут с типом email
                        $scope.data.lead.values.push( $scope.attrEditor.lead.editors.email.data );
                        break;

                    case 'textarea':
                        // создаем новый атрибут с типом textarea
                        $scope.data.lead.values.push( $scope.attrEditor.lead.editors.textarea.data );
                        break;

                    case 'input':
                        // создаем новый атрибут с типом input
                        $scope.data.lead.values.push( $scope.attrEditor.lead.editors.textinput.data );
                        break;

                    case 'checkbox':
                    case 'radio':
                    case 'select':
                        // создаем новый атрибут с типом 'checkbox', 'radio' или 'select'
                        $scope.data.lead.values.push( $scope.attrEditor.lead.editors.selective.data );
                        break;

                    case 'calendar':
                        // создаем новый атрибут с типом calendar
                        $scope.data.lead.values.push( $scope.attrEditor.lead.editors.calendar.data );
                        break;

                    default:
                        break;
                }

                console.log($scope.data.lead.values);

            }else{
                // атрибут уже есть в модели, его нужно просто обновить

                // действие в зависимости от типа атрибута
                switch ( $scope.attrEditor.lead.currentType ){

                    case 'email':
                        // изменяем содержание атрибута с типом email
                        $scope.data.lead.values[ $scope.attrEditor.lead.currentIndex ] = JSON.parse( JSON.stringify( $scope.attrEditor.lead.editors.email.data ) );
                        // todo $scope.data.lead.values.push( JSON.parse( JSON.stringify( $scope.attrEditor.lead.editors.email.data ) ) );

                        break;

                    case 'textarea':
                        // изменяем содержание атрибута с типом textarea
                        $scope.data.lead.values[ $scope.attrEditor.lead.currentIndex ] = JSON.parse( JSON.stringify( $scope.attrEditor.lead.editors.textarea.data ) );
                        break;

                    case 'input':
                        // изменяем содержание атрибута с типом input
                        $scope.data.lead.values[ $scope.attrEditor.lead.currentIndex ] = JSON.parse( JSON.stringify( $scope.attrEditor.lead.editors.textinput.data ) );
                        break;

                    case 'checkbox':
                    case 'radio':
                    case 'select':
                        // изменяем содержание атрибута с типом email
                        $scope.data.lead.values[ $scope.attrEditor.lead.currentIndex ] = JSON.parse( JSON.stringify( $scope.attrEditor.lead.editors.selective.data ) );
                        break;

                    case 'calendar':
                        // изменяем содержание атрибута с типом email
                        $scope.data.lead.values[ $scope.attrEditor.lead.currentIndex ] = JSON.parse( JSON.stringify( $scope.attrEditor.lead.editors.calendar.data ) );
                        break;

                    default:
                        break;
                }

                //$scope.positioning( $scope.data.lead.values );

            }

            // убираем модальное окно
            $('#modal-page').modal('hide');
        };

        // добавление опции атрибуту лида
        $scope.addLeadOption = function(){

            // создаем новую опцию
            var newOption =
            {
                id: 0,       // id статуса
                val: '',     // имя
                vale: '',       // значения
                position: $scope.attrEditor.lead.editors.selective.data.option.length + 1 // позиция
            };

            // добавляем статус в модель
            $scope.attrEditor.lead.editors.selective.data.option.push( newOption );

            // todo $scope.positioning($scope.attrEditor.agentAttrData.option);

        };

        // удаление опции атрибута лида
        $scope.deleteLeadOption = function( option ){

            // проверка, была ли опция уже сохранен на сервере
            // (есть или нет id)
            if( option.id == 0){
                // если статус еще небыл сохранен на сервере
                // просто удаляем его

                // находим индекс элемента
                var index = $scope.attrEditor.lead.editors.textinput.data.option.indexOf(option);
                // удаляем элемент
                $scope.attrEditor.lead.editors.selective.data.option.splice(index, 1);

            }else{
                // если статус уже сохранен на сервере

                // добавляем в модель статуса элемент delete
                option.delete = true;
            }
        };

        // добавление валидации атрибуту лида
        $scope.addLeadValidate = function(){

            // создаем новую опцию
            var newValidate =
            {
                id: 0,       // id валидации
                val: '',     // название валидации
                vale: ''     // значение
            };

            // добавление валидации в атрибут соответствующего типа
            switch ($scope.attrEditor.lead.currentType){

                // атрибут с типом 'textarea'
                case 'textarea':
                    // добавляем валидацию в модель
                    $scope.attrEditor.lead.editors.textarea.data.validate.push( newValidate );
                    break;

                // атрибут с типом 'input'
                case 'input':
                    // добавляем валидацию в модель
                    $scope.attrEditor.lead.editors.textinput.data.validate.push( newValidate );
                    break;

                default:
                    break;
            }
        };

        // удаление валидации атрибута лида
        $scope.deleteLeadValidate = function( validate ){

            // проверка, была ли валидация уже сохранен на сервере
            // (есть или нет id)
            if( validate.id == 0){
                // если валидация еще небыла сохранен на сервере
                // просто удаляем ее

                // удаление валидации атрибута по типу
                switch ($scope.attrEditor.lead.currentType){

                    // атрибут с типом 'textarea'
                    case 'textarea':
                        // находим индекс элемента
                        var index = $scope.attrEditor.lead.editors.textarea.data.validate.indexOf(validate);
                        // удаляем элемент
                        $scope.attrEditor.lead.editors.textarea.data.validate.splice(index, 1);
                        break;

                    // атрибут с типом 'input'
                    case 'input':
                        // находим индекс элемента
                        var index = $scope.attrEditor.lead.editors.textinput.data.validate.indexOf(validate);
                        // удаляем элемент
                        $scope.attrEditor.lead.editors.textinput.data.validate.splice(index, 1);
                        break;

                    default:
                        break;
                }

            }else{
                // если валидация уже сохранена на сервере

                // добавляем в модель валидации элемент delete
                validate.delete = true;
            }
        };

        /**
         * Действие по выбору селекта типа атрибута лида
         *
         */
        $scope.leadSelectedTypeAction = function(){

            // проверка выбранно что-то в селекте или нет
            if($scope.attrEditor.lead.selectedType != 0){
                // если выбранно

                // задаем текущий тип атрибута
                $scope.attrEditor.lead.currentType = $scope.attrEditor.lead.selectedType;

                // выбор типа формы редактора в завичимости от типа атрибута
                switch ( $scope.attrEditor.lead.selectedType ) {

                    case 'email':
                        showLeadEmailNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    case 'textarea':
                        showLeadTextareaNewAttr($scope.attrEditor.lead.selectedType);
                        break;

                    case 'input':
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

        /**
         * Удаление опции атрибута
         *
         */
        $scope.deleteLeadAttr = function( attr ){

            // проверка, был ли атрибут уже сохранен на сервере
            // (есть или нет id)
            if( attr.id == 0){
                // если атрибут еще не был сохранен на сервере
                // просто удаляем его

                // находим индекс элемента
                var index = $scope.data.lead.values.indexOf(attr);
                // удаляем элемент
                $scope.data.lead.values.splice(index, 1);

            }else{
                // если атрибут уже сохранен на сервере

                // добавляем в модель атрибута элемент delete
                attr.delete = true;
            }
        };

        /**
         * Блокировка дополнительного поля валидации лида
         *
         * при некоторых полях валидации, дополнительное поле не требуется
         * поэтому блокируется
         */
        $scope.IsAdditionalValidationFieldDisabled = function( field ){
            // данные о полях, которые нужно блокировать, а какие нет
            // храняться в модели редактора атрибутов
            return $scope.attrEditor.lead.additionalValidationFieldDisabled[ field ];
        };

        /**
         * Очистка дополнительного поля валидации при смене селекта
         */
        $scope.clearAdditionalValidationField = function(validate){
            validate.vale = '';
        };


        /** Статусы */

        /**
         * Форма добавления нового статуса
         *
         *
         */
        $scope.addStatus = function(type){

            // создаем новый статус
            var newStatus =
            {
                id:0,           // id статуса
                type: type,     // тип
                stepname: '',   // имя статуса
                comment: '',    // комментарий
                outerId: ++sphereStatusesCount,
                position: $scope.data.threshold.values[type].length + 1 // позиция
            };

            // проверка типа статуса
            if( type == 5 ){
                // если сделка, добавляем первое значение из типов сделки
                newStatus['additional_type'] = String( $scope.data.dealsTypes[0].id );
            }
            
            // добавляем статус в модель
            $scope.data.threshold.values[type].push( newStatus );
    
            // инициализация селектбоксов
            setTimeout(function(){
        
                // селектбоксы типов сделок
                $('.deals_types_selectbox').select2({
                    // allowClear: true
                });
        
            }, 500);
            
            // перестройка транзитов статусов (уже ненужно)
            //makeStatusTransitions();
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
                var index = $scope.data.threshold.values[status.type].indexOf(status);
                // удаляем элемент
                $scope.data.threshold.values[status.type].splice(index, 1);

            }else{
                // если статус уже сохранен на сервере

                // добавляем в модель статуса элемент delete
                status.delete = true;
            }


            // удаление транзитов в которых учавствует статус

            // перебираем все транзиты
            angular.forEach($scope.data.statusTransitions, function( transition ) {

                // если "предыдущий внешний статус" либо текущий внешний статус транзита равен внешнему id статуса
                if(transition.outer_previous_status_id == status.outerId || transition.outer_status_id == status.outerId){
                    // удаляем этот транзит
                    $scope.deleteStatusTransition( transition );
                }
            });

        };



        /** Транзиты по статусам */

        /**
         * Добавление нового транзита
         *
         *
         */
        $scope.addStatusTransition = function(){

            // создаем новый статус
            var newStatusTransition =
            {
                id: 0,

                previous_status_id: 0,
                status_id: 0,

                outer_previous_status_id: "",
                outer_status_id: "",

                position: $scope.data.statusTransitions.length + 1, // позиция

                transition_direction: 1,
                rating_1: 20,
                rating_2: 40,
                rating_3: 60,
                rating_4: 80,
                rating_5: 100
            };

            // добавляем статус в модель
            $scope.data.statusTransitions.push( newStatusTransition );


            setTimeout(function(){

                $('.transition_selectbox').select2({
                    allowClear: true
                });
            }, 10);

        };


        /**
         * Изменение направленности транзита статусов
         *
         */
        $scope.changeTransitionDirection = function(transition){

            // проверка текущей направленности транзита

            if(transition.transition_direction == 1){
                // если транзит прямой

                // транзит меняется на обратный
                transition.transition_direction = 2;

                // transition.rating_1 = 100;
                // transition.rating_2 = 80;
                // transition.rating_3 = 60;
                // transition.rating_4 = 40;
                // transition.rating_5 = 20;

            }else{
                // если транзит обратный

                // транзит меняется на прямой
                transition.transition_direction = 1;

                // transition.rating_1 = 20;
                // transition.rating_2 = 40;
                // transition.rating_3 = 60;
                // transition.rating_4 = 80;
                // transition.rating_5 = 100;
            }
        };


        /**
         * Сравнение двух оценок в транзите
         *
         * возвращает true либо false
         *
         */
        function transitionCheck(transition, first, second){

            // проверка, не является ли значение в first пустым
            if( transition['rating_' + first] == '' ){
                // выставляем значение в 0
                transition['rating_' + first] = 0;
            }

            // пробуем преобразовать первый аргумент в число
            var firstInt = parseInt( transition['rating_' + first] );

            // проверка данных в первом аргументе
            if( !firstInt ){
                // если это строка

                // заводим данные в ноль
                transition['rating_' + first] = 0;

            }else{
                // если число

                // проверка диапазона чисел
                if( firstInt > 100 ){
                    // если значение больше 100

                    // заводим значение в 100
                    transition['rating_' + first] = 100;

                }else{
                    // если не больше 100 или 100

                    // добавляем число в значение
                    transition['rating_' + first] = firstInt;
                }
            }


            // проверка второго значения, если оно есть
            if(second != undefined){

                // проверка, не является ли значение в second пустым
                if( transition['rating_' + second] == '' ){
                    // выставляем значение в 0
                    transition['rating_' + second] = 0;
                }

                // пробуем преобразовать второй аргумент в число
                var secondInt = parseInt( transition['rating_' + second] );

                // проверка данных во втором аргументе
                if( !secondInt ){
                    // если это строка

                    // заводим данные в ноль
                    transition['rating_' + second] = 0;

                }else{
                    // если число

                    // проверка диапазона чисел
                    if( secondInt > 100 ){
                        // если значение больше 100

                        // заводим значение в 100
                        transition['rating_' + second] = 100;

                    }else{
                        // если не больше 100 или 100

                        // добавляем число в значение
                        transition['rating_' + second] = secondInt;
                    }
                }

                // проверка на совпадение значений
                if( transition['rating_' + first] == transition['rating_' + second] ){
                    // если значения совпадают - возвращается false
                    return false;
                }


                // проверка направления

                // если направление прямое
                if( transition.transition_direction == 1 ){
                    // при прямом направле предыдущее значение должно быть меньше последующего
                    if( transition['rating_' + first] > transition['rating_' + second] ){
                        // если предыдущее значение больше - возвращается false
                        return false;
                    }
                }

                // если направление обратное
                if( transition.transition_direction == 2 ){
                    // при обратном направлении предыдущее значение должно быть больше последующего
                    if( transition['rating_' + first] < transition['rating_' + second] ){
                        // если предыдущее значение меньш - возвращается false
                        return false;
                    }
                }
            }

            return true;
        }


        /**
         * Проверка данных в ячейке транзита
         *
         *
         * rating =
         *     col
         *     badly
         *     secondary
         *     satisfactorily
         *     good
         *
         * возвращает либо true либо false
         */
        $scope.transitionInspection = function( transition, rating ){

            // обработка значения транзита в зависимости от заданного уровня оценки
            switch (rating) {

                case 'col':
                    // если оценка col

                    // проверка между 1 и 4 оценкой транзита
                    return transitionCheck(transition, 1, 4);
                    break;

                case 'badly':
                    // если оценка badly

                    // проверка между 1 и 2 оценкой транзита
                    return transitionCheck(transition, 1, 2);
                    break;

                case 'secondary':
                    // если оценка secondary

                    // проверка между 2 и 3 оценкой транзита
                    return transitionCheck(transition, 2, 3);
                    break;

                case 'satisfactorily':
                    // если оценка satisfactorily

                    // проверка между 3 и 4 оценкой транзита
                    return transitionCheck(transition, 3, 4);
                    break;

                case 'good':
                    // если оценка good

                    // проверка между 1 и 4 оценкой транзита
                    return transitionCheck(transition, 1, 4);
                    break;

                default:
                    // если уровень не задан

                    // возвращается false
                    return false;
                    break;
            }
        };


        /**
         * Проверка правильного направления транзита
         *
         *
         * transition
         * транзит
         *
         * rating
         * уровень оценки (от 1 до 5)
         *
         * возвращает либо true либо false
         */
        $scope.transitionDirectionCheck = function( transition, rating ){

            // обработка значения транзита в зависимости от заданного уровня оценки
            switch (rating) {

                case 1:
                    // если оценка 1

                    var aa = parseInt( transition.rating_1 );

                    console.log( typeof aa + ' -> ' + transition.rating_1 + ' -> ' + aa );

                    //if(typeof transition.rating_1 == 'string'){
                    //    transition.rating_1 = null;
                    //}

                    // если поле пустое возвращаем false
                    if(transition.rating_1 === ''){ return false; }

                    // проверка направления транзита
                    if( transition.transition_direction == 1 ){
                        // если транзит прямой

                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_1) < parseInt(transition.rating_2);

                    }else{
                        // если транзит обратный

                        return parseInt(transition.rating_1) > parseInt(transition.rating_2);
                    }

                    break;

                case 2:
                    // если оценка 2

                    // если поле пустое возвращаем false
                    if(transition.rating_2 === ''){ return false; }

                    // проверка направления транзита
                    if( transition.transition_direction == 1 ){

                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_2) < parseInt(transition.rating_3) && parseInt(transition.rating_1) < parseInt(transition.rating_2);

                    }else{
                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_2) > parseInt(transition.rating_3) && parseInt(transition.rating_1) > parseInt(transition.rating_2);
                    }
                    break;

                case 3:
                    // если оценка 3

                    // если поле пустое возвращаем false
                    if(transition.rating_3 === ''){ return false; }

                    // проверка направления транзита
                    if( transition.transition_direction == 1 ){

                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_3) < parseInt(transition.rating_4) && parseInt(transition.rating_2) < parseInt(transition.rating_3);

                    }else{
                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_3) > parseInt(transition.rating_4) && parseInt(transition.rating_2) > parseInt(transition.rating_3);
                    }

                    break;

                case 4:
                    // если оценка 4

                    // если поле пустое возвращаем false
                    if(transition.rating_4 === ''){ return false; }

                    // проверка направления транзита
                    if( transition.transition_direction == 1 ){

                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_4) < parseInt(transition.rating_5) && parseInt(transition.rating_3) < parseInt(transition.rating_4);

                    }else{
                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_4) > parseInt(transition.rating_5) && parseInt(transition.rating_3) > parseInt(transition.rating_4);
                    }

                    break;

                case 5:
                    // если оценка 5

                    // если поле пустое возвращаем false
                    if(transition.rating_5 === ''){ return false; }

                    // проверка направления транзита
                    if( transition.transition_direction == 1 ){

                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_4) < parseInt(transition.rating_5);

                    }else{
                        // проверяем чтобы предыдущая оценка была меньше последующей
                        return parseInt(transition.rating_4) > parseInt(transition.rating_5);
                    }

                    break;

                default:
                    // если уровень не задан

                    // возвращается false
                    return true;
                    break;
            }
        };


        /**
         * Отображение/скрытие сепаратора статуса
         *
         *
         */
        $scope.statusSeparator = function( type ){

            var result = 0;

            angular.forEach($scope.data.threshold.values[type], function( status ){


                return status['delete'] ? false : ++result;
            });


            return result != 0;
        };


        /**
         * Отключение селекта "to status"
         *
         */
        $scope.toStatusDisabled = function( transition ){

            if( transition.outer_previous_status_id=='' ){

                transition.outer_status_id = false;
                return true;
            }

            return false;
        };


        /**
         * Показывает/скрывает опции toStatus
         *
         */
        $scope.toStatusOptionShow = function( transition, option ){

            // если эта опция выбрана в предыдущих статусах, опция скрывается в toStatus
            if( transition.outer_previous_status_id == option.outerId ){
                return false;
            }

            if( transition.outer_previous_status_id != '0' ){

                if( option['type'] == 4 ){
                    return false;
                }
            }

            return true;
        };


        /**
         * Удаление статуса
         *
         *
         * при удалении элемент скрывается
         * и ему добавляется элемент delete
         * на сервере этот элемент будет удален из базы
         */
        $scope.deleteStatusTransition = function( transition ){

            // проверка, был ли транзит уже сохранен на сервере
            // (есть или нет id)

            if( transition.id == 0){
                // если транзит еще небыл сохранен на сервере
                // просто удаляем его

                // находим индекс элемента
                var index = $scope.data.statusTransitions.indexOf(status);
                // удаляем элемент
                $scope.data.statusTransitions.splice(index, 1);

            }else{
                // если транзит уже сохранен на сервере

                // добавляем в модель транзитов элемент delete
                transition.delete = true;
            }
        };


        /**
         * Создает массив транзитов статусов
         *
         *
         */
        function makeStatusTransitions(){

            // создаем массив с транзитами
            $scope.data.currentStatusTransition = [];

            $scope.data.currentStatusTransition[0] =
            {
                outerId: 'no status',
                //type: val.type,
                //index: $scope.data.threshold.values[val.type].indexOf(val)  ,
                statuses: []
            };

            // добавляем плохие лиды к no status
            $.each($scope.data.threshold.values[4], function( valKey, status){
                $scope.data.currentStatusTransition[0].statuses.push(
                    {
                        id: status.id,
                        outerId: status.outerId,
                        type: status.type,
                        index: $scope.data.threshold.values[status.type].indexOf(status),
                        levels:
                        {
                            1: 0,
                            2: 0,
                            3: 0,
                            4: 0
                        }

                    });
            });

            // добавляем рабочие лиды к no status
            $.each($scope.data.threshold.values[1], function( valKey, status){
                $scope.data.currentStatusTransition[0].statuses.push(
                    {
                        id: status.id,
                        outerId: status.outerId,
                        type: status.type,
                        index: $scope.data.threshold.values[status.type].indexOf(status),
                        levels:
                        {
                            1: 0,
                            2: 0,
                            3: 0,
                            4: 0
                        }
                    });
            });

            // добавляем отказников к no status
            $.each($scope.data.threshold.values[2], function( valKey, status){
                $scope.data.currentStatusTransition[0].statuses.push(
                    {
                        id: status.id,
                        outerId: status.outerId,
                        type: status.type,
                        index: $scope.data.threshold.values[status.type].indexOf(status),
                        levels:
                        {
                            1: 0,
                            2: 0,
                            3: 0,
                            4: 0
                        }
                    });
            });

            // добавляем непонятные лиды к no status
            $.each($scope.data.threshold.values[3], function( valKey, status){
                $scope.data.currentStatusTransition[0].statuses.push(
                    {
                        id: status.id,
                        outerId: status.outerId,
                        type: status.type,
                        index: $scope.data.threshold.values[status.type].indexOf(status),
                        levels:
                        {
                            1: 0,
                            2: 0,
                            3: 0,
                            4: 0
                        }
                    });
            });


            // перебираем все рабочие статусы и подбираю по ним статусы на которые можно перейти с этого статуса
            $.each($scope.data.threshold.values[1], function( key, val){

                var statusesLength = $scope.data.currentStatusTransition.length;

                $scope.data.currentStatusTransition[statusesLength] = {

                    id: val.id,
                    outerId: val.outerId,
                    statuses: [],
                    type: val.type,
                    index: $scope.data.threshold.values[val.type].indexOf(val)  ,
                };

                $.each($scope.data.threshold.values[1], function( statusKey, status){

                    if( val.outerId != status.outerId && val.position < status.position){
                        $scope.data.currentStatusTransition[statusesLength].statuses.push(
                        {
                            id: status.id,
                            outerId: status.outerId,
                            type: status.type,
                            index: $scope.data.threshold.values[status.type].indexOf(status),
                            levels:
                            {
                                1: 0,
                                2: 0,
                                3: 0,
                                4: 0
                            }

                        });
                    }
                });

                $.each($scope.data.threshold.values[2], function( statusKey, status){

                    $scope.data.currentStatusTransition[statusesLength].statuses.push(
                        {
                            id: status.id,
                            outerId: status.outerId,
                            type: status.type,
                            index: $scope.data.threshold.values[status.type].indexOf(status),
                            levels:
                            {
                                1: 0,
                                2: 0,
                                3: 0,
                                4: 0
                            }

                        });
                });

                $.each($scope.data.threshold.values[3], function( statusKey, status){

                    $scope.data.currentStatusTransition[statusesLength].statuses.push(
                        {
                            id: status.id,
                            outerId: status.outerId,
                            type: status.type,
                            index: $scope.data.threshold.values[status.type].indexOf(status),
                            levels:
                            {
                                1: 0,
                                2: 0,
                                3: 0,
                                4: 0
                            }
                        });
                });

            });

            // console.log( $scope.data );
            // console.log( $scope.data.currentStatusTransition );
        }


        /** Комментарии по сфере */

        /**
         * Добавление нового комментария
         *
         *
         */
        $scope.addNote = function(){

            // создаем новый комментарий
            var newNote =
            {
                id:0,       // id комментария
                note: ''    // текст комментария
                //position: $scope.data.notes.length + 1 // позиция
            };

            // добавляем статус в модель
            $scope.data.notes.push( newNote );
        };

        /**
         * Удаление комментария
         *
         *
         * при удалении элемент скрывается
         * и ему добавляется элемент delete
         * на сервере этот элемент будет удален из базы
         */
        $scope.deleteNote = function( note ){

            // проверка, был ли статус уже сохранен на сервере
            // (есть или нет id)

            if( note.id == 0){
                // если заметка еще небыла сохранена на сервере
                // просто удаляем его

                // находим индекс элемента
                var index = $scope.data.notes.indexOf(note);
                // удаляем элемент
                $scope.data.notes.splice(index, 1);

            }else{
                // если элемент уже сохранен на сервере

                // добавляем в модель заметок элемент delete
                note.delete = true;
            }
        };


        /** Общее */

        /**
         * Действие после скрытия модального окна
         *
         */
        $('#modal-page').on('hidden.bs.modal', function (e) {

            // возвращаем данные редактора в начальное состояние
            // клонируем модель чтобы не перебивать данные
            $scope.attrEditor = JSON.parse( JSON.stringify( attrEditorData ) );
            $scope.$apply($scope.attrEditor);
        });

        /**
         * Позиционирование елементов по индексу
         *
         *
         * Индекс итема соответствует позиции,
         * если сортировка идет по 'position'.
         * Поэтому позиция элемента == индекс+1
         */
        $scope.positioning = function( list ){
            // перебираем список и выставляем соответствующую позицию каждому элементу

            //console.log( $scope.data );

            angular.forEach(list, function(item, i){

                item.position = i+1;
            });

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

                /**
                 * Перебираем все статусы и добавляем каждому внешний id (outerId)
                 *
                 * просто уникальный идентификатор в основном чтобы как то идентифицировать
                 * только что созданные статусы, у который id равен 0
                 */
                $.each(data.threshold.values, function( key, val){
                    $.each(val, function( valKey, status){
                        // добавляем внешние id
                        status.outerId = ++sphereStatusesCount;
                        // формируем объект в котором статус будет соответствовать вшеншему статусу
                        statusToOuterId[status.id] = String( status.outerId );
                        outerIdToStatus[status.outerId] = String( status.id );
                    });
                });

                // преобразовываем данные фильтра агента в булев тип
                $.each(data.cform.values, function( key, val ){
                    //val.vale[0] = (val.vale[0] == 1);
                    $.each(val.option, function( key, opt ){
                        opt.vale = (opt.vale == 1);
                    });

                });

                /**
                 * Перебираю все транзиты статусов и преобразовываю id в строку
                 *
                 */
                $.each(data.statusTransitions, function(key, transition){
                    transition.outer_status_id = String( statusToOuterId[transition.status_id] );
                    transition.outer_previous_status_id = String( statusToOuterId[transition.previous_status_id] );
                });

                //data.statusTransitions[0].status_id = String( data.statusTransitions[0].status_id );
                //data.statusTransitions[0].previous_status_id = String( data.statusTransitions[0].previous_status_id );


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
                // срок по статусу uncertian
                data.opt.variables.lead_uncertain_status_interval_month.values = String( data.opt.variables.lead_uncertain_status_interval_month.values );
                data.opt.variables.lead_uncertain_status_interval_days.values = String( data.opt.variables.lead_uncertain_status_interval_days.values );
                data.opt.variables.lead_uncertain_status_interval_hours.values = String( data.opt.variables.lead_uncertain_status_interval_hours.values );
                data.opt.variables.lead_uncertain_status_interval_minutes.values = String( data.opt.variables.lead_uncertain_status_interval_minutes.values );

                // преобразовываем статус в строку (иначе выпадающее меню на него не реагирует
                data.opt.variables.status.values = String( data.opt.variables.status.values );
    
                // пореборазование дополнительного статуса сделки в строку
                angular.forEach( data.threshold.values[5], function( status ){
                    
                    status.additional_type = String( status.additional_type );
                });
                
                // объект с собранными статусами
                data.collectingStatuses =
                {
                    process: false,
                    uncertain: false,
                    refuseniks: false,
                    bad: false,
                    deal: false,
                };

                // выбираем собирательные статусы по типу
                angular.forEach( data.threshold.values[6], function( status ){
                    
                    switch(status.additional_type){
                        
                        case 1:
                            data.collectingStatuses.process = status;
                            break;
    
                        case 2:
                            data.collectingStatuses.uncertain = status;
                            break;
    
                        case 3:
                            data.collectingStatuses.refuseniks = status;
                            break;
    
                        case 4:
                            data.collectingStatuses.bad = status;
                            break;
    
                        case 5:
                            data.collectingStatuses.deal = status;
                            break;
                    }
                });
                
                
                // отдаем модель
                $scope.data = data;

                // создаем транзиты из текущих статусов
                makeStatusTransitions();

                var dbStatusTransitions = {};

                console.log( data);

                // оформляем данные по сфере в объект
                $.each(data.statusTransitions, function(key, val){

                    //console.log( dbStatusTransitions[val.previous_status_id] );

                    if( dbStatusTransitions[val.previous_status_id] == undefined ){
                        dbStatusTransitions[val.previous_status_id] ={};
                    }

                    dbStatusTransitions[val.previous_status_id][val.status_id] = val;
                });

                // инициализация селектбоксов
                setTimeout(function(){

                    // селектбоксы транзитов
                    $('.transition_selectbox').select2({
                        allowClear: true
                    });
                    
                    // селектбоксы типов сделок
                    $('.deals_types_selectbox').select2({
                        // allowClear: true
                    });
                    
                }, 500);

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

                    // проверка ответа с сервера
                    if( data.status == 'true' ){
                        // если статус true (все прошло нормально

                        // переходим на страницу редактирования
                        location.href = data.route;

                    }else if( data.status == 'error' ){
                        // если есть ошибки валидации

                        // записываем все данные в localstorage
                        localStorage.setItem( 'errors', data.errors );

                        // переходим на страницу редактирования
                        location.href = data.route;

                    }else{
                        // непонятный ответ сервера

                        // идем на главную страницу
                        location.href = '/';
                    }

                })
                .error(function (data, status, header, config) {
                    alert('Error');
                });
        };


        /**
         * Проверка переменной с ошибками
         *
         * если она есть - выводим и обнуляем
         *
         */
        var validateErrors = localStorage.getItem('errors');

        if( validateErrors != 'false' && validateErrors != null ){
            //console.log( JSON.parse( validateErrors ) );

            $scope.errorContent = JSON.parse( validateErrors);
            $scope.errorSwitch = true;
            localStorage.removeItem('errors');
        }
    });

