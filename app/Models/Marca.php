<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    protected $fillable = ['nome','imagem'];

    public function modelos(){
        //Marca tem muitos Modelos
        return $this->hasMany('App\Models\Modelo','marca_id','id');
    }

    public function rules(){
        return [
            'nome' => 'required|unique:marcas,nome,'.$this->id.'|min:3',
            'imagem' => 'required|file|mimes:png'
        ];
    }
        // unique recebe 3 parametros: - 1)tabela, 2)coluna da tabela, 3) exceto id informado não valera o unique
    public function feedback(){
        return [
            'required'=>'O campo :attribute precisa ser preenchido',
            'imagem.mimes' => 'Arquivo deve ser um PNG',
            'nome.unique' => 'Marca já existe',
            'nome.min' => 'Nome precisa ter no mínimo 3 caracters'
        ];
    }

}
