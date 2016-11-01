var tables = [];

$(function(){
	if ($.isFunction($.fn.selectBoxIt)) {
	    $("select:not(.notSelectBoxIt)").selectBoxIt();
	}

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
		$.ajax({
			url:href,
			success:function(response){
				var dialog = bootbox.dialog({
					message:response,
					show: false
				});
				dialog.on("show.bs.modal", function() {
					$(this).find('.ajax-form').ajaxForm(function(resp) {
						dialog.modal('hide');

                        if (resp=='reload') location.reload();

						if ( resp[0]=='OrganizerItemsaved' ){

                            // получение токена
                            var token = $('meta[name=csrf-token]').attr('content');

                            $.post( getOrganizerRoute, { 'id': resp[1], '_token': token }, function( data ){

                                addOrganizerRow( data[0], data[1], data[2], data[3] );
                            });

                        }
                        if ( resp[0] == 'OrganizerItemUpdated' ) {

							// получение токена
							var token = $('meta[name=csrf-token]').attr('content');

							$.post( getOrganizerRoute, { 'id': resp[1], '_token': token }, function( data ){

								updateOrganizerRow( data[0], data[1], data[2], data[3] );
							});

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

		$('.dataTableOperatorLeads').DataTable({
			responsive: true,
			"order": [[ 4, "desc" ]]
		});

        $('.dataTableOperatorLeads').on( 'draw.dt', function () {
            $("select").selectBoxIt();
        } );


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
        var $table=$(this);
        var $container=$table.closest('.dataTables_container');

        // выбираем id сферы
        var sphereId = $table.attr('sphere_id');

        var dTable = $table.DataTable({
            "destroy": true,
            "searching": false,
            "lengthChange": false,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "data": function (d) {
                    var filter = {};
                    $container.find(".dataTables_filter").each(function () {
                        if ($(this).data('name') && $(this).data('js') != 1) {
                            filter[$(this).data('name')] = $(this).val();
                        }
                    });
                    d['filter'] = filter; // данные фильтра
                    d['sphere_id'] = sphereId; // id сферы
                }
            },

            "responsive": true

        });
        $container.find(".dataTables_filter").change(function () {
            if ($(this).data('js') == '1') {
                switch ($(this).data('name')) {
                    case 'pageLength':
                        if ($(this).val()) dTable.page.len($(this).val()).draw();
                        break;
                    default:
                        ;
                }
            } else {
                dTable.ajax.reload();
            }
        });
        $container.delegate('.ajax-link', 'click', function () {
            var href = $(this).attr('href');
            $.ajax({
                url: href,
                method: 'GET',
                success: function (resp) {
                    dTable.ajax.reload();
                    $('#alertContent').html(resp);
                    $('#alert').removeClass('hidden');
                }
            });
            return false;
        });
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

                        if( mask.status === undefined ){ return false; }

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

                        if( mask.status === undefined ){ return false; }

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

                //alert(balance.children().length);
                //
                //if( balance.children().length == 0){
                //    li.text( 'гы' );
                //    balance.append(li);
                //}

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

});







// todo доработать

var source = new EventSource("/notice");


source.onmessage = function(event) {

	var a = $.parseJSON(event.data);

	$.each( a, function( k, notice ){

		if( notice == 'note' ){

			var noteBlock = $('#notice .notice_newLead');

			// делаем блок уведомлений видимым
			noteBlock.css('display', 'block');

			// выключение оповещений
			noticeOff('note');
		}

	});

    function noticeOff ( event ){

        var token = $('meta[name=csrf-token]').attr('content');


        $.post('/notified', {'event': event, '_token': token});
    }


	$('.removeNoticeIcon').bind('click', function(){
		$('#notice .notice_newLead').css('display', 'none');
	});


};



// функция для работы с куки
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}




