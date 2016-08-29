var tables = [];

$(function(){
	if ($.isFunction($.fn.selectBoxIt)) {
	    $("select").selectBoxIt();
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

	$(".dialog").click(function(){
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
						///{organizer:hfgj} resp.or
                        if ( resp.split(',')[0]=='OrganizerItemsaved' ){

                            // получение токена
                            var token = $('meta[name=csrf-token]').attr('content');

                            $.post( getOrganizerRoute, { 'id': resp.split(',')[1], '_token': token }, function( data ){

                                addOrganizerRow( data[0], data[1], data[2], data[3] );
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


        $('.openLeadsTable').DataTable({
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
            $('#badge_' + mId).text( $($table).find('tr').length - 1 );
        });
	});



    /** наполняет таблицу обтеин на странице где только одна таблица с масками */
    $('.ajax-dataTable').each(function() {
        var $table=$(this);
        var $container=$table.closest('.dataTables_container');
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
                    d['filter'] = filter;
                },
            },

            "responsive": true,

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


	$('.removeNoticeIcon').bind('click', function(){
		$('#notice .notice_newLead').css('display', 'none');
	});




};





