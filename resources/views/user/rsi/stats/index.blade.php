@extends('user.layouts.default_wide')

@section('title', __('Statistiken'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Spendenstatistiken')</h4>
        <div class="card-body px-0">
            <canvas id="fundsChart" width="400" height="100"></canvas>
        </div>
    </div>
    <div class="card">
        <h4 class="card-header">@lang('Fansstatistiken')</h4>
        <div class="card-body px-0">
            <canvas id="fansChart" width="400" height="100"></canvas>
        </div>
    </div>
    <div class="card">
        <h4 class="card-header">@lang('Fleetstatistiken')</h4>
        <div class="card-body px-0">
            <canvas id="fleetChart" width="400" height="100"></canvas>
            <p class="text-center mt-3">
                Seit dem 20.11.2019 entspricht Fleet = Fans.
            </p>
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
                    backgroundColor: 'lightblue',
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

        const fleetContext = document.getElementById('fleetChart').getContext('2d');
        const fleetChart = new Chart(fleetContext, {
            type: 'line',
            data: {
                labels: {!! $labels !!},
                datasets: [{
                    backgroundColor: 'lightgreen',
                    label: 'Fleet',
                    data: {!! $fleet !!},
                }]
            },
            options: {
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return formatNumber(tooltipItem.value, 0) + ' Fleet';
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
                                return formatNumber(value, 0) + ' Fleet';
                            }
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            callback: function(value, index) {
                                return new Date(value).toLocaleDateString();
                            }
                        }
                    }]
                }
            }
        });

        const fansContext = document.getElementById('fansChart').getContext('2d');
        const fansChart = new Chart(fansContext, {
            type: 'line',
            data: {
                labels: {!! $labels !!},
                datasets: [{
                    backgroundColor: '#F08080',
                    label: 'Fans',
                    data: {!! $fans !!},
                }]
            },
            options: {
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return formatNumber(tooltipItem.value, 0) + ' Fans';
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
                                return formatNumber(value, 0) + ' Fans';
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