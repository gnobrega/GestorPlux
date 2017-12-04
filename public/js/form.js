/* 
 * Manipulação de formulários
 */
var Form = {
    /**
     * Construtor
     */
    init: function (formId) {
        
        //Máscaras
        $('input.phone').mask("(99) 9999-9999?9");
        $('input.cep').mask("99999-999");
        $('input.time').mask("99:99");
        $('input.cnpj').mask("999.999.999/999-99");
        $('input.date').datepicker({
            format: 'dd/mm/yyyy',
            language: 'pt'
        });
        $('.chosen-select').chosen();
    },
    
    /**
     * Callback genérico para qualquer método. Deve ser sobrescrito
     */
    callback: function() {},
    
    /**
     * Filtra uma combo a partir da seleção da outra
     */
    filterCombo: function($combo, $comboParent, attrs) {
        $comboParent.change(function() {
            wait();
            var entity = $combo.attr('entity');
            var filter = {
                entityParent: {
                    entity: $comboParent.attr('entity'),
                    id: $comboParent.val()
                }
            };
            var data = {filter:filter};
            
            //Retorna dados extras
            if( attrs.extra != undefined ) {
                data.extra = attrs.extra;
            }
            
            $.post("/"+entity+"/load-combo", data, function(rs) {
                wait(true);
                if( rs.status == 'success') {

                    //Limpa as opções
                    $combo.html('');

                    //Adiciona os itens
                    var $optSelecione = $("<option />").val("").text('[Selecione]');
                    $combo.append($optSelecione);
                    
                    $.each(rs.data, function() {
                        $combo.append($("<option />").val(this.id).text(this.label));
                        $.each(this, function(k, v) {
                            if( k != 'id' && k != 'label' ) {
                                $combo.attr(k, v);
                            }
                        });
                    });
                    
                    //Se houver callback executa
                    Form.callback();
                } else {
                    notification(rs.msgErro, 'danger');
                }
            }, 'json');
        });
    },
    
    /**
     * Adiciona um botão de novo registro ao lado da combo
     */
    addButtonNew: function($combo, data) {
        var entity = $combo.attr('entity');
        var link = '/' + entity + '/cadastrar/';
        
        //Cria o evento de sucesso
        $("body").on("form.save.success", function(e, resp) {
            var html = "<option value='" + resp.id + "' >" + resp.nome + "</option>";
            $combo.append(html);
            $combo.find('option[value=' + resp.id + ']').prop('selected', true);
        });

        //Ajax
        block();

        //Carrega o formulário de cadastro
        $('.container-ajax').load(link, data, function() {});
    }
}