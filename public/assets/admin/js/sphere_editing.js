
var app = angular.module('app', [])

    .controller('SphereCtrl', function ( $scope, $http, $compile ) {


        /** Атрибуты агента */

        /**
         * Показ модального окна создания атрибута агента
         *
         */
        $scope.agentAddAttrShow = function(){

            $('#modal-page').modal();

        };





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

