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
    public function pesquisar($inicio, $fim, $limite, $pag = 1) {
        $adapter = $this->getAdapter();
        $offset = ($pag - 1) * $limite;
        $where = "data_foto BETWEEN '{$inicio}' AND '{$fim}'";
        
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
                ->where("data_foto BETWEEN '{$inicio}' AND '{$fim}'")
                ->limit($limite, $offset)
                //->order("data_foto DESC")
                        ;
        
        //Carrega o resultado
        $indices = $adapter->fetchAll($sql);
        return array(
            'total' => $total,
            'indices' => $indices,
            'limite' => $limite
        );
    }
    
}