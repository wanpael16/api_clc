<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categorias;

class CategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datos = Categorias::orderBy('id', 'desc')->get();
        return response()->json($datos, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          //recibir el json
       //echo file_get_contents('php://input');exit;
       $json = json_decode(file_get_contents('php://input') , true);
       //print_r($json);
       //validar que viene un json
        if(!is_array($json ))
        {
       		$array=
		        	array
		        	(
		        		'response'=>array
			        	(
			        		'estado'=>'Bad Request',
			        		'mensaje'=>'La peticion HTTP no trae datos para procesar ' 
			        	)
		        	)
		        ;  	
		    return response()->json($array, 400);
        }
       //crear el registro
        Categorias::create(
            [
                'nombre'=>$json['nombre'],
                'slug'=>Str::slug($json['nombre'], '-')
            ]
        );
       //retornar un json
       $array=array
                    (
                        'estado'=>'ok',
                        'mensaje'=>'Se creó el registro exitosamente', 
                    ); 
        return response()->json( $array, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $datos=Categorias::where(['id'=>$id])->first();
        if(!is_object($datos))
        {
            $array=array
                (
                    'estado'=>'error',
                    'mensaje'=>'No existe el registro', 
                ); 
            return response()->json( $array, 404);
        }else
        {
            return response()->json( $datos, 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $datos=Categorias::where(['id'=>$id])->first();
       if(!is_object($datos))
       {
            $array=array
                    (
                        'estado'=>'error',
                        'mensaje'=>'No existe el registro', 
                    ); 
        return response()->json( $array, 404);
       }else
       {
        $json = json_decode(file_get_contents('php://input'), true);
        if(!is_array($json ))
            {
                $array=
                        array
                        (
                            'response'=>array
                            (
                                'estado'=>'Bad Request',
                                'mensaje'=>'La peticion HTTP no trae datos para procesar ' 
                            )
                        )
                    ;  	
                return response()->json($array, 400);
            }
        //ejecuto el update
        $datos->nombre=$json['nombre'];
        $datos->slug=Str::slug($json['nombre'], '-');
        $datos->save();
        //retorno un json
        $array=array
                    (
                        'estado'=>'ok',
                        'mensaje'=>'Se modificó el registro', 
                    ); 
        return response()->json( $array, 201);
       }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $datos=Categorias::where(['id'=>$id])->firstOrFail();
        $existe = Productos::where(['categorias_id'=>$id])->count();
        if($existe==0)
        {
            Categorias::where(['id'=>$id])->delete();
            $array=array
                        (
                            'estado'=>'ok',
                            'mensaje'=>'Se eliminó el registro', 
                        ); 
            return response()->json( $array, 201);
        }else
        { 
            $array=array
                        (
                            'estado'=>'error',
                            'mensaje'=>'No se puede eliminar el registro', 
                        ); 
     }
    }
}
