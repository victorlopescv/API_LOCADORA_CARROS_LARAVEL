<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Repositories\ClienteRepository;

class ClienteController extends Controller

{
    public function __construct(Cliente $cliente){
        $cliente = $this->cliente = $cliente;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $clienteRepository = new ClienteRepository($this->cliente);

        if ($request->has('filtro')) {
            $clienteRepository->filtro($request->filtro);
        }
        if($request->has('atributos')){            
            $atributos = $request->atributos;
            $clienteRepository->selectAtributos($atributos);   
        }
        return response()->json($clienteRepository->getResultado(),200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->cliente->rules());

        $cliente = $this->cliente->create([
            'nome' => $request->input('nome')
        ]);

        return response()->json($cliente,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = $this->cliente->find($id);

        if ($cliente === null) {
            return response()->json(['erro' => 'Recurso não encontrado'],404);
        }

        return response()->json($cliente,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cliente = $this->cliente->find($id);
        if ($cliente === null) {
            return response()->json(['erro' => 'Não foi possível realizar a atualização. Recurso não encontrado'] , 404);
        }

        if ($request->method()==='PUT') {
            $request->validate($this->cliente->rules());
        }else {
            $rulesDinamico = [] ;

            foreach($cliente->rules() as $input => $regras){
          
                if( array_key_exists( $input, $request->all() ) ){
                
                    $rulesDinamico[$input] =  $regras;
                }
             }  
             $request->validate($rulesDinamico); 
        }

        $cliente->fill($request->all());
        $cliente->save();

        return response()->json($cliente,201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cliente = $this->cliente->find($id);
        if ($cliente===null) {
            return response()->json(['erro' => 'Não foi possível realizar a exclusão. Recurso não encontrado'] , 404 );
        }
        $cliente->delete();
        return response()->json(['msg' => 'Cliente deletada com sucesso!'] ,200);
    }
}
