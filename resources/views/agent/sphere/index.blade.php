@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

    @forelse($spheres as $sphere)

        <h4>{!! $sphere->name !!}</h4>
        @php($masks = $agentMask->findSphereMask($sphere->id)->get())

            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{!! trans("site/mask.name") !!}</th>
                        <th>{!! trans("main.status") !!}</th>
                        <th>{!! trans("main.updated_at") !!}</th>
                        <th>{!! trans("main.action") !!}</th>
                    </tr>
                </thead>
                <tbody>

                @if( count($masks) == 0 )

                    <tr>
                        <td colspan="4">
                            нет ни одной маски
                        </td>
                    </tr>

                @else

                    @forelse($masks as $mask)

                        <tr>
                            <td>{!! $mask->name !!}</td>
                            <td>
                                @if(isset($mask->status) && $mask->status) <span class="label label-success">@lang('site/sphere.status_1')</span> @else <span class="label label-danger">@lang('site/sphere.status_0')</span> @endif</td>
                            <td>{!! $mask->updated_at !!}</td>
                            <td><a href="{{ route('agent.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>$mask->id]) }}" class="btn btn-xs" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a></td>
                        </tr>

                    @empty
                    @endforelse

                @endif

                </tbody>
            </table>

        <a href="{{ route('agent.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>0]) }}" type="button" class="btn btn-xs btn-primary add_mask"> add mask </a>


    @empty
    @endforelse

@stop


{{-- styles --}}
@section('styles')
    <style>

        .table{
            margin-bottom: 5px;
        }

        .add_mask{
            margin-bottom: 20px;
        }

    </style>
@stop



{{-- Scripts --}}
@section('scripts')
@stop
