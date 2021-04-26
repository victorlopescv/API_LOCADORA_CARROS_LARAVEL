<?php
    namespace App\Repositories;
    use Illuminate\Database\Eloquent\Model;

    abstract class AbstractRepository {

        public function __construct(Model $model){
            $this->model = $model;
        }

        public function selectAtributosRegistrosRelacionados($atributos){
           $this->model =  $this->model->with($atributos);
        }

        public function filtro($filtros){
            $filtro = explode(';', $filtros);
            
            foreach ($filtro as $key => $condicao) {
                $c = explode('@', $condicao);
                $this->model = $this->model->where($c[0], $c[1], $c[2]);
            } 
        }

        public function selectAtributos($atributos){
            $this->model = $this->model->selectRaw($atributos); //quando realizamos a consulta sem usar all() ou get() ele nos retorna uma build que nos permite continuar manuzeando a query
        }

        public function getResultado(){
            return $this->model->get();
        }
    }

?>