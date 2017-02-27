@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') Operators :: @parent
@stop

{{-- Content --}}
@section('main')


    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb" style="margin-bottom: 5px;">
            <li><a href="/">LM CRM</a></li>
            <li class="active">Operator statistic</li>
            {{--<li class="active">Operator: {{ $statistic['user']['email'] }}</li>--}}
        </ul>
    </div>


    {{--<div class="page-header">--}}
        {{--<h3>--}}
            {{--Operators--}}
        {{--</h3>--}}
    {{--</div>--}}
    <table id="statisticSphereTable" class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Leads added</th>
            <th class="center">Leads for processing</th>
            <th class="center">Processed leads</th>

            <th class="center">Spheres</th>

            <th class="center">created date</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
            @forelse($operators as $operator)
                <tr>
                    <td class="middle">{{ $operator['email'] }}</td>
                    <td class="center middle">{{ $operator['leadsAddedCount'] }}</td>
                    <td class="center middle">{{ $operator['leadsToEdit'] }}</td>
                    <td class="center middle">{{ $operator['leadsEdited'] }}</td>

                    <td class="middle">
                        @forelse($operator['sphere'] as $index=>$sphere )

                            @if( $index == 0)
                                {{ $sphere['name'] }}
                            @else
                                , {{ $sphere['name'] }}
                            @endif

                        @empty
                            No sphere
                        @endforelse
                    </td>

                    <td class="center middle">{{ $operator['created_at']->format('d/m/Y') }}</td>
                    <td>
                        <a class="btn btn-sm btn-success" title="Statistic" href="{{ route('admin.statistic.operator', ['id'=>$operator['id']]) }}">
                            Statistic
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"></td>
                </tr>
            @endforelse
        </tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">

    </script>
@stop
