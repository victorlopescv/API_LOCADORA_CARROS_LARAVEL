<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller 
{
    public function login(Request $request){
    
        $credenciais = $request->all(['email','password']);

        //Autenticação (email e senha)
        $token = auth('api')->attempt($credenciais);  //auth('api') -> configurado em config/auth.php onde foi feito a configuração do JWT / attempt() -> tenta procurar no banco users onde tbm foi configurado la em config/auth.php em providers o Model que sera feito a tentativa comparação
        
        if($token){ //usuario autenticado com sucesso

            return response()->json(['token' => $token]);

        }else { //erro de usuario ou senha

            return response()->json(['msg'=>'Usuário não autenticado'],403);

            //401 = Unauthorized -> não autorizado
            //403 = forbidden -> proibido (login inválido)
        }
    }

    public function logout(){
         auth('api')->logout();
        return response()->json(['msg'=>'Logou foi realizado com sucesso!']);
    }
    public function refresh(){
        $token = auth('api')->refresh();
        return response()->json(['token' => $token]);
    }
    public function me(){
        return response()->json(auth()->user());
    }
}
