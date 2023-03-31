<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\User;
use App\Clientes;
use App\Compras;
use DataTables;
use Illuminate\Support\Facades\DB;
Use Carbon\Carbon;


class ComprasController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        $users = DB::select("select users.id, users.name, count(is_read) as unread 
        from users LEFT JOIN messages ON users.id = messages.from and is_read = 0 and messages.to = " . auth()->id() ." 
        where users.id != " . auth()->id() . " group by users.id, users.name");

        $userss = User::where('id', '!=', auth()->id())->get();
        $userss = DB::table('users')->where('rol', '=' ,'Administrador')->get();
        $userss = DB::select("select users.id, users.name, count(is_read) as unread 
        from users LEFT JOIN messages ON users.id = messages.from and is_read = 0 and messages.to = " . auth()->id() ." 
        where users.id != " . auth()->id() . " and users.rol = 'Administrador' group by users.id, users.name");

        $nombreCliente = Clientes::all();
        $useres = User::all();

        $usuariosGetAll = User::where('id', '=', auth()->id())->get();

        $lastCompra = DB::table('compras')->orderBy('created_at', 'desc')->first();


        $compras = Compras::all();

        return view('pages/compras', array('compras' => $compras, 'userss'=>$userss, 'users'=>$users, 'nombreCliente'=>$nombreCliente, 'useres'=>$useres, 'usuariosGetAll'=>$usuariosGetAll, 'lastCompra'=>$lastCompra));
    }

    function getdata()
    {

        $join = DB::table('compras')->join('users', 'users.id', '=', 'compras.id_usuario')->where('compras.id_usuario', '=', auth()->id())
                                      ->select('compras.id as idCompras', 'compras.id_usuario', 'compras.proveedor', 'compras.fecha', 'compras.compra_aportacion', 'compras.numero_compra', 'compras.referencia', 'compras.pdf_orden', 'compras.pdf_pago', 'compras.retiro', 'compras.deposito', 'compras.saldo', 'compras.status', 'users.*')->where('compras.status', '=', 'Pendiente')->get();
              
        return DataTables()->of($join)
        ->addColumn('name', function($data){
            $name = $data->name;
            return $name;
        })
        ->addColumn('proveedor', function($data){
            $proveedor = $data->proveedor;
            return $proveedor;
        })
        ->addColumn('fecha', function($data){
            $fecha = $data->fecha;
            $date = Carbon::parse($fecha);
            return $date->format('d/m/Y');
        })
        ->addColumn('compra_aportacion', function($data){
            $compra_aportacion = $data->compra_aportacion;
            return $compra_aportacion;
        })
        ->addColumn('numero_compra', function($data){
            $numero_compra = $data->numero_compra;
            return $numero_compra;
        })
        ->addColumn('referencia', function($data){
            $referencia = $data->referencia;
            return $referencia;
        })
        ->addColumn('pdf_orden', function($data){
            if($data->pdf_orden == null){
            return 'Pendiente';
            }
            else
            {
            return '<a href="'.$data->pdf_orden.'" class="linkTable" target="_blank">Orden (PDF)</a>';
            }
        })
        ->addColumn('pdf_pago', function($data){
            if($data->pdf_pago == null){
            return 'Pendiente';
            }
            else
            {
            return '<a href="'.$data->pdf_pago.'" class="linkTable" target="_blank">Orden (PDF)</a>';
            }
        })
        ->addColumn('retiro', function($data){
            $retiro = $data->retiro;
            return $retiro;
        })
        ->addColumn('deposito', function($data){
            $deposito = $data->deposito;
            return $deposito;
        })
        ->addColumn('saldo', function($data){
            $saldo = $data->saldo;
            return $saldo;
        })
        ->addColumn('status', function($data){
            $status = $data->status;
            return $status;
        })
        ->addColumn('action', function($data){
            return '<a href="#" class="btn btn-xs btn-editarServicio edit" id="'.$data->idCompras.'"><img src="/img/editar.svg" width="24px" height="24px"></a><a href="#" class="btn btn-xs btn-editarServicioDanger delete" id="'.$data->idCompras.'"><img src="/img/eliminar.svg" width="24px" height="24px"></a>';
        })
        ->rawColumns(['name', 'proveedor', 'fecha', 'compra_aportacion', 'numero_compra', 'referencia', 'pdf_orden', 'pdf_pago', 'retiro', 'deposito', 'saldo', 'status', 'action'])
        ->make(true);
    }

    
        public function calculateBalance()
        {
            $saldo = 0;
            $compras = Compras::all();
            foreach ($compras as $compra) {
                $saldo += $compra->deposito - $compra->retiro;
            }
            return $saldo;
        }
    

    function postdata(Request $request)
    {
        $validation = Validator::make($request->all(), [

            'proveedor'  => 'required',
            'fecha'  => 'required',
            'compra_aportacion'  => 'required',
            'numero_compra'  => 'required',
            'referencia'  => 'required',
            'retiro'  => 'required',
            'deposito'  => 'required',
            'saldo'  => 'required',

        ]);

        $error_array = array();
        $success_output = '';
        if ($validation->fails())
        {
            foreach($validation->messages()->getMessages() as $field_name => $messages)
            {
                $error_array[] = $messages;
            }
        }
        else
        {

            if($request->get('button_action') == "insert")
            {
                $pdf_orden = $request->file('pdf_orden');
                $pdf_pago = $request->file('pdf_pago');
                $estatusNull = 'Pendiente';

                if($pdf_orden || $pdf_pago == null){
                $compras = new Compras([
                    'id_usuario' => $request->get('id_usuario'),
                    'proveedor' => $request->get('proveedor'),
                    'fecha' => $request->get('fecha'),
                    'compra_aportacion' => $request->get('compra_aportacion'),
                    'numero_compra' => $request->get('numero_compra'),
                    'referencia' => $request->get('referencia'),
                    'pdf_orden' => $request->get('pdf_orden'),
                    'pdf_pago' => $request->get('pdf_pago'),
                    'retiro' => $request->get('retiro'),
                    'deposito' => $request->get('deposito'),
                    'saldo' => $request->get('saldo'),
                    'status'  => $estatusNull,
                ]);
                $compras->save();
                $success_output = '<div class="alertaTables alert alert-success">Compra/Aportación Añadido</div>';    

                }else if($pdf_orden && $pdf_pago != null){
                    $pdf_orden = $request->file('pdf_orden');
                    $pdf_name = $pdf_orden->getClientOriginalName();
                    $rutaPDF2 = 'pdf/';
                    $rutaPDF = 'pdf/'.$pdf_name;
                    $pdf_orden->move($rutaPDF2, $pdf_name);
    
                    $pdf_pago = $request->file('pdf_pago');
                    $pdf_name2 = $pdf_pago->getClientOriginalName();
                    $rutaPDF22 = 'pdf/';
                    $rutaPDF2 = 'pdf/'.$pdf_name2;
                    $pdf_pago->move($rutaPDF22, $pdf_name2);

                $compras = new Compras([
                    'id_usuario' => $request->get('id_usuario'),
                    'proveedor' => $request->get('proveedor'),
                    'fecha' => $request->get('fecha'),
                    'compra_aportacion' => $request->get('compra_aportacion'),
                    'numero_compra' => $request->get('numero_compra'),
                    'referencia' => $request->get('referencia'),
                    'pdf_orden' => $rutaPDF,
                    'pdf_pago' => $rutaPDF2,
                    'retiro' => $request->get('retiro'),
                    'deposito' => $request->get('deposito'),
                    'saldo' => $request->get('saldo'),
                    'status'  => $request->get('status'),
                ]);
                $compras->save();
                $success_output = '<div class="alertaTables alert alert-success">Compra/Aportación Añadido</div>';
                }
            }


            if($request->get('button_action') == 'update')
            {
                $pdf_orden = $request->file('pdf_orden');
                $pdf_name = $pdf_orden->getClientOriginalName();
                $rutaPDF2 = 'pdf/';
                $rutaPDF = 'pdf/'.$pdf_name;
                $pdf_orden->move($rutaPDF2, $pdf_name);

                $pdf_pago = $request->file('pdf_pago');
                $pdf_name2 = $pdf_pago->getClientOriginalName();
                $rutaPDF22 = 'pdf/';
                $rutaPDF2 = 'pdf/'.$pdf_name2;
                $pdf_pago->move($rutaPDF22, $pdf_name2);

                $compras = Compras::find($request->get('compras_id'));
                $compras->proveedor = $request->get('proveedor');
                $compras->fecha = $request->get('fecha');
                $compras->compra_aportacion = $request->get('compra_aportacion');
                $compras->numero_compra = $request->get('numero_compra');
                $compras->referencia = $request->get('referencia');
                $compras->pdf_orden = $rutaPDF;
                $compras->pdf_pago = $rutaPDF2;
                $compras->retiro = $request->get('retiro');
                $compras->deposito = $request->get('deposito');
                $compras->saldo = $request->get('saldo');
                $compras->save();
                $success_output = '<div class="alertaTables alert alert-success">Compra/Aportación Actualizado</div>';
            }
        }
        $output = array(
            'error'     =>  $error_array,
            'success'   =>  $success_output
        );
        echo json_encode($output);
    }

    function fetchdata(Request $request)
    {
        $id = $request->input('id');
        $compras = Compras::find($id);

        $fecha4 = $compras->fecha;
        $date = Carbon::parse($fecha4)->format('Y-m-d');

        

        $output = array(
            'id_usuario' => $compras->id_usuario,
            'proveedor' => $compras->proveedor,
            'fecha' => $date,
            'compra_aportacion' => $compras->compra_aportacion,
            'numero_compra' => $compras->numero_compra,
            'referencia' => $compras->referencia,
            'pdf_orden' => $compras->pdf_orden,
            'pdf_pago' => $compras->pdf_pago,
            'retiro' => $compras->retiro,
            'deposito' => $compras->deposito,
            'saldo' => $compras->saldo
        );
        echo json_encode($output);
    }

    function removedata(Request $request)
    {
        $compras = Compras::find($request->input('id'));
        if($compras->delete()){
            echo 'Compra/Aportación eliminado';
        }
    }
}

