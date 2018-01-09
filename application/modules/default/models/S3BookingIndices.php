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
    public function pesquisar($inicio, $fim, $limite, $pag = 1, $ambienteId = null) {
        $adapter = $this->getAdapter();
        $offset = ($pag - 1) * $limite;
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        if( $ambienteId ) {
            $where .= " AND id_ambiente = " . $ambienteId;
        }
        
        //Calcula o total de itens
        $sql = $adapter
                ->select()
                ->from($this->_name, 'COUNT(*) total')
                ->where($where);
        $rs = $adapter->fetchAll($sql);
        $total = $rs[0]['total'];
        
        $sql = $adapter
                ->select()
                ->from($this->_name)
                ->where($where)
                ->limit($limite, $offset)
                ->order(array("data_foto DESC", "tela ASC", "id DESC"));
        
        //Carrega o resultado
        //echo "<pre>{$sql}</pre>";die;
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
    public function calcularFotoAmbientes($inicio, $fim) {
        $adapter = $this->getAdapter();
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        
        //Calcula o total de itens
        $sql = $adapter
            ->select()
            ->from($this->_name, 'id_ambiente, COUNT(*) total')
            ->where($where)
            ->group('id_ambiente');
        
        $rs = $adapter->fetchAll($sql);
        return $rs;
    }
    
}