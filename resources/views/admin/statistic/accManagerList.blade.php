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
            <th class="center">Name</th>
            <th class="center">Spheres</th>
            <th class="center">Agents</th>
            <th class="center">Date</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($accountManagers as $manager)
            <tr>
                <td>{{ $manager['email'] }}</td>
                <td class="center">
                    @forelse( $manager['spheres'] as $sphere )
                        {{ $sphere['sphere']['name'] }},
                    @empty
                    @endforelse
                </td>
                <td class="center">{{ $manager['agents'] }}</td>
                <td class="center">{{ $manager['created_at'] }}</td>
                <td>
                    <a class="btn btn-sm btn-success" title="Statistic" href="{{ route('admin.statistic.accManager', ['id'=>$manager['id']]) }}">
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
