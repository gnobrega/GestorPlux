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
        $('.chosen-select').chosen();
        $('.input-group.date').datepicker({
            todayBtn: "linked",
            autoclose: true,
            language: "pt-BR",
            format: 'dd/mm/yyyy'
        });
        //Carrega a Treeview
        $('input.date').datepicker({
            todayBtn: "linked",
            autoclose: true,
            language: "pt-BR",
            format: 'dd/mm/yyyy'
        });
        
        //Validation
        var VALIDATION_MSG_REQUIRED = "Esse campo é obrigatório";
        jQuery.extend(jQuery.validator.messages, {
            required: VALIDATION_MSG_REQUIRED
        });
        $.validator.setDefaults({ ignore: ":hidden:not(.chosen-select)" })
        $.validator.addMethod('select-not-null', function (value, element, param) {
            if( value == "[*SELECT_NULL*]" || value == "" ) {
                return false;
            } else {
                return true;
            }
        }, VALIDATION_MSG_REQUIRED);
        $("#"+formId).validate();
    },
    
    /**
     * Callback genérico para qualquer método. Deve ser sobrescrito
     */
    callback: function() {},
    
    /**
     * Filtra uma combo a partir da seleção da outra
     */
    filterCombo: function($combo, $comboParent, url, attrs, autoexec) {
        function startFilterCombo() {
            $combo.html("");
            $combo.trigger("chosen:updated");
            if( $comboParent.val() == SELECT_VALUE_NULL ) {
                return false;
            }
            
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
            if( typeof(attrs) != 'undefined' ) {
                if( typeof(attrs.extra) != 'undefined' ) {
                    data.extra = attrs.extra;
                }
            }
            
            $.post(url, data, function(rs) {
                wait(true);
                if( rs.status == 'success') {

                    //Limpa as opções
                    $combo.html('');

                    //Adiciona os itens
                    if( !$combo.prop("multiple") ) {
                        var $optSelecione = $("<option />").val("").text('[Selecione]');
                        $combo.append($optSelecione);
                    }
                                       
                    $.each(rs.data, function() {
                        $combo.append($("<option />").val(this.id).text(this.label));
                        $.each(this, function(k, v) {
                            if( k != 'id' && k != 'label' ) {
                                $combo.attr(k, v);
                            }
                        });
                    });
                    $combo.trigger("chosen:updated");
                    
                    //Se houver callback executa
                    Form.callback();
                } else {
                    toastr.error(rs.msgErro);
                }
            }, 'json');
        }
        if( autoexec ) {
            startFilterCombo();
        }
        $comboParent.change(function() {
            startFilterCombo();
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