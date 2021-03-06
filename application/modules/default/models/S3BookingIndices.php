<?php

/**
 * Casse modelo da entidade
 *
 * @author gustavonobrega
 */
class Model_S3BookingIndices extends Model_Abstract {
    
    /** Table name */
    protected $_name    = 's3_booking_indices';
    
    /**
     * Pesquisa os índices
     */
    public function pesquisar($canais, $campAmbientesIds, $inicio, $fim, $limite, $pag = 1, $ambienteId = null) {
        
        //Se não houver ambientes retorna vazio
        if( !count($campAmbientesIds) ) {
            return array();
        }
        
        $adapter = $this->getAdapter();
        $offset = ($pag - 1) * $limite;
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        if( $ambienteId ) {
            $where .= " AND id_ambiente = " . $ambienteId;
        } else {
            $where .= " AND ambiente.id_canal IN (" . implode(",", $canais) . ")";
            $where .= " AND ambiente.id IN (" . implode(",", $campAmbientesIds) . ")";
        }
         
        //Calcula o total de itens
        $sql = $adapter
                ->select()
                ->from($this->_name, null)
                ->columns(array("COUNT(*) total"))
                ->join("ambiente", "ambiente.id = {$this->_name}.id_ambiente", null)
                ->where($where);
        $rs = $adapter->fetchAll($sql);
        $total = $rs[0]['total'];
        
        $sql = $adapter
                ->select()
                ->from($this->_name, array("*","id_indice"=>"id"))
                ->join("ambiente", "ambiente.id = {$this->_name}.id_ambiente")
                ->where($where)
                ->limit($limite, $offset)
                ->order(array("ambiente.nome ASC", "data_foto DESC", "tela ASC", $this->_name . ".id DESC"));

        //Carrega o resultado
        $indices = $adapter->fetchAll($sql);
        return array(
            'total' => $total,
            'indices' => $indices,
            'limite' => $limite
        );
    }
    
    /**
     * Calcula a quantidade de fotos por ambiente
     */
    public function calcularFotoAmbientes($canais, $campAmbientesIds, $inicio, $fim) {
        
        //Se não houver ambientes retorna vazio
        if( !count($campAmbientesIds) ) {
            return array();
        }
        
        $adapter = $this->getAdapter();
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        $where .= " AND ambiente.id_canal IN (" . implode(",", $canais) . ")";
        $where .= " AND ambiente.id IN (" . implode(",", $campAmbientesIds) . ")";
        
        //Calcula o total de itens
        $sql = $adapter
            ->select()
            ->from($this->_name, 'id_ambiente, COUNT(*) total')
            ->join("ambiente", "ambiente.id = {$this->_name}.id_ambiente")
            ->where($where)
            ->group('id_ambiente');
        $rs = $adapter->fetchAll($sql);
        return $rs;
    }
    
    /**
     * Carrega os índices para a geração do booking
     */
    public function carregarIndicesBooking($where) {
        $adapter = $this->getAdapter();
        $sql = $adapter
            ->select()
            ->from($this->_name)
            ->join("ambiente", "ambiente.id = {$this->_name}.id_ambiente", null)
            ->where($where)
            ->order(array("ambiente.nome", $this->_name.".data_foto"));
        $rs = $adapter->fetchAll($sql);
        return $rs;
    }
}