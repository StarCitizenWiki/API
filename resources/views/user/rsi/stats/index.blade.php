@extends('user.layouts.default_wide')

@section('title', __('Statistiken'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Statistiken')</h4>
        <div class="card-body text-center">
            <div>
                <h6>@lang('Filter alle Daten'):</h6>
                <a class="btn @if($active === 100) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?skip=100">@lang('Jeder 100te Datensatz')</a>
                <a class="btn @if($active === 50) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?skip=50">@lang('Jeder 50te Datensatz')</a>
                <a class="btn @if($active === 25) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?skip=25">@lang('Jeder 25te Datensatz')</a>
                <a class="btn @if($active === 10) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?skip=10">@lang('Jeder 10te Datensatz')</a>
                <a class="btn @if($active === 5) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?skip=5">@lang('Jeder 5te Datensatz')</a>
                <a class="btn @if($active === 0) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?skip=0">@lang('Alle Daten')</a>
            </div>
            <div class="mt-3">
                <h6>@lang('Filter Zeit'):</h6>
                <a class="btn @if($from === \Carbon\Carbon::now()->subWeek()->format('Y-m-d')) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?from={{\Carbon\Carbon::now()->subWeek()->format('Y-m-d')}}">@lang('Letzte Woche')</a>
                <a class="btn @if($from === \Carbon\Carbon::now()->subMonths(1)->format('Y-m-d')) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?from={{\Carbon\Carbon::now()->subMonths(1)->format('Y-m-d')}}">@lang('Letzter Monat')</a>
                <a class="btn @if($from === \Carbon\Carbon::now()->subMonths(3)->format('Y-m-d')) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?from={{\Carbon\Carbon::now()->subMonths(3)->format('Y-m-d')}}">@lang('Letzte 3 Monate')</a>
                <a class="btn @if($from === \Carbon\Carbon::now()->subMonths(6)->format('Y-m-d')) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?from={{\Carbon\Carbon::now()->subMonths(6)->format('Y-m-d')}}">@lang('Letzte 6 Monate')</a>
                <a class="btn @if($from === \Carbon\Carbon::now()->subYear()->format('Y-m-d')) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?from={{\Carbon\Carbon::now()->subYear()->format('Y-m-d')}}">@lang('Letztes Jahr')</a>
                <a class="btn @if($from === \Carbon\Carbon::now()->subYears(2)->format('Y-m-d')) btn-dark @else btn-outline-dark @endif" href="{{ route('web.user.rsi.stat.index') }}?from={{\Carbon\Carbon::now()->subYears(2)->format('Y-m-d')}}">@lang('Letzte 2 Jahre')</a>
            </div>
        </div>
    </div>
    <div class="stats-card">
        <div class="card">
            <h4 class="card-header">@lang('Spenden')</h4>
            <div class="card-body">
                <canvas id="fundsChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="stats-card">
        <div class="card">
            <h4 class="card-header">@lang('Citizens')</h4>
            <div class="card-body">
                <canvas id="citizensChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    <script src="/js/Chart.min.js"></script>

    <script>
        function formatNumber(amount, decimalCount = 2, decimal = ",", thousands = ".") {
            try {
                decimalCount = Math.abs(decimalCount);
                decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

                const negativeSign = amount < 0 ? "-" : "";

                let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
                let j = (i.length > 3) ? i.length % 3 : 0;

                return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
            } catch (e) {
                console.log(e)
            }
        };

        const fundsContext = document.getElementById('fundsChart').getContext('2d');
        const fundsChart = new Chart(fundsContext, {
            type: 'line',
            data: {
                labels: {!! $labels !!},
                datasets: [{
                    backgroundColor: '#1090ac',
                    label: 'Funds',
                    data: {!! $funds !!},
                }]
            },
            options: {
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return formatNumber(tooltipItem.value, 0) + ' $';
                        },
                        title: function(tooltipItem) {
                            return new Date(tooltipItem[0].label).toLocaleDateString()
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            callback: function(value, index) {
                                return formatNumber(value, 0) + ' $'
                            }
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            callback: function(value, index) {
                                return new Date(value).toLocaleDateString()
                            }
                        }
                    }]
                }
            }
        });

        const citizensContext = document.getElementById('citizensChart').getContext('2d');
        const citizensChart = new Chart(citizensContext, {
            type: 'line',
            data: {
                labels: {!! $labels !!},
                datasets: [{
                    backgroundColor: '#d24418',
                    label: 'Citizens',
                    data: {!! $fans !!},
                }]
            },
            options: {
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return formatNumber(tooltipItem.value, 0) + ' Citizens';
                        },
                        title: function(tooltipItem) {
                            return new Date(tooltipItem[0].label).toLocaleDateString()
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            callback: function(value, index) {
                                return formatNumber(value, 0) + ' Citizens';
                            }
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            callback: function(value, index) {
                                return new Date(value).toLocaleDateString()
                            }
                        }
                    }]
                }
            }
        });
    </script>
@endsection