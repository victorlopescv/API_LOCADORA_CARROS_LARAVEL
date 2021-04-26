<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use Illuminate\Http\Request;
use App\Repositories\ModeloRepository;

class ModeloController extends Controller
{

   public function __construct(Modelo $modelo){
        $this->modelo = $modelo;
   } 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

       /*  select
            select como nome sugere seleciona as indices, ou, colunas das tabelas
            select -> recebe os parametros no seguinte formato: 'id','nome','imagem'
            selectRaw -> recebe os parametros no formato id,nome,imagem e consegue entender
        */  
          //all() -> não permite manuzear a query e já devolve uma collection
        //get() -> permite manuzear a query com um builder e devolve uma collection
        //quando usamos o with() temos que usar com get() para podemos manuzear antes de realizar a query

    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);
        //verificando se existe parametro 'atributos_modelo' e formatando a query e utilizando get() para consultar
        if ($request->has('atributos_marca')) {
            $atributos_marca = 'marca:id,'. $request->atributos_marca;
            $modeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
        }else{
            $modeloRepository->selectAtributosRegistrosRelacionados('marca');
        }
        //verificando se existe parametro 'filtro' para realizar pesquisas especificas e formatando a query
        if ($request->has('filtro')){

            $modeloRepository->filtro($request->filtro);
                                 
        }        
        //verificando se existe parametro 'atributos' e formatando a query
        if($request->has('atributos')){            
            $atributos = $request->atributos;
            $modeloRepository->selectAtributos($atributos);            
        }
        return response()->json($modeloRepository->getResultado(), 200);
   }

        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //  
        
        $request->validate($this->modelo->rules());
        

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('modelos' , 'public');
 /*
       $this->modelo->fill($request->all()); //fill() sobrescreve os valores com o mesmo indice de outro array e mantem os demais indices que nao foram sobrescritos do original
       $this->modelo->imagem = $imagem_urn ;        
       $this->modelo->save(); //quando 'save' não recebe identifica nenhum id ele automaticamente intende que se trata de um novo arquivo  e realiza um create() no banco de dados
 */      
       $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' =>  $request->nome,
            'imagem' =>  $imagem_urn ,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares ,
            'air_bag' => $request->air_bag ,
            'abs' => $request->abs
        ]);
       
        return response()->json($modelo , 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $modelo = $this->modelo->with('marca')->find($id);
        if ($modelo=== null) {
           return response()->json(['erro'=>'recurso não encontrado'] , 404);
        }

        return response()->json($modelo , 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json(['erro'=>'recurso não encontrado'] , 404);
         }

        if ($request->method() === 'PUT') {
            $request->validate($modelo->rules());
        }else{
            
            $rulesDinamico = [];
            foreach($modelo->rules() as $input => $regras){
               
                if (array_key_exists($input , $request->all())) {
                    $rulesDinamico[$input] = $regras;
                }
            }
            $request->validate($rulesDinamico);
        }

        if ($request->file('imagem')) {
           
            Storage::disk('public')->delete($modelo->imagem);
            $imagem = $request->file('imagem');
            $imagem_urn = $imagem->store('modelos', 'public');  
        }else{
            $imagem_urn = $modelo->imagem;
        }

        $modelo->fill($request->all());
        $modelo->imagem = $imagem_urn;
        $modelo->save();
/*
        $modelo->update([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' =>  $request->numero_portas,
            'lugares' => $request->lugares ,
            'air_bag' =>  $request->air_bag  ,
            'abs' => $request->abs 
        ]);
            */  
        return response()->json($modelo , 201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json(['erro' => 'Recurso não localizado'],404);
        }

        Storage::disk('public')->delete($modelo->imagem);

        $modelo->delete();

        return response()->json(['msg' => 'Modelo deletado com sucesso!'] , 200);
    }
}
