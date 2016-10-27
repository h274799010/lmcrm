<div id="footer">
    <p class="text-muted credit"><span style="text-align: left; float: left">&copy; 2016 <a href="#">LM CRM</a></span>
        <!--<span class="hidden-phone" style="text-align: right; float: right">Powered by: <a href="http://laravel.com/" >Laravel 5</a></span>-->

        <br>
    </p>

    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-language"></i> {{ trans('site/site.languages') }} <i class="fa fa-caret-down"></i></a>
    <ul class="dropdown-menu" role="menu">
        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
            <li>
                <a rel="alternate" hreflang="{{$localeCode}}" href="{{LaravelLocalization::getLocalizedURL($localeCode) }}">
                    {{ $properties['native'] }}
                </a>
            </li>
        @endforeach
    </ul>

</div>

<div class="modal fade" tabindex="-1" role="dialog" id="creditsDetails" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Credits details</h4>
            </div>

            <div class="modal-body">

                @foreach( $balance['allSpheres'] as $sphere )

                   <div><b>{{ $sphere['name']  }}</b></div>

                    <table class="table">

                        @forelse( $sphere['masks'] as $mask)

                            @if( $mask['status'] != 0 )
                            <tr>
                                <td>{{$mask['name']}}</td>
                                <td style="text-align: right">{{$mask['leadsCount']}}</td>
                            </tr>
                            @endif

                        @empty

                            <tr>
                                <td>
                                    no active masks
                                </td>
                            </tr>

                        @endforelse

                    </table>

                    <hr>

                @endforeach

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>


        </div>
    </div>
</div>