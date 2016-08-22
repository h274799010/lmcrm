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


		$('.openLeadsTable').DataTable({
			responsive: true,
			"aoColumnDefs": [
				{ "sWidth": "150px", "aTargets": [ 1 ] },

			]
		});


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


	$('.ajax-dataTable').each(function() {
		$table=$(this);
		$container=$table.closest('.dataTables_container');
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




// todo дописать функцию

var source = new EventSource("/notice");


source.onmessage = function(event) {

	var a = $.parseJSON(event.data);

	$.each( a, function( k, notice ){

		if( notice == 'note' ){

			var noteBlock = $('#notice .notice_newLead');

			// делаем блок уведомлений видимым
			noteBlock.css('display', 'block');


			// todo включить обратно
			noticeOff('note');
		}

	});


	$('.removeNoticeIcon').bind('click', function(){
		$('#notice .notice_newLead').css('display', 'none');
	})

};





