<div>
    <style>
        .card-custom {
            min-height: 140px;
        }

        .icon i {
            font-size: 2rem;
        }

        h5 {
            font-size: 1rem;
        }

        h3 {
            font-size: 1.2rem;
        }
    </style>

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
                        <div class="col-md-3">
                            <label for="startDate">Fecha de Inicio:</label>
                            <input type="date" wire:model="startDate" id="startDate" class="form-control shadow-sm">
                        </div>

                        <div class="col-md-3">
                            <label for="endDate">Fecha de Fin:</label>
                            <input type="date" wire:model="endDate" id="endDate" class="form-control shadow-sm">
                        </div>

                        <div class="ml-2">
                            <button type="button" wire:click="actualizarDatos"
                                class="btn btn-outline-dark ml-2">Filtrar</button>
                        </div>
                    </div>
                    <hr>

                    <div class="d-flex justify-content-around flex-wrap">
                        <!-- Tarjetas con información -->
                        <div class="card card-custom text-center p-2 box-shadow-2 bg-light border-danger rounded"
                            style="flex: 1 0 15%; max-width: 180px;">
                            <div class="icon mb-2">
                                <i class="fa fa-money-bill-wave fa-2x text-danger"></i>
                            </div>
                            <h5>Gastos Totales</h5>
                            <h3 class="text-danger">$ {{ number_format($gastos_totales, 2) }}</h3>
                        </div>

                        <div class="card card-custom text-center p-2 box-shadow-2 bg-light border-info rounded"
                            style="flex: 1 0 15%; max-width: 180px;">
                            <div class="icon mb-2">
                                <i class="fa fa-chart-line fa-2x text-success"></i>
                            </div>
                            <h5>Ganancia Real</h5>
                            <h3 class="text-success">$ {{ number_format($ganancia_real, 2) }}</h3>
                        </div>


                        <div class="card card-custom text-center p-2 box-shadow-2 bg-light border-info rounded"
                            style="flex: 1 0 15%; max-width: 180px;">
                            <div class="icon mb-2">
                                <i class="fa fa-shopping-cart fa-2x text-info"></i>
                            </div>
                            <h5>Compras Totales</h5>
                            <h3 class="text-info">$ {{ number_format($compras_totales, 2) }}</h3>
                        </div>

                        <div class="card card-custom text-center p-2 box-shadow-2 bg-light border-warning rounded"
                            style="flex: 1 0 15%; max-width: 180px;">
                            <div class="icon mb-2">
                                <i class="fa fa-store fa-2x text-warning"></i>
                            </div>
                            <h5>Ventas Totales</h5>
                            <h3 class="text-warning">$ {{ number_format($ventas_totales, 2) }}</h3>
                        </div>

                        <div class="card card-custom text-center p-2 box-shadow-2 bg-light border-primary rounded"
                            style="flex: 1 0 15%; max-width: 180px;">
                            <div class="icon mb-2">
                                <i class="fa fa-boxes fa-2x text-primary"></i>
                            </div>
                            <h5>Valor del Inventario</h5>
                            <h3 class="text-primary">$ {{ number_format($valor_inventario, 2) }}</h3>
                        </div>
                    </div>

                    <!-- Gráficas -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card box-shadow-2">
                                <div class="card-body">
                                    <h5>Producto Más Vendido</h5>
                                    <div id="grafica_producto_mas_vendido" style="height: 300px"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card box-shadow-2">
                                <div class="card-body">
                                    <h5>Ranking de Productos por Ganancias</h5>
                                    <div id="grafica_ranking_ganancias" style="height: 300px"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card box-shadow-2">
                                <div class="card-body">
                                    <h5>Ventas Diarias</h5>
                                    <div id="grafica_ventas_diarias" style="height: 300px"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js_extra')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            document.addEventListener('livewire:init', function() {
                let charts = {
                    grafica_producto_mas_vendido: null,
                    grafica_ranking_ganancias: null,
                    grafica_ventas_diarias: null // Nueva variable para la gráfica de ventas diarias
                };

                // Función para renderizar gráfico de Producto Más Vendido
                function renderProductoMasVendidoChart(data) {
                    if (!data || Object.keys(data).length === 0) {
                        $('#grafica_producto_mas_vendido').html('<p>No hay datos disponibles.</p>');
                        return;
                    }

                    let options = {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: [{
                            name: 'Productos',
                            data: Object.values(data)
                        }],
                        xaxis: {
                            categories: Object.keys(data)
                        }
                    };

                    // Verifica si existe una gráfica previa y la destruye antes de renderizar la nueva
                    if (charts.grafica_producto_mas_vendido) {
                        try {
                            charts.grafica_producto_mas_vendido.destroy();
                        } catch (error) {
                            console.error("Error al destruir gráfica anterior: ", error);
                        }
                    }

                    // Crea una nueva gráfica
                    charts.grafica_producto_mas_vendido = new ApexCharts(document.querySelector(
                        "#grafica_producto_mas_vendido"), options);
                    charts.grafica_producto_mas_vendido.render();
                }

                // Función para renderizar gráfico de Ranking de Ganancias
                function renderRankingGananciasChart(data) {
                    if (!data || Object.keys(data).length === 0) {
                        $('#grafica_ranking_ganancias').html('<p>No hay datos disponibles.</p>');
                        return;
                    }

                    let options = {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: [{
                            name: 'Ganancias',
                            data: Object.values(data)
                        }],
                        xaxis: {
                            categories: Object.keys(data)
                        }
                    };

                    // Verifica si existe una gráfica previa y la destruye antes de renderizar la nueva
                    if (charts.grafica_ranking_ganancias) {
                        try {
                            charts.grafica_ranking_ganancias.destroy();
                        } catch (error) {
                            console.error("Error al destruir gráfica anterior: ", error);
                        }
                    }

                    // Crea una nueva gráfica
                    charts.grafica_ranking_ganancias = new ApexCharts(document.querySelector(
                        "#grafica_ranking_ganancias"), options);
                    charts.grafica_ranking_ganancias.render();
                }

                // Función para renderizar gráfico de Ventas Diarias
                function renderVentasDiariasChart(data) {
                    if (!data || Object.keys(data).length === 0) {
                        $('#grafica_ventas_diarias').html('<p>No hay datos disponibles.</p>');
                        return;
                    }

                    let options = {
                        chart: {
                            type: 'area',
                            height: 300,
                            zoom: {
                                enabled: false
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        series: [{
                            name: 'Ventas',
                            data: Object.values(data)
                        }],
                        xaxis: {
                            categories: Object.keys(data),
                            title: {
                                text: 'Días'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Total Ventas'
                            }
                        },
                        tooltip: {
                            x: {
                                format: 'dd/MM/yyyy' // Formato de fecha en el tooltip
                            }
                        }
                    };

                    // Verifica si existe una gráfica previa y la destruye antes de renderizar la nueva
                    if (charts.grafica_ventas_diarias) {
                        try {
                            charts.grafica_ventas_diarias.destroy();
                        } catch (error) {
                            console.error("Error al destruir gráfica anterior: ", error);
                        }
                    }

                    // Crea una nueva gráfica
                    charts.grafica_ventas_diarias = new ApexCharts(document.querySelector(
                        "#grafica_ventas_diarias"), options);
                    charts.grafica_ventas_diarias.render();
                }

                // Escuchar evento emitido por Livewire y actualizar las gráficas
                window.addEventListener('chartDataUpdated', event => {
                    const eventData = event.detail[0]; // Aseguramos que sea el primer elemento del array

                    const {
                        productosMasVendidos,
                        rankingGanancias,
                        ventasPorDia // Agregamos ventasDiarias
                    } = eventData;


                    // Asegúrate de que los datos recibidos son válidos
                    if (productosMasVendidos && rankingGanancias && ventasPorDia) {
                        setTimeout(() => {
                            renderProductoMasVendidoChart(productosMasVendidos);
                            renderRankingGananciasChart(rankingGanancias);
                            renderVentasDiariasChart(
                            ventasPorDia); // Renderizar gráfica de ventas diarias
                        }, 100); // Añadir un retraso de 100 ms
                    }


                });

                // Renderizar gráficos iniciales cuando la página se carga
                renderProductoMasVendidoChart(@json($productos_mas_vendidos));
                renderRankingGananciasChart(@json($ranking_productos));
                renderVentasDiariasChart(@json($ventas_diarias)); // Renderizar gráfica de ventas diarias inicial
            });
        </script>
    @endpush
</div>
