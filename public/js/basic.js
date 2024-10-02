

    addEventListener("DOMContentLoaded", () => {
        // ------ quitamos el loading ------
        $(".cc-loadingpage").fadeOut("slow");
        $('.select2').select2()

        const dark_mode = localStorage.getItem("dark_mode");
        changeDarkMode( dark_mode )
        // console.log( {dark_mode} )
    });

    $('#change-dark-mode').on('click', ()=>{
        if( $('body').hasClass('dark-theme') ){
            changeDarkMode( false )
            localStorage.setItem("dark_mode", 0);
        }else{
            changeDarkMode( true )
            localStorage.setItem("dark_mode", 1);
        }
    })

    function changeDarkMode( dark_mode = 1 ){

        const body = $('body')
        const icon = $('.icon_dark_mode')
        const text = $('.text_dark_mode')

        if( dark_mode == 1 ){
            body.addClass('dark-theme')
            body.removeClass('light-theme')

            icon.removeClass('fa-moon')
            icon.addClass('fa-sun')

            text.text( 'Light' )
        }else{
            body.removeClass('dark-theme')
            body.addClass('light-theme')
   
            icon.addClass('fa-moon')
            icon.removeClass('fa-sun')

            text.text( 'Dark' )
        }
    }

    $('.btn-block').on('click', ()=>{
        blockPage()
        console.log( 'entrando' )
    })

    // loading con transparencia
    function blockPage() {
        console.log('entrando dd')
        $(".cc-loadingpage_transparent").fadeIn("slow");
    }

    function unblockPage( timer = 1000) {
        setTimeout(() => {
            $(".cc-loadingpage_transparent").fadeOut("slow");
        }, timer);
    }

    // valida para que no se pinte textualmente NULL en la tabla
    function print( val ){
        return val ? val : '';
    }

    function redirec( url ){
        window.location.href = url
    }

    function __numberFormat( number ){
        // return number;
        // return new Intl.NumberFormat('de-DE').format(number);
        if( typeof number !== 'undefined' ){
            return '$' + number.toLocaleString();
        }else{
            return '$ 0'
        }
    }


    function getDay(date){
        const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        const day = dias[ date.getDay() ];
        return day;
    }

    function formatDateTime(date){
        const months = ["Jun", "Feb", "Mar","Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        let formatted_date = date.getDate() + " " + months[date.getMonth()] + " del " + date.getFullYear() + " " + date.toLocaleTimeString();
        return formatted_date;
    }

    function formatDate(date){
        console.log(date);
        const months = ["Jun", "Feb", "Mar","Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        let formatted_date = date.getDate() + " " + months[date.getMonth()] + " del " + date.getFullYear();
        return formatted_date;
    }

    // ventana emergente
    function openIframe(src, clase = '', fn_destroy = null) {
        Fancybox.show(
            [{
                src: src,
                type: 'iframe',
                preload: true,
                //   scrolling: false,//no probado
                autoSize: true, //para que si damos clase en CC se ponga el boton cerrar automático donde debe ser
                autoFocus: true,


            }, ], {
                closeButton: true, // lo ocultamos porque no funciona en responsive, sabrá el putas por qué
                smallBtn: false,
                mainClass: clase,
                template: {
                    spinner: '',
                },
                on: {
                    init: () => { // se ejecuta al iniciar
                        // blockPage();
                    },
                    shouldClose: () => { // se ejecuta cuando se cierra el fancy
                        if (fn_destroy && fn_destroy != '') {
                            fn_destroy();
                        }
                        // unblockPage();
                    },
                    done: () => { // cuando ya se ha cargado y mostrado el fancy
                    }
                },
            }
        );

    }

    // se agrega todo aquí por variables, order, si se va a exportar todo eso
    function resetTable( id ){
        $( id ).DataTable().destroy(); // destruimos la tabla
        $( id ).DataTable({ // le asignamos nuevamente las propiedades de datatables
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            order:[[0, 'desc']] // si se necesita cambiar como parámetro y se asigna esta por defecto
        });

        $(id).removeClass('d-none'); // mostramos la table

    }

    function __eliminarAcentos( val ){
        return val.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
    }

    // recibe un numero con formato y lo devuelve limpio
    function __limpiarNum( val ){
        for(var i = 0; i<=val.length; i++){
            val = val.replace("$","");
            val = val.replace(" ","");
            val = val.replace(".","");
            val = val.replace(",","");
        }
        val = isNaN( val ) ? 0 : val
        return Number( val );
    }
