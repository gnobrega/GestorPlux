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
    public function pesquisar($canais, $inicio, $fim, $limite, $pag = 1, $ambienteId = null) {
        $adapter = $this->getAdapter();
        $offset = ($pag - 1) * $limite;
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        if( $ambienteId ) {
            $where .= " AND id_ambiente = " . $ambienteId;
        } else {
            $where .= " AND ambiente.id_canal IN (" . implode(",", $canais) . ")";
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
                ->from($this->_name)
                ->join("ambiente", "ambiente.id = {$this->_name}.id_ambiente")
                ->where($where)
                ->limit($limite, $offset)
                ->order(array("data_foto DESC", "tela ASC", $this->_name . ".id DESC"));
        
        //Carrega o resultado
        echo "<pre>{$sql}</pre>";die;
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
    public function calcularFotoAmbientes($canais, $inicio, $fim) {
        $adapter = $this->getAdapter();
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        $where .= " AND ambiente.id_canal IN (" . implode(",", $canais) . ")";
        
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
    
}