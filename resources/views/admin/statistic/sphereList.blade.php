@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') Spheres :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            Spheres
        </h3>
    </div>
    <table id="statisticSphereTable" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Date</th>
            <th>{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
            @forelse($spheres as $sphere)
                <tr>
                    <td>{{ $sphere['name'] }}</td>
                    <td>{{ $sphere['created_at'] }}</td>
                    <td>
                        <a class="btn btn-sm btn-success" title="Statistic" href="{{ route('admin.statistic.sphere', ['id'=>$sphere['id']]) }}">
                            Statistic
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2"></td>
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
