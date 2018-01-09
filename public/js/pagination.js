/** 
 * Cria a estrutura de paginação
 */
function Pagination(totalItens, limit, current) {
    this.totalItens = totalItens;
    this.limit = limit;
    this.current = current;
    this.totalPages = Math.ceil(totalItens/limit)
    this.pages = new Array();
    this.id = "pagination-" + parseInt(Math.random()*100000);
    
    /**
     * Renderiza o conteúdo
     */
    this.render = function() {
        
        //Gera as páginas
        this.mountPages();
        
        //Monta o html
        var clsPrevious = "";
        var clsNext = "";
        if( this.current == 1 ) {
            clsPrevious = "disabled";
        }
        if( this.current == this.totalPages ) {
            clsNext = "disabled";
        }
                
        var html = '\n\
            <div class="row" id="'+this.id+'">\n\
                <div class="col-xs-6">\n\
                    <div class="dataTables_info" id="tb-633_info" role="status" aria-live="polite">\n\
                        Exibindo página '+this.current+' de '+this.totalPages+' ('+this.totalItens+' registros)</div>\n\
                </div>\n\
                <div class="col-sm-12 col-md-6">\n\
                    <div class="dataTables_paginate paging_simple_numbers" id="tb-633_paginate">\n\
                        <ul class="pagination">\n\
                            <li class="paginate_button previous '+clsPrevious+'"><a href="#">Anterior</a></li>\n';
        //Páginas
        for( i in this.pages ) {
            var cls = "";
            if( this.pages[i].number == this.current ) {
                cls = "active";
            }
            html += '\n\
                            <li class="paginate_button '+cls+'"><a href="#">'+this.pages[i].number+'</a></li>\n';
        }
                            
        html += '\n\
                            <li class="paginate_button next '+clsNext+'"><a href="#">Próxima</a></li>\n\
                        </ul>\n\
                    </div>\n\
                </div>\n\
            </div>\n\
        ';

        return html;
    };
    
    /**
     * Monta as páginas
     */
    this.mountPages = function() {
        this.pages = new Array();
        this.last = Math.ceil(this.current/8) * 8;
        this.first = this.last - 7;
        for( var i = this.first; i <= this.last; i ++ ) {
            this.pages.push({
                "number":i
            });
            if( i >= this.totalPages ) {
                break;
            }
        }
    },
    
    /**
     * Define a página atual
     */
    this.setCurrent = function(current) {
        this.current = current;
        this.mountPages();
    }
}


