var calc_oils={
    'list':[],
    'nonce':'',
    'get_oilByID': function(id, callback){
        if(this.list.length){
            var oil = $.grep(this.list,function(oil,ind){
                return oil.id == id;
            });
            if(oil.length){
                if(callback) callback(oil[0]);
                return oil[0];
            }           
        }
        return null;
    },
    'update_list':function(oil,operation){
        if(operation == "insert"){
            calc_oils.list.push(oil);
        }
        if(operation == "update"){
            $.each(calc_oils.list,function(ind,oldOil){
                if(oldOil.id == oil.id) calc_oils.list[ind] = oil;
            });
        }
        if(operation == 'delete'){
            var oldOil = calc_oils.get_oilByID(oil);
            if(oldOil !== null) calc_oils.list.splice(oldOil,1);
        }
    }    
}

jQuery('document').ready(function($){    
    //ajax query for oils object, prepare modal
    
    $.post(ajaxurl,{'action':'get_oils'},function(result){
        calc_oils.list = result.oils;
        calc_oils.acids = result.acids;
        calc_oils.nonce = result.nonce;
        calc_oils.groups = result.groups;
        calc_oils.types = result.types;
        $.each(calc_oils.groups,function(ind,val){
            $('#co_oil_group').append($('<option>').attr("value",val).text(val));
        });
        $('#co_oil_group').val('');
        $("#co_oil_iodine").val(0);
        var grouped = {};
        $.each(calc_oils.acids,function(ind,acid){
            if(typeof(grouped[acid.type]) == "undefined") grouped[acid.type] = new Array();
            grouped[acid.type].push(acid);
            
        });
        $.each(calc_oils.types,function(ind,type){
            var typeLocal = {'polyunsaturated':'полиненасыщенные','monounsaturated':'мононенасыщенные','saturated':'насыщенные'};
            $('#co_acids_table').append($('<tbody>').attr('id',type).append($('<tr>').attr({'class':'clickable','data-toggle':'collapse','data-target':'#group-'+type,'aria-expanded':'false', 'aria-controls':'group-'+type}).append($('<td>').attr({'colspan':3}).append($('<span>').attr('class','colapse-caption').text(typeLocal[type])))));
            $('#co_acids_table').append($('<tbody>').attr({'id':'group-'+type,'class':'collapse'}));
            if(typeof(grouped[type]) == "object"){
                $.each(grouped[type],function(ind,gr_acid){
                    $('#co_acids_table tbody#group-'+type).append($('<tr>')
                        .append($('<td>').text(gr_acid.id))
                        .append($('<td>').text(gr_acid.name))
                        .append($('<td>').append($('<input>').attr({'type':'text','id':'acid-id-'+gr_acid.id}).val(0).data('acid_id',gr_acid.id))));
                });     
            }
        });        
    },'json');
    
    //get oil, modal fill & show
    $('.oil-edit').each(function(){
        $(this).on('click',function(){
            calc_oils.get_oilByID($(this).data('oil'), function(oil){
                $('#co_oil_group').val(oil.o_group);
                $("#co_oil_name").val(oil.name);
                $("#co_oil_name").attr('data-oil',oil.id);
                $("#co_oil_iodine").val(oil.iodine);
                $.each(oil.acids,function(ind,o_acid){
                    $('#co_acids_table input#acid-id-'+o_acid.id).val(parseFloat(o_acid.percent*100).toFixed(2));
                })
                $('#co_oils_modal').modal('toggle');
            });            
        });
    });
    //delete oil
    $('.oil-del').each(function(){
        $(this).on('click',function(){
            if(confirm('Вы действительно хотите удалить запись:\r\n'+calc_oils.get_oilByID($(this).data('oil')).name+"?")){            
                $.post(ajaxurl,{'action':'remove_oil',oil:$(this).data('oil'),'nonce': calc_oils.nonce},function(result){                    
                    var oilName = calc_oils.get_oilByID(result.oil).name;
                    //update list
                    calc_oils.update_list(result.oil,'delete');
                    //update table
                    $("#oil-row-"+result.oil).remove();
                    //show message
                    alert(result.msg+ " "+oilName);
                },'json').fail(function(xhr,text){
                    console.log(text);
                });
            }
        });
    });

    //add oil
    $('#add-oil').on('click',function(){
        $("#co_oil_name").attr('data-oil',-1);      
        $('#co_oils_modal').modal('toggle');
    })
    //modal clear & save 
    $('#co_oils_modal').on('hidden.bs.modal', function (e) {
        $('#co_oil_group').val('');
        $("#co_oil_name").val('');
        $("#co_oil_name").removeAttr('data-oil');
        $("#co_oil_iodine").val(0);
        $.each($('#co_acids_table input'),function(ind,el){
            $(this).val(0);
        });
    })

    $('#btn-save-oil').on('click',function(){
        //check before send
        var errMsg = "";
        if (!$("#co_oil_name").val()) errMsg += "Укажите наименование\r\n";
        if (!$('#co_oil_group').val()) errMsg += "Выберите группу\r\n";
        var checkAcids = $.grep($('#co_acids_table input'),function(el,ind){
            return $(el).val() != 0;
        });
        if(!checkAcids.length) errMsg += "Укажите % хотя бы для одной кислоты\r\n";
        if(errMsg){
            alert(errMsg);
            return false;
        }
        //check oil id
        if(typeof($('#co_oil_name').data('oil')) === "undefined") return false;
        //prepare data       
        var data = {
            'action':'update_oil',
            'oil': JSON.stringify({
                'id': $("#co_oil_name").data('oil'),
                'name':$("#co_oil_name").val(),
                'o_group':$('#co_oil_group').val(),
                'iodine': $("#co_oil_iodine").val(),
                'acids':$.map($('#co_acids_table input'),function(inp){if(parseFloat($(inp).val())) return {'id':$(inp).data('acid_id'),'percent':(parseFloat($(inp).val())/100).toFixed(4)}})
                }),
            'nonce': calc_oils.nonce
        }
        //remove data-oil
        $("#co_oil_name").removeAttr('data-oil');
        $("#co_oil_name").removeData('oil');
        //send
        $.post(ajaxurl,data,function(result){
            //set attribute data
            $("#co_oil_name").attr('data-oil',result.oil.id);
            $("#co_oil_name").data('oil',result.oil.id);
            //update list
            calc_oils.update_list(result.oil,result.action);
            //update table
            if(result.action == "update"){
               var tds = $("#oil-row-"+result.oil.id+" td");
               $(tds[1]).text(result.oil.name);
               $(tds[2]).text(result.oil.o_group);
               $(tds[3]).text(result.oil.iodine);
            }
            if(result.action == 'insert'){
                $('#tab-oils tbody').append($('<tr>').attr({'id':'oil-row-'+ result.oil.id})
                    .append($('<td>').attr({'td-data':"id"}).text(result.oil.id))
                    .append($('<td>').attr({'td-data':"name"}).text(result.oil.name))
                    .append($('<td>').attr({'td-data':"group"}).text(result.oil.o_group))
                    .append($('<td>').attr({'td-data':"iodine"}).text(result.oil.iodine))
                    .append($('<td>' )
                        .append($('<button>').addClass("btn btn-success oil-edit").attr({'data-oil':result.oil.id}).text('Редактировать').on('click',function(){
                                calc_oils.get_oilByID($(this).data('oil'), function(oil){
                                    $('#co_oil_group').val(oil.o_group);
                                    $("#co_oil_name").val(oil.name);
                                    $("#co_oil_name").attr('data-oil',oil.id);
                                    $("#co_oil_iodine").val(oil.iodine);
                                    $.each(oil.acids,function(ind,o_acid){
                                        $('#co_acids_table input#acid-id-'+o_acid.id).val(parseFloat(o_acid.percent*100).toFixed(2));
                                    })
                                    $('#co_oils_modal').modal('toggle');
                                });            
                            }).after('\xa0'))                        
                        .append($('<button>').addClass("btn btn-danger oil-del").attr({'data-oil':result.oil.id}).text('X').on('click',function(){
                                if(confirm('Вы действительно хотите удалить запись:\r\n'+calc_oils.get_oilByID($(this).data('oil')).name+"?")){            
                                    $.post(ajaxurl,{'action':'remove_oil',oil:$(this).data('oil'),'nonce': calc_oils.nonce},function(result){
                                    
                                    },'json').fail(function(xhr,text){
                                        console.log(text);
                                    });
                                }
                            }))
                    )                    
                );
            }
            //show message
            alert(result.msg + " " + result.oil.name);
        },'json').fail(function(xhr,text){
            console.log(text);
        });
    })
});
