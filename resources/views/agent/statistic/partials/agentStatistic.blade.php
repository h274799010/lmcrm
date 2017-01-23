@foreach($spheres as $sphere)
    <h4>{{ $sphere->name }}</h4>

    <div class="row">
        <div class="col-md-4">
            <table class="table table-bordered table-striped table-hover process-statuses">
                <thead>
                <tr>
                    <th colspan="4">Процессные статусы</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 1)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-bordered table-striped table-hover undefined-statuses">
                <thead>
                <tr>
                    <th colspan="4">Не определенные</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 2)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-bordered table-striped table-hover fail-statuses">
                <thead>
                <tr>
                    <th colspan="4">Отказники</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 3)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <table class="table table-bordered table-striped table-hover bad-statuses">
                <thead>
                <tr>
                    <th colspan="4">Плохие</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 4)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        @if(isset($sphere->statusTransitions) && count($sphere->statusTransitions) > 0)
            <div class="col-md-8">
                <table class="table table-bordered table-striped table-hover performance-table">
                    <thead>
                    <tr>
                        <th colspan="5">уровень производительности</th>
                    </tr>
                    <tr>
                        <th>Статус 1</th>
                        <th>Статус 2</th>
                        <th>Процент от общего числа</th>
                        <th>Процент за выбранный период</th>
                        <th>Оценка</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sphere->statusTransitions as $status)
                        <tr>
                            <td>
                                @if(isset($status->previewStatus->stepname))
                                    {{ $status->previewStatus->stepname }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(isset($status->status->stepname))
                                    {{ $status->status->stepname }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="percent-col">{{ $status->percent }}%</td>
                            <td class="percent-col">{{ $status->percentPeriod }}%</td>
                            <td class="status_{{ $status->rating }}">{{ $status->rating }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endforeach