var tables = [];

$(function(){
	if ($.isFunction($.fn.selectBoxIt)) {
	    $("select:not(.notSelectBoxIt)").selectBoxIt();
	}

    //$('select').select2({
    //    allowClear: true
    //});

	if ($.isFunction($.fn.datepicker)) {
		$(".datepicker").each(function (i, el) {
			var $this = $(el),
					opts = {
						format: $this.attr('format') || 'mm/dd/yyyy',
						startDate:  $this.attr('startDate') || '',
						endDate:  $this.attr( 'endDate') || '',
						daysOfWeekDisabled:  $this.attr('disabledDays') || '',
						startView:  $this.attr('startView') || 0,
					},
					$n = $this.next();
			$this.datepicker(opts);
			if ($n.is('.input-group-addon') && $n.has('a')) {
				$n.on('click', function (ev) {
					ev.preventDefault();
					$this.datepicker('show');
				});
			}
		});
	}


	if ($.isFunction($.fn.validate)) {
		$(".validate").validate();
	}

	$(document).on('click', ".dialog", function(){
		var href=$(this).attr("href");

		if($(this).hasClass('leadCreateLink')) {
		    $('#errorCreateLead').find('.alert').remove();
        }

		$.ajax({
			url:href,
			success:function(response){
				var dialog = bootbox.dialog({
					message:response,
					show: false
				});
				dialog.on("show.bs.modal", function() {
					$(this).find('.ajax-form').ajaxForm(function(resp) {
                        $(document).find('.leadCreateForm .alert').remove();
                        if( resp[0] == 'OrganizerItemError' ) {

                            $.each(resp['errors'], function (key, error) {
                                $('#'+key).addClass('has-error').find('.help-block').html(error).show();

                                $('#'+key+' :input').on('change', function () {
                                    $('#'+key).removeClass('has-error').find('.help-block').empty();
                                });
                            });

                        } else {
                            if(resp['status'] == undefined) {
                                dialog.modal('hide');
                            }
                        }

                        if (resp=='reload') location.reload();

						if ( resp[0]=='OrganizerItemsaved' && resp['status'] == undefined ){

                            // получение токена
                            var token = $('meta[name=csrf-token]').attr('content');

                            $.post( getOrganizerRoute, { 'id': resp[1], '_token': token }, function( data ){

                                addOrganizerRow( data[0], data[1], data[2], data[3] );
                            });

                        }
                        if ( resp[0] == 'OrganizerItemUpdated' && resp['status'] == undefined ) {

							// получение токена
							var token = $('meta[name=csrf-token]').attr('content');

							$.post( getOrganizerRoute, { 'id': resp[1], '_token': token }, function( data ){
							    console.log(data);

								updateOrganizerRow( data[0], data[1], data[2], data[3] );
							});

						}

						if( resp['status'] != undefined && resp['status'] == 'LeadCreateError' ) {
                            $(document).find('.leadCreateForm').prepend('<div class="alert alert-danger" style="margin-top: 16px" role="alert"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'+resp['message']+'</div>');
                        }

						if( resp['status'] != undefined && resp['status'] == 'LeadCreateSuccess' ) {
                            dialog.modal('hide');
                            $('#errorCreateLead').show().find('.alertWrap').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'+resp['message']+'</div>');
                        }

					});
				});
				dialog.modal("show");
			}
		});
		return false;
	});

	if ($.isFunction($.fn.DataTable)) {


		$('.dataTable').DataTable({
			responsive: true
		});

        $('.dataTable').on( 'draw.dt', function () {
            $("select").selectBoxIt();
        } );


        /**
         * Подключение dataTable к таблице с лидами на обработку оператору
         *
         *
         */
		var tableOperatorLeads = $('.dataTableOperatorLeads').DataTable({
			responsive: true
		});

        /**
         * Подключение selectBoxIt к таблице с лидами на обработку оператору
         *
         */
        $('.dataTableOperatorLeads').on( 'draw.dt', function () {
            $("select").selectBoxIt();
        } );


        $('.reset_operator_table').bind('click', function(){

            tableOperatorLeads.order([ [2,'desc'], [3,'asc'] ]).draw();
        });


        $('.openLeadsTable').DataTable({
			autoWidth: false,
			responsive: true,
			"aoColumnDefs": [
				{ "sWidth": "150px", "aTargets": [ 1 ] },

			]
		});

		$('.openLeadsTable').on( 'draw.dt', function () {
            $("select").selectBoxIt();
        } );


        /**
         * Изменяет высоту выпадающего меню на странице openLeads агента
         *
         * стилями и прочьими средствами невозможно выровнять высоту выпадающего меню
         * под высоту ячейки. Только таким образом.
         *
         */
        function dropMenuResize(){

            // выбираем все ячейки с выпадающим меню в таблице
            var allCells = $('table > tbody > tr > td.select_cell');

            // путь к дроб меню относительно ячейки
            var dropMenu = 'span.selectboxit-container.selectboxit-container';

            // перебираем все ячейки
            $.each( allCells, function( key, cell ){

                // выбираем высоту ячейки
                var cellHeight = $(cell).height();

                // присваиваем высоту ячейки выбадающему меню (минус размер бордюров, 2 пикселя)
                $(cell).find(dropMenu).height( cellHeight-2, 'important' );

            });

        }



        /** Изменение высоты выпадающего списка на странице открытых лидов агента */

        // изменяем высту при загрузске страницы
        dropMenuResize();

        // изменяем высту при изменении размера экрана (и ячейки, соответственно)
        $(window).resize(function() {
            dropMenuResize();
        });

	}

	/** наполняет таблицу обтеин на странице где отдельная таблица для каждой маски */
	$('table.ajax-dataTableId').each(function() {
		var $table = $(this);

        var mId = $table.attr('mask_id');

		var $container = $table.closest('#dataTables_container_' + mId);

        tables[mId] = $table.DataTable({
            "destroy": true,
			"searching": false,
			"lengthChange": false,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"data": function (d) {
					var filter = {};
					$container.find(".table_filter_" + mId).each(function () {
						if ($(this).data('name') && $(this).data('js') != 1) {
							filter[$(this).data('name')] = $(this).val();
						}
					});
					d['filter'] = filter;

                    d['maskId'] = mId;

				}
			},

            "responsive": true
        });

		$container.find(".table_filter_" + mId).change(function () {
			if ($(this).data('js') == '1') {
				switch ($(this).data('name')) {
					case 'pageLength':
						if ($(this).val()) tables[mId].page.len($(this).val()).draw();
						break;
					default:
						;
				}
			} else {
                tables[mId].ajax.reload();
			}
		});

		$container.delegate('.ajax-link', 'click', function () {
			var href = $(this).attr('href');
			$.ajax({
				url: href,
				method: 'GET',
				success: function (resp) {
					tables[mId].ajax.reload();
                    $('.alertContent_' + mId).html(resp);
                    $('.alert_' + mId).removeClass('hidden');
				}
			});
			return false;
		});
        tables[mId].ajax.reload();


        // при изменении количества строк в таблице
        $table.on( 'draw.dt', function(){

            // заносим количество строк в значек возле маски
			$('#badge_' + mId).text( tables[mId].data().length );
        });
	});


    /** наполняет таблицу обтеин на странице где только одна таблица с масками */
    $('.ajax-dataTable').each(function() {

        // выбираем таблицу с фильтром
        var $table = $(this);

        // выбираем id сферы
        var sphereId = $table.attr('sphere_id');

        // выбираем контейнер с таблицей лидов
        //var $container = $table.closest('.dataTables_container_' + sphereId);
        var $container = $('.dataTables_container_' + sphereId);



        var dTable = $table.DataTable({
            "destroy": true,
            "searching": false,
            "lengthChange": false,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "data": function (d) {

                    // переменная с данными по фильтру
                    var filter = {};

                    // перебираем фильтры и выбираем данные по ним
                    $container.find(':input.dataTables_filter').each(function () {

                        // если есть name и нет js
                        if ($(this).data('name') && $(this).data('js') != 1) {

                            // заносим в фильтр данные с именем name и значением опции
                            filter[$(this).data('name')] = $(this).val();
                        }
                    });

                    // данные фильтра
                    d['filter'] = filter;
                    // id сферы
                    d['sphere_id'] = sphereId;
                }
            },

            "responsive": true
        });


        // обработка фильтров таблицы при изменении селекта
        $container.find(':input.dataTables_filter').change(function () {

            // проверяем параметр data-js
            if ($(this).data('js') == '1') {
                // если js равен 1

                // перечисляем имена
                switch ($(this).data('name')) {

                    // если у селекта имя pageLength
                    case 'pageLength':
                        // перерисовываем таблицу с нужным количеством строк
                        if ($(this).val()) dTable.page.len($(this).val()).draw();
                        break;
                    default:
                        ;
                }
            } else {
                // если js НЕ равен 1

                // просто перезагружаем таблицу
                dTable.ajax.reload();
            }
        });


        // обработка клика по ссылке на открытие лида (на глазик в таблице выборки)
        $container.delegate('.ajax-link.sphere_' + sphereId, 'click', function () {

            // получение ссылки линка
            var href = $(this).attr('href');

            // запрос на сервер
            $.ajax({
                // ссылка
                url: href,
                // метод
                method: 'GET',
                // действия после получения ответа
                success: function (resp) {
                    // обновить контент таблицы
                    dTable.ajax.reload();
                    // записываем в блок результата ответ с сервера
                    $('#open_result_content_' + sphereId).html(resp);
                    // делаем блок видимым
                    $('#open_result_' + sphereId).removeClass('hidden');
                }
            });
            return false;
        });


        // обновление таблицы выборки
        dTable.ajax.reload();
    });


    /** Контейнер выпадающего меню с балансом по всем маскам агента */

    // наполнение контейнера с данными по балансу агента , перед появлением
    $('.balance_data_container').on('show.bs.dropdown', function () {

        // получаем данные баланса из куки и преобразовываем в json
        var balanceData = JSON.parse( getCookie('balance') );

        // выбираем блок с контентом выпадающего меню
        var balance = $('#balance_data_content');

        if( balanceData.allSpheres != '' ){
            // перебираем все маски и заносим данные в выпадающее меню
            $( balanceData.allSpheres ).each(function( key, val ){

                // итем с именем маски
                var li = $('<li />');

                // дочерний блок в котором будут имена масок с количеством лидов по ним
                var childrenUl = $('<ul />');

                // добавление класса к основному блоку с масками выпадающего меню
                childrenUl.addClass('balance_masks_block');

                // добавляем имя сферы в блок
                li.text( val.name.replace( '+', ' ' ) );

                // добавляем в блок с именем сферы блок с его масками
                li.append( childrenUl );


                // проверка наличия масок в сфере

                if( val.masks.length != 0 ){
                    // если маски есть
                    // перебираем все маски и добавляем название маски и количество лидов по ней

                    $(val.masks).each(function( key, mask ){
                        // перебираем все маски

                        //if( mask.status === undefined ){ return false; }

                        // блок с именем
                        var name = $('<span />');
                        // блок с количеством лидов
                        var count = $('<span />');

                        // добавляем имя маски в блок с именем
                        name.text( mask.name.replace('+',' ') );
                        // добавляем количество лидво в блок с количеством
                        count.text( mask.leadsCount );

                        // создаем li дочернего ul блока
                        var childrenLi = $('<li />');

                        // добавляем блок с именем к дочернему li
                        childrenLi.append(name);
                        // добавляем блок с количеством лидов к дочернему li
                        childrenLi.append(count);

                        childrenUl.append(childrenLi);
                    });
                }


                //Проверка на содержание блока со сферами
                if( childrenUl.children().length == 0){
                    // если масок нет
                    // просто добавляем надпись что масок нет

                    // li дочернего блока
                    var childrenLi = $('<li />');
                    // наполнение li дочернего блока
                    childrenLi.text('no active masks ');
                    // подключение li к дочернему ul
                    childrenUl.append(childrenLi);
                }

                balance.append(li);
            });
        }else{
            // если масок нет
            // просто добавляем надпись что масок нет

            // итем с именем маски
            var li = $('<li />');
            // наполнение li дочернего блока
            li.text('no spheres ');
            // подключение li к дочернему ul
            balance.append(li);
        }

    });

    // очистка контейнера с балансом, после его сворачивания
    $('.balance_data_container').on('hidden.bs.dropdown', function () {

        // очистка блока
        $('#balance_data_content').empty();
    });

    // наполнение контейнера с данными по балансу продавца, перед появлением
    $('.salesman_balance_data_container').on('show.bs.dropdown', function () {

        // получаем данные баланса из куки и преобразовываем в json
        var balanceData = JSON.parse( getCookie('salesman_balance') );

        // выбираем блок с контентом выпадающего меню
        var balance = $('#salesman_balance_data_content');

        if( balanceData.allSpheres != '' ){
            // перебираем все маски и заносим данные в выпадающее меню
            $( balanceData.allSpheres ).each(function( key, val ){

                // итем с именем маски
                var li = $('<li />');

                // дочерний блок в котором будут имена масок с количеством лидов по ним
                var childrenUl = $('<ul />');

                // добавление класса к основному блоку с масками выпадающего меню
                childrenUl.addClass('balance_masks_block');

                // добавляем имя сферы в блок
                li.text( val.name.replace( '+', ' ' ) );

                // добавляем в блок с именем сферы блок с его масками
                li.append( childrenUl );


                // проверка наличия масок в сфере

                if( val.masks.length != 0 ){
                    // если маски есть
                    // перебираем все маски и добавляем название маски и количество лидов по ней

                    $(val.masks).each(function( key, mask ){
                        // перебираем все маски

                        //if( mask.status === undefined ){ return false; }

                        // блок с именем
                        var name = $('<span />');
                        // блок с количеством лидов
                        var count = $('<span />');

                        // добавляем имя маски в блок с именем
                        name.text( mask.name.replace('+',' ') );
                        // добавляем количество лидво в блок с количеством
                        count.text( mask.leadsCount );

                        // создаем li дочернего ul блока
                        var childrenLi = $('<li />');

                        // добавляем блок с именем к дочернему li
                        childrenLi.append(name);
                        // добавляем блок с количеством лидов к дочернему li
                        childrenLi.append(count);

                        childrenUl.append(childrenLi);
                    });
                }


                //Проверка на содержание блока со сферами
                if( childrenUl.children().length == 0){
                    // если масок нет
                    // просто добавляем надпись что масок нет

                    // li дочернего блока
                    var childrenLi = $('<li />');
                    // наполнение li дочернего блока
                    childrenLi.text('no active masks ');
                    // подключение li к дочернему ul
                    childrenUl.append(childrenLi);
                }

                balance.append(li);
            });

        }else{
            // если масок нет
            // просто добавляем надпись что масок нет

            // итем с именем маски
            var li = $('<li />');
            // наполнение li дочернего блока
            li.text('no spheres ');
            // подключение li к дочернему ul
            balance.append(li);
        }

    });

    // очистка контейнера с балансом продавца, после его сворачивания
    $('.salesman_balance_data_container').on('hidden.bs.dropdown', function () {

        // очистка блока
        $('#salesman_balance_data_content').empty();

    });
updateBalance();
});


/**
 * Функция для получение нужной куки по имени
 *
 */
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}


// Обновление данных по балансу
function updateBalance() {
    var balance = getCookie('balance');
    if($('#salesman_balance_data_content').length > 0) {
        balance = getCookie('salesman_balance');
    }

    if(balance == undefined) {
        return false;
    }

    // получаем данные баланса из куки и преобразовываем в json
    var balanceData = JSON.parse( balance );
    //dd(balanceData);
    var maxLeadsToBuy = 0;

    if( balanceData.allSpheres != '' && balanceData.allSpheres != undefined ) {
        // проходим по всех сферам
        $.each(balanceData.allSpheres, function (ind, sphere) {

            // проходим по всем маскам
            // и ищем максимальное кол-во возможных открытий лида
            $.each(sphere.masks, function (i, mask) {
                if( mask.leadsCount > maxLeadsToBuy ) {
                    maxLeadsToBuy = mask.leadsCount;
                }
            })
        });
    }

    $('#balance_data span').html(maxLeadsToBuy);
}