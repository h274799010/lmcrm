@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

        <div class="panel panel-default">
            <div class="col-md-12">
                <table class="table table-bordered table-striped table-hover dataTable">
                    <thead>
                    <tr>
                        {{--<th>{{ trans("main.action") }}</th>--}}
                        <th>{{ trans("main.status") }}</th>
                        <th>{{ trans("site/lead.updated") }}</th>
                        <th>{{ trans("site/lead.sphere") }}</th>
                        <th>{{ trans("site/lead.name") }}</th>
                        <th>{{ trans("site/lead.phone") }}</th>
                        <th>{{ trans("site/lead.email") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>{{ $lead->statusName() }} @if( $lead['status'] == 8 ) <a href="{{ route('agent.lead.deposited.details', ['lead_id'=>$lead['id']]) }}"><img src="/assets/web/icons/list-edit.png"></a> @endif</td>
                            <td>{{ $lead->updated_at }}</td>
                            <td>{{ $lead['sphere']['name'] }}</td>
                            <td>{{ $lead->name }}</td>
                            <td>{{ $lead->phone->phone }}</td>
                            <td>{{ $lead->email }}</td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>

@stop

@section('script')
    <script>

        $.extend( true, $.fn.dataTable.defaults, {
            "language": {
                "url": '{{ asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') }}'
            }
        });

    </script>
@stop
