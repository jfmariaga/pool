<div x-data="dataalpine">

    {{-- para no cargar el CDN en todas las vistas --}}
    @push('js_extra')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @endpush

    {{-- <span class="loader_new"></span> --}}

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Dahsboard</h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card p-3">
                    <b>Filtras Dahsboard</b>
                    <div class="d-flex f_right">
                        <div class="">
                            {{-- <span x-text="$wire.desde"></span> --}}
                            <x-input type="date" model="$wire.desde" id="desde" class="form-control"></x-input>
                        </div>
                        <div class="ml-2">
                            <x-input type="date" model="$wire.hasta" id="hasta" class="form-control"></x-input>
                        </div>
                        <button type="button" x-on:click="getEstadisticas()" class="btn btn-outline-dark ml-2">Filtrar</button>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2" style="min-height: 315px !important;">
                                        <div class="header">
                                            <h5>Ejemplo torta dinamica</h5>
                                        </div>
                                        <div id="ejemplo_torta" style="height: 300px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2" style="min-height: 315px !important;">
                                        <div class="header">
                                            <h5>Ejemplo comparando 2 cosas</h5>
                                        </div>
                                        <div id="ejemplo_area" style="max-height: 200px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2" style="min-height: 315px !important;">
                                        <div class="header">
                                            <h5>Ejemplo gráfica área solo 1 cosa</h5>
                                        </div>
                                        <div id="ejemplo_area_una" style="max-height: 200px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2" style="min-height: 315px !important;">
                                        <div class="header">
                                            <h5>Ejemplo Radar, ( Como la torta pero resalta las proporciones )</h5>
                                        </div>
                                        <div id="ejemplo_radar" style="max-height: 200px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2" style="min-height: 315px !important;">
                                        <div class="header">
                                            <h5>Ejemplo barras</h5>
                                        </div>
                                        <div id="ejemplo_barras" style="max-height: 200px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2" style="min-height: 315px !important;">
                                        <div class="header">
                                            <h5>Ejemplo barras múltiples</h5>
                                        </div>
                                        <div id="ejemplo_barras_mulpiple" style="max-height: 200px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            Alpine.data('dataalpine', () => ({
                loading:        true,
                compras:        {},
                comprobante:    {},

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.tortaPrueba()
                    this.areaPrueba()
                    this.areaPruebaUna()
                    this.radarPrueba()
                    this.barrasPrueba()
                    this.barrasPruebaMultiple()
                },

                // debe mandar a consultar nuevamente las estaditicas
                getEstadisticas(){

                    // hay que limpiar las graficas anterior con un remove al indicador
                    $('#ejemplo_torta').html('')
                    $('#ejemplo_area').html('')
                    $('#ejemplo_area_una').html('')
                    $('#ejemplo_radar').html('')
                    $('#ejemplo_barras').html('')
                    $('#ejemplo_barras_mulpiple').html('')
                    
                    this.tortaPrueba()
                    this.areaPrueba()
                    this.areaPruebaUna()
                    this.radarPrueba()
                    this.barrasPrueba()
                    this.barrasPruebaMultiple()

                },

                tortaPrueba(){

                    const data = {
                        series: [20, 30, 40],
                        labels: ['hola', 'mundo', 'mundial'],
                    }
                    var ejemplo_torta_options = {
                        series: data.series,
                        chart: {
                            type: 'pie',
                            foreColor: '#A5A8AD',
                            height: 320
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: data.labels,
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: 300
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        // colors:['#ed3c89', '#33a8f2']
                    };
                    var ejemplo_torta = new ApexCharts(document.querySelector("#ejemplo_torta"),ejemplo_torta_options);
                    ejemplo_torta.render();
                },

                areaPrueba(){
                    // visitas vs registros
                    var ejemplo_area_options = {
                        stroke: {
                            curve: 'smooth'
                        },
                        chart: {
                            height:305,
                            type: 'area',
                            id: 'chart_vr',
                            foreColor: '#A5A8AD'
                        },
                        dataLabels: {
                            enabled: false
                        },
                        series: [{
                            name: 'series1',
                            data: [31, 40, 28, 51, 42]
                            }, {
                            name: 'series2',
                            data: [11, 32, 45, 32, 34]
                            }
                        ],
                        xaxis: {
                            categories: [
                                'label 1', 'label 2', 'label 3', 'label 4', 'label 5'
                            ]
                        },
                        // son los colores de cad cosa
                        colors: ['#ed3c89', '#33a8f2']
                    }
                    var ejemplo_area = new ApexCharts(document.querySelector("#ejemplo_area"), ejemplo_area_options);
                    ejemplo_area.render();
                },

                areaPruebaUna(){
                    // visitas vs registros
                    var ejemplo_area_una_options = {
                        stroke: {
                            curve: 'smooth'
                        },
                        chart: {
                            height:310,
                            type: 'area',
                            id: 'chart_vr',
                            foreColor: '#A5A8AD'
                        },
                        dataLabels: {
                            enabled: false
                        },
                        series: [{
                            name: 'series1',
                            data: [31, 40, 28, 51, 42]
                            }
                        ],
                        xaxis: {
                            categories: [
                                'label 1', 'label 2', 'label 3', 'label 4', 'label 5'
                            ]
                        },
                        // son los colores de cad cosa
                        colors: ['#ed3c89', '#33a8f2']
                    }
                    var ejemplo_area_una = new ApexCharts(document.querySelector("#ejemplo_area_una"), ejemplo_area_una_options);
                    ejemplo_area_una.render();
                },

                radarPrueba(){
                    var radar_options = {
                        series: [14, 23, 21, 17, 15],
                        chart: {
                            type: 'polarArea',
                            foreColor: '#A5A8AD',
                            height: 320
                        },
                        labels: ['producto 1','producto 2','producto 3','producto 4','producto 5'],
                        fill: {
                            opacity: 1
                        },
                        stroke: {
                            width: 1,
                            colors: undefined
                        },
                        yaxis: {
                            show: false
                        },
                        legend: {
                            position: 'bottom'
                        },
                        plotOptions: {
                            polarArea: {
                                rings: {
                                    strokeWidth: 0
                                },
                                spokes: {
                                    strokeWidth: 0
                                },
                            }
                        },
                    };
                    var ejemplo_radar = new ApexCharts(document.querySelector("#ejemplo_radar"), radar_options);
                    ejemplo_radar.render();
                },

                barrasPrueba(){
                    var barras_pruebas_multiple_options = {
                        series: [{
                            name: 'Ventas',
                            data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
                            }
                        ],
                        chart: {
                        type: 'bar',
                        height: 350
                        },
                        plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                        },
                        dataLabels: {
                        enabled: false
                        },
                        stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                        },
                        xaxis: {
                            categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                        },
                        yaxis: {
                            // // titulo opcionar
                            // title: {
                            //     text: '$ (thousands)'
                            // }
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {

                            // // formato del tooltip
                            y: {
                                formatter: function (val) {
                                    return "$ " + val + " lo que quieras que diga"
                                }
                            }
                        }
                    };

                    var ejemplo_barras = new ApexCharts(document.querySelector("#ejemplo_barras"), barras_pruebas_multiple_options);
                    ejemplo_barras.render();
                },

                barrasPruebaMultiple(){
                    var barras_pruebas_multiple_options = {
                        series: [{
                            name: 'Net Profit',
                            data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
                            }, {
                            name: 'Revenue',
                            data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
                            }, {
                            name: 'Free Cash Flow',
                            data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
                            }
                        ],
                        chart: {
                        type: 'bar',
                        height: 350
                        },
                        plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                        },
                        dataLabels: {
                        enabled: false
                        },
                        stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                        },
                        xaxis: {
                            categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                        },
                        yaxis: {
                            // // titulo opcionar
                            // title: {
                            //     text: '$ (thousands)'
                            // }
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {

                            // // formato del tooltip
                            y: {
                                formatter: function (val) {
                                    return "$ " + val + " lo que quieras que diga"
                                }
                            }
                        }
                    };

                    var ejemplo_barras_mulpiple = new ApexCharts(document.querySelector("#ejemplo_barras_mulpiple"), barras_pruebas_multiple_options);
                    ejemplo_barras_mulpiple.render();
                }

            }))
        </script>
    @endscript
</div>
