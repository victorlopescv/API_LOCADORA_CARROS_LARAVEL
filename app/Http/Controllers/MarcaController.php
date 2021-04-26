<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use Illuminate\Http\Request;
use App\Repositories\MarcaRepository;

class MarcaController extends Controller
{

    public function __construct(Marca $marca){
        $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $marcaRepository = new MarcaRepository($this->marca);

        //verificando se existe parametro 'atributos_modelo' e formatando a query e utilizando get() para consultar
        if ($request->has('atributos_modelo')) {
            $atributos_modelo = 'modelos:marca_id,'. $request->atributos_modelo;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        }else{
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }

        //verificando se existe parametro 'filtro' para realizar pesquisas especificas e formatando a query
        if ($request->has('filtro')){

            $marcaRepository->filtro($request->filtro);
                                 
        }
        
        //verificando se existe parametro 'atributos' e formatando a query
        if($request->has('atributos')){            
            $atributos = $request->atributos;
            $marcaRepository->selectAtributos($atributos);
            
        }
        return response()->json($marcaRepository->getResultado(), 200);
    }

      /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
/*
        *no caso de  api, para que seja possivel o validate() retorna a ultima route,(no caso não tem é 'stateless')
         e indicar suas msg de feedback de validação, por parte do client, ou seja no request temos que indicar no headers que a rquisição aceita um application/json através de um Accept como parametro no client
         Headers Request 
         Accept = application/json -avisa previamente que deve retornar um json
*/      
        
        $request->validate($this->marca->rules() , $this->marca->feedback());
       
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens','public'); /* 
       *store(<nome_diretorio> , <caminho_armazenagem>) -> persiste os arquivos em um Disk - config/filesystems.php (onde fica as configurações de armazenagem)
       1 - armazena de acordo com a configuração la em config/filesystems.php
       2- pode ser armazenado em storage/app , storage/app/public dentro do aplicativo (essas pastas não sao publicas) ou no s3 armazenagem em nuvem da amazon
    */ 
        $marca = $this->marca->create([
            'nome' => $request->input('nome'),
            'imagem' => $imagem_urn
        ]);

        return response()->json($marca , 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if ($marca === null) {
            return response()->json(['erro' => 'Recurso não encontrado'] , 404);
        }
        return response()->json($marca , 200);
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
        //dd($request->nome);
       // dd($request->file('imagem'));

        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json(['erro' => 'Não foi possível realizar a atualização. Recurso não encontrado'] , 404);
        }

        //verificando se é request é tipo PATCH (parcial) ou PUT(completa)
        if ($request->method() === 'PUT') {
            //se for PUT vai atualizar todos os valores da tabela
            $request->validate($marca->rules(),$marca->feedback());
            
        }else{
            //PATCH espera atualizar de forma parcial, entao temos que aplicar as regras apenas para os parametros que recebemos por request
            
            //array rules Dinamico
            $rulesDinamico = [] ;

            //percorro todos indices do array de rules() do Model
            foreach($marca->rules() as $input => $regras){
          
                if( array_key_exists( $input, $request->all() ) ){
                /* array_key_exists(<chave>,<array>) — Checa se uma chave ou índice existe em um array 
                1 - verificando se no array da request existe algum indice com o mesmo indice que o array de rules() 
                2- caso exista, vamos inserir no array rulesDinamico como indice o input e as regras como seu valor */
                    $rulesDinamico[$input] =  $regras;
                }
             }  
             $request->validate($rulesDinamico , $marca->feedback());         
        }

        if ($request->file('imagem')) {
            
            Storage::disk('public')->delete($marca->imagem);
            $imagem = $request->file('imagem');
            $imagem_urn = $imagem->store('imagens', 'public');
        }else{
            $imagem_urn = $marca->imagem;
        }

        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        $marca->save();
/*
        $marca->update([ 
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
*/
        return response()->json($marca , 201);
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
        $marca = $this->marca->find($id);
        
        if ($marca === null) {
            return response()->json(['erro' => 'Não foi possível realizar a exclusão. Recurso não encontrado'] , 404 );
        }

        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();
        return response()->json(['msg' => 'Marca deletada com sucesso!'] ,200);
    }
}
