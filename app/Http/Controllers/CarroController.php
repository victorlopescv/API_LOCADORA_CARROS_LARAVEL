<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use Illuminate\Http\Request;
use App\Repositories\CarroRepository;

class CarroController extends Controller
{
    public function __construct(Carro $carro){
        $this->carro = $carro;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $carroRepository = new CarroRepository($this->carro);

        //verificando se existe parametro 'atributos_modelo' e formatando a query e utilizando get() para consultar
        if ($request->has('atributos_modelo')) {
            $atributos_modelo = 'modelo:id,'. $request->atributos_modelo;
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        }else{
            $carroRepository->selectAtributosRegistrosRelacionados('modelo');
        }

        //verificando se existe parametro 'filtro' para realizar pesquisas especificas e formatando a query
        if ($request->has('filtro')){

            $carroRepository->filtro($request->filtro);
                                 
        }
        
        //verificando se existe parametro 'atributos' e formatando a query
        if($request->has('atributos')){            
            $atributos = $request->atributos;
            $carroRepository->selectAtributos($atributos);
            
        }
        return response()->json($carroRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {            
       $request->validate($this->carro->rules());

       $carro = $this->carro->create([
           'modelo_id' => $request->input('modelo_id'),
           'placa' => $request->placa,
           'disponivel' => $request->disponivel,
           'km' => $request->km
       ]);

       return response()->json($carro , 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $carro = $this->carro->with('modelo')->find($id);
        if ($carro === null) {
            return response()->json(['erro' => 'Recurso não encontrado'] , 404);
        }
        return response()->json($carro , 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $carro = $this->carro->find($id);

        if ($carro === null) {
            return response()->json(['erro' => 'Não foi possível realizar a atualização. Recurso não encontrado'] , 404);
        }

        //verificando se é request é tipo PATCH (parcial) ou PUT(completa)
        if ($request->method() === 'PUT') {
            //se for PUT vai atualizar todos os valores da tabela
            $request->validate($carro->rules());
            
        }else{
            //PATCH espera atualizar de forma parcial, entao temos que aplicar as regras apenas para os parametros que recebemos por request
            
            //array rules Dinamico
            $rulesDinamico = [] ;

            //percorro todos indices do array de rules() do Model
            foreach($carro->rules() as $input => $regras){
          
                if( array_key_exists( $input, $request->all() ) ){
                /* array_key_exists(<chave>,<array>) — Checa se uma chave ou índice existe em um array 
                1 - verificando se no array da request existe algum indice com o mesmo indice que o array de rules() 
                2- caso exista, vamos inserir no array rulesDinamico como indice o input e as regras como seu valor */
                    $rulesDinamico[$input] =  $regras;
                }
             }  
             $request->validate($rulesDinamico);         
        }
      
        $carro->fill($request->all());
        $carro->save();

        return response()->json($carro , 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $carro = $this->carro->find($id);

        if ($carro === null) {
            return response()->json(['erro' => 'Não foi possível realizar a exclusão. Recurso não encontrado'] , 404 );
        }

        $carro->delete();
        return response()->json(['msg'=>'Marca deletada com sucesso!'], 200);
    }
}
