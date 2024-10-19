<div x-data="dataalpine">
    {{-- Para no cargar el CDN en todas las vistas --}}
    @push('js_extra')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @endpush

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Dashboard</h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card p-3">
                    <b>Filtrar Dashboard</b>
                    <div class="d-flex f_right">
                        <div class="">
                            <x-input type="date" model="$wire.desde" id="desde" class="form-control"></x-input>
                        </div>
                        <div class="ml-2">
                            <x-input type="date" model="$wire.hasta" id="hasta" class="form-control"></x-input>
                        </div>

                        <button type="button" x-on:click="getEstadisticas()" class="btn btn-outline-dark ml-2">Filtrar</button>
                    </div>
                    <hr>
                    <div class="row">
                        <!-- Gráfica de Gastos -->
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2">
                                        <h5>Gastos Totales</h5>
                                        <div id="grafica_gastos" style="height: 300px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica de Ventas -->
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2">
                                        <h5>Ventas Totales</h5>
                                        <div id="grafica_ventas" style="height: 300px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica del Producto más vendido -->
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2">
                                        <h5>Producto Más Vendido</h5>
                                        <div id="grafica_producto_mas_vendido" style="height: 300px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica del Valor del Inventario -->
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2">
                                        <h5>Valor del Inventario</h5>
                                        <div id="grafica_valor_inventario" style="height: 300px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica del Ranking de productos por Ganancias -->
                        <div class="col-md-6 col-lg-6">
                            <div class="card" style="margin-top: 5px;">
                                <div class="body">
                                    <div class="content_graficas box-shadow-2 m-2 p-2">
                                        <h5>Ranking de Productos por Ganancias</h5>
                                        <div id="grafica_ranking_ganancias" style="height: 300px"></div>
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
                init() {
                    this.renderGastos();
                    this.renderVentas();
                    this.renderProductoMasVendido();
                    this.renderValorInventario();
                    this.renderRankingGanancias();
                },

                getEstadisticas() {
                    // Limpiar las gráficas para volver a renderizarlas
                    $('#grafica_gastos').html('');
                    $('#grafica_ventas').html('');
                    $('#grafica_producto_mas_vendido').html('');
                    $('#grafica_valor_inventario').html('');
                    $('#grafica_ranking_ganancias').html('');

                    // Volver a renderizar
                    this.renderGastos();
                    this.renderVentas();
                    this.renderProductoMasVendido();
                    this.renderValorInventario();
                    this.renderRankingGanancias();
                },

                renderGastos() {
                    const data = {
                        series: [{
                            name: 'Gastos',
                            data: [{{ $gastos_totales }}]
                        }],
                        labels: ['Gastos']
                    };

                    var options = {
                        chart: {
                            type: 'bar',  // Cambiado a barra
                            height: 300
                        },
                        series: data.series,
                        xaxis: {
                            categories: ['Gastos'],
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#grafica_gastos"), options);
                    chart.render();
                },

                renderVentas() {
                    const data = {
                        series: [{
                            name: 'Ventas',
                            data: [{{ $ventas_totales }}]
                        }],
                        labels: ['Ventas']
                    };

                    var options = {
                        chart: {
                            type: 'line',  // Cambiado a línea
                            height: 300
                        },
                        series: data.series,
                        xaxis: {
                            categories: ['Ventas'],
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#grafica_ventas"), options);
                    chart.render();
                },

                renderProductoMasVendido() {
                    const data = {
                        series: [{
                            name: 'Productos',
                            data: @json(array_values($productos_mas_vendidos->toArray()))
                        }],
                        labels: @json(array_keys($productos_mas_vendidos->toArray()))
                    };

                    var options = {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: data.series,
                        xaxis: {
                            categories: data.labels
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#grafica_producto_mas_vendido"), options);
                    chart.render();
                },

                renderValorInventario() {
                    const data = {
                        series: [{
                            name: 'Valor Inventario',
                            data: [{{ $valor_inventario }}]
                        }],
                        labels: ['Valor Inventario']
                    };

                    var options = {
                        chart: {
                            type: 'area',  // Cambiado a área
                            height: 300
                        },
                        series: data.series,
                        xaxis: {
                            categories: ['Valor Inventario'],
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#grafica_valor_inventario"), options);
                    chart.render();
                },

                renderRankingGanancias() {
                    const data = {
                        series: [{
                            name: 'Ganancias',
                            data: @json(array_values($ranking_productos->toArray()))
                        }],
                        labels: @json(array_keys($ranking_productos->toArray()))
                    };

                    var options = {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: data.series,
                        xaxis: {
                            categories: data.labels
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#grafica_ranking_ganancias"), options);
                    chart.render();
                }

            }));
        </script>
    @endscript
</div>
