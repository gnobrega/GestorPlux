/* 
 * Manipulação de grids
 */
var Grid = {
    
    /**
     * Id da tabela
     */
    _idTable: null,
    
    /**
     * Possibilita manipular a grid após o carregamento
     */
    _objGrid: null,
    
    /**
     * Entidade
     */
    _entity: null,
    
    /**
     * Modulo
     */
    _module: null,
    
    /**
     * Id selecionado
     */
    _currentId: null,
    
    /**
     * Construtor
     */
    init: function(_idTable, _entity, _module) {
        this._idTable = _idTable;
        this._entity = _entity;
        this._module = _module;
        
        //Carrega os eventos padrões
        Grid.loadEvents();
    },
    
    /**
     * Configuração padrão para as grids
     */
    config: {
        "processing": true,
        "serverSide": true,
        //"ajax": "/service/load/genero", //Sobrescrever
        "dom": "<'row'<'col-xs-6'l><'col-xs-6'f>r>"+
		"t"+
		"<'row'<'col-xs-6'i><'col-sm-12 col-md-6'p>>",
        "columns": [{"data":"id"}],
        "lengthMenu": [[20, 50, 100, -1], [20, 50, 100, "Ver tudo"]],
        "language": {
            "lengthMenu": "Registros por página _MENU_",
            "zeroRecords": "Não existem registros",
            "info": "Exibindo página _PAGE_ de _PAGES_",
            "infoEmpty": "Não há registros disponíveis",
            "infoFiltered": "(filtrado a partir de _MAX_ registos totais)",
            "search": "Filtro",
            "preview": "Filtro",
            "processing": "Carregando...",
            "paginate": {
                "previous": "Anterior",
                "next": "Próxima"
            }
        }
    },
    
    //Recupera o id do item selecionado
    getItemId: function($objSel) {
        var tr = $objSel.closest("tr");
        var paramsTrId = tr.attr('id').split('-');
        var itemId = paramsTrId.pop();
        return itemId;
    },
    
    /**
     * Eventos padrões
     */
    loadEvents: function() {
        var thisGrid = this;
        
        //New - Ajax
        $( document ).on('click', "button.grid-act-new", function(e) {
            e.preventDefault();
            
            if( $(this).attr('ajax') == 'true' ) {
                
                //Ajax
                block();

                //Carrega a modal
                var link = null;
                if( $(this).attr('link') ) {
                    link = $(this).attr('link');
                } else {
                    link = '/' + thisGrid._module + '/' + thisGrid._entity + '/cadastrar/';
                }
                
                //Se for um template adiciona a url
                if( typeof TPL != 'undefined' ) {
                    link += '?tpl=' + TPL;
                }
                
                $('.container-ajax').load(link, null, function() {});    
            } else {
                if( typeof URL_CADASTRAR != 'undefined' ) {
                    var link = URL_CADASTRAR;
                } else {
                    var link = '/' + thisGrid._module + '/' + thisGrid._entity + '/cadastrar/';
                }
                
                //Default - redirect
                window.location = link;
            }
            
        });
        
        //Edit
        $( document ).on( 'click', '#'+this._idTable+" a.grid-act-edit", function(e) {
            e.preventDefault();
            
            if( $(this).attr('ajax') == 'true' ) {
                block();

                //Recupera o id
                var tr = $(this).closest("tr");
                var paramsTrId = tr.attr('id').split('-');
                var itemId = paramsTrId.pop();

                //Carrega a modal
                var urlEdit = null;
                if (typeof URL_EDITAR !== "undefined") {
                    urlEdit = URL_EDITAR + '/id/' + itemId;
                } else {
                    urlEdit = '/' + thisGrid._module + '/' + thisGrid._entity + '/editar/id/' + itemId;
                }
                
                //Exclusivo para templates
                if( typeof TPL != 'undefined' ) {
                    urlEdit += '/tpl/' + TPL;
                }
                
                $('.container-ajax').load(urlEdit, null, function() {});
            } else {
                //Default - redirect
                var itemId = Grid.getItemId($(this));
                if (typeof URL_EDITAR !== "undefined") {
                    var params = URL_EDITAR.split('?');
                    var link = params[0] + "/id/" + itemId;
                    if( params.length > 1 ) {
                        link += "?" + params[1];
                    }
                } else {
                    var link = '/' + thisGrid._entity + '/editar/id/' + itemId;
                }
                window.location = link;
            }
        });
        
        //Del
        $( document ).on( 'click', '#'+this._idTable+" a.grid-act-del", function(e) {
            e.preventDefault();
            
            //Recupera o id
            var tr = $(this).closest("tr");
            var paramsTrId = tr.attr('id').split('-');
            var itemId = paramsTrId.pop();
            
            Grid._currentId = itemId;
            confirme("Você tem certeza que deseja excluir esse registro?", callbackExclusao);
        });
        
        //Resposta do pedido de exclusão
        function callbackExclusao(resp) {
            
            //Ok
            if( resp ) {
                Form.excluir(thisGrid._entity, Grid._currentId, thisGrid._module);
            }
        }
    }
}


