@if(Auth::user()->rol != "Usuario")


@extends('home')
@section('content')

<a class="saldoCompras">{{ $lastCompra->saldo }}</a>

<main class="content">
				<div class="container-fluid p-0">
					<h1 class="h3 mb-3">Compras/Aportaciones</h1>
                    <br />
                    <div align="right">
                        <button type="button" name="add" id="add_data" class="btn btn-success btn-sm">Registrar Compra/Aportación</button>
                    </div>
                    <br />
                    <table id="compras_table" class="table table-striped table-bordered dataTable" style="width:100%">
                        <thead>
                            <tr style="background-color: white;">
                                <th>Usuario</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Compra/Aportación</th>
                                <th>No. de OC/A</th>
                                <th>Referencia</th>
                                <th>Retiro</th>
                                <th>Deposito</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th>PDF Orden</th>
                                <th>PDF Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="comprasModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" id="compras_form" enctype="multipart/form-data">
                                <div class="modal-header">
                                <h4 class="modal-title">Añadir Compra/Aportacion</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    {{csrf_field()}}
                                    <span id="form_output"></span>
                                    <div class="form-group">
                                        <label>Usuario</label>
                                        <select name="id_usuario" id="id_usuario" class="form-control">
                                                <option value="">Elige el Usuario</option>
                                            @foreach($usuariosGetAll as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Proveedor</label>
                                        <input type="text" name="proveedor" id="proveedor" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Fecha</label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" data-date-format="YYYY-MM-DD"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Compra/Aportación</label>
                                        <input type="text" name="compra_aportacion" id="compra_aportacion" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label>No. de OC/A</label>
                                        <input type="text" name="numero_compra" id="numero_compra" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label>Referencia</label>
                                        <input type="text" name="referencia" id="referencia" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label>PDF Orden</label>
                                        <input type="file" name="pdf_orden" id="pdf_orden" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label>PDF Pago</label>
                                        <input type="file" name="pdf_pago" id="pdf_pago" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label>Retiro</label>
                                        <input type="number" step="0.001" name="retiro" id="retiro" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Deposito</label>
                                        <input readonly step="0.001" type="number" name="deposito" id="deposito" value="0" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Saldo</label>
                                        <input readonly type="number" step="0.001" name="saldo" id="saldo" value="{{ $lastCompra->saldo }}" class="form-control"/>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="hidden" name="compras_id" id="compras_id" value="" />

                                    <input type="hidden" name="button_action" id="button_action" value="insert" />
                                    <input type="submit" name="submit" id="action" value="Añadir" class="btn btn-info" />
                                    <button id="cerrarModal" type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
</main>




<script type="text/javascript">
$(document).ready(function() {
    function updateBalance(table) {
        let saldo = 0;

        table.rows().every(function () {
            const rowData = this.data();
            const retiro = parseFloat(rowData.retiro) || 0;
            const deposito = parseFloat(rowData.deposito) || 0;
            saldo += deposito - retiro;
            rowData.saldo = saldo.toFixed(2);
            this.invalidate(); // Invalida la fila para que se redibuje
        });

        table.draw(); // Redibuja la tabla
    }

    const comprasTable = $('#compras_table').DataTable({
    initComplete: function() {
      var api = this.api();
      var info = api.page.info();
      
      api.page(info.pages - 1).draw(false);
    },
        "processing": true,
        "ordering": false,
        "ajax": "{{ route('compras.getdata') }}",
        "columns":[
            { "data": "name"},
            { "data": "proveedor" },
            { "data": "fecha" },
            { "data": "compra_aportacion" },
            { "data": "numero_compra" },
            { "data": "referencia" },
            { "data": "retiro", render: $.fn.dataTable.render.number( ',', '.', 2, '$' ) },
            { "data": "deposito", render: $.fn.dataTable.render.number( ',', '.', 2, '$' )},
            { "data": "saldo", render: $.fn.dataTable.render.number( ',', '.', 2, '$' )},
            { "data": "status" },
            { "data": "pdf_orden" },
            { "data": "pdf_pago" },
            { "data": "action", orderable:false, searchable: false, className: "accionesTD"}

        ],
        "language": idioma_espanol,
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
     });

// Llama a updateBalance cuando se cambie un valor de retiro o depósito
comprasTable.on('change', '.retiro input, .deposito input', function () {
        const cell = comprasTable.cell($(this).closest('td'));
        cell.data($(this).val());
        updateBalance(comprasTable);
    });

    updateBalance(comprasTable); // Actualiza el saldo cuando se carga la página



     $('#add_data').click(function(){
        $('#comprasModal').modal('show');
        $('#compras_form')[0].reset();
        $('#form_output').html('');
        $('#button_action').val('insert');
        $('#action').val('Añadir');
    });

    $('#compras_form').on('submit', function(event){
        event.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            url:"{{ route('compras.postdata') }}",
            method:"POST",
            data:form_data,
            dataType:"json",
            success:function(data)
            {
                if(data.error.length > 0)
                {
                    var error_html = '';
                    for(var count = 0; count < data.error.length; count++)
                    {
                        error_html += '<div class="alertaTables alert alert-danger">'+data.error[count]+'</div>';
                    }
                    $('#form_output').html(error_html);
                    window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                         });
                    }, 5000);
                }
                else
                {

                    $('#form_output').html(data.success);
                    $('#compras_form')[0].reset();
                    $('#action').val('Añadir');
                    $('.modal-title').text('Añadir Compra/Aportación');
                    $('#button_action').val('insert');
                    $('#compras_table').DataTable().ajax.reload();


                    
                    window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                         });
                    }, 5000);

                    location.reload();

                }
            }
        })
    });

    $(document).on('click', '.edit', function(){
        var id = $(this).attr("id");
        $.ajax({
            url:"{{ route('compras.fetchdata') }}",
            method:'get',
            data:{id:id},
            dataType:'json',
            success:function(data)
            {
                $('#id_usuario').val(data.id_usuario);
                $('#proveedor').val(data.proveedor);
                $('#fecha').val(data.fecha);
                $('#compra_aportacion').val(data.compra_aportacion);
                $('#numero_compra').val(data.numero_compra);
                $('#referencia').val(data.referencia);
                $('#pdf_orden').val(data.pdf_orden);
                $('#pdf_pago').val(data.pdf_pago);
                $('#retiro').val(data.retiro);
                $('#deposito').val(data.deposito);
                $('#saldo').val(data.saldo);
                $('#status').val(data.status);
                $('#compras_id').val(id);
                $('#comprasModal').modal('show');
                $('#action').val('Editar');
                $('.modal-title').text('Editar Compra/Aportación');
                $('#button_action').val('update');
            }
        })            
    });


    $(document).on('click', '.delete', function(){
        var id = $(this).attr('id');
        if(confirm("¿Estas seguro que quieres eliminar esta compra?"))
        {
            $.ajax({
                url:"{{ route('compras.removedata') }}",
                method:'get',
                data:{id:id},
                success:function(data)
                {
                    alert(data);
                    $('#compras_table').DataTable().ajax.reload();
                }

            });
        }
        else{
            return false;
        }
    });

        $('#nav4').addClass('active'); 

});

var idioma_espanol = {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_   Compras",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando   Compras del _START_ al _END_ de un total de _TOTAL_   Compras",
            "sInfoEmpty":      "Mostrando   Compras del 0 al 0 de un total de 0   Compras",
            "sInfoFiltered":   "(filtrado de un total de _MAX_   Compras)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        }


        var saldoCompras = parseFloat('{{ $lastCompra->saldo }}');


        $("#retiro").on("keydown keyup", function(){


        var retiro = parseFloat($(this).val()).toFixed(2);
        restaSaldo = parseFloat(saldoCompras-retiro).toFixed(2)

        if ($("#retiro").val().length == 0){
            $('#saldo').val(saldoCompras);
        } else {
            $('#saldo').val(restaSaldo);
        }

        });




</script>



@endsection

@else

<script>

        window.location="/dashboard"

</script>

@endif
