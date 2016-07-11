@extends('layouts.master')

@section('styles')
<style>

    table.user_data{
        margin: 0 auto;
    }

    table.user_data th{
        padding: 20px;
        background: lightgray;
        border-bottom: solid black 1px;
        border-right: solid black 1px;
        border-top: solid black 1px;
        text-align: center;
        vertical-align: middle;
    }

    table.user_data th:first-child{
        border-left: solid black 1px;
    }

    table.user_data td{
        padding: 20px;
        border-bottom: solid black 1px;
        border-right: solid black 1px;
        border-top: solid black 1px;
        text-align: center;
        vertical-align: middle;

    }

    table.user_data td:first-child{
        border-left: solid black 1px;
    }

    .view_data{
        margin: 30px 0 30px 20%;

    }

    table.all_user_data td{
        padding: 10px;
        border-bottom: solid black 1px;
        border-right: solid black 1px;
        border-top: solid black 1px;
        text-align: left;
        vertical-align: middle;
    }

    table.all_user_data td:first-child{
        border-left: solid black 1px;
        font-weight: bold;
        text-align: left;
        background: lightgray;
    }

    .darkGrey{
        background: #A29F9F !important;
    }




</style>
@stop


@section('content')
    <table class="user_data">
        <thead>
            <tr>
                <th>icon</th>
                <th>date</th>
                <th>name</th>
                <th>phone</th>
                <th>email</th>
            </tr>
        </thead>
        <tbody>

            @foreach($leads as $lead)
                <tr class="all_data" data_id="{{$lead['lead_id']}}">
                    <td></td>
                    <td>{{$lead['date']}}</td>
                    <td>{{$lead['name']}}</td>
                    <td>{{$lead['phone']}}</td>
                    <td>{{$lead['email']}}</td>

                </tr>
            @endforeach

        </tbody>
    </table>

    <div class="view_data">

    </div>

@stop


@section('scripts')
<script>
    $('.all_data').bind('click', function(){

        var view_data = $('.view_data');
        var id = $(this).attr('data_id');

        if( (view_data.html()==false) || (id != $('#all_data_table').attr('allDataId')) ){
            $.post('open_leads', {'id': id, '_token': '{{csrf_token()}}'}, function( data ){

                view_data.empty();

                var fields = ['icon', 'date', 'name', 'phone', 'email', 'Radio', 'Checkbox'];

                var table = $('<table/>');

                table.attr('allDataId', id);
                table.attr('id', 'all_data_table');
                table.attr('class', 'all_user_data');

                $.each( fields, function( i, val ){

                    var tr =$('<tr/>');
                    var td1 =$('<td/>');
                    var td2 =$('<td/>');

                    if( val == 'Radio' || val == 'Checkbox' ){
                        td1.attr('class', 'darkGrey')
                    }

                    td1.text(val).appendTo(tr);
                    td2.text(data[i]).appendTo(tr);

                    tr.appendTo(table);

                } );
                view_data.append(table);

            }, 'json');

        }else {

                view_data.empty();
        }
    });
</script>
@stop




