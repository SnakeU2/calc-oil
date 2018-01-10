var calc_oils={
    'list':[],
    'nonce':'',
    'get_oil': function(id, callback){
        if(calc_oils.list.length){
            var oil = $.grep(calc_oils.list,function(oil,ind){
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
        switch(operation){
            case 'update': $.map(calc_oils.list,function(lOil,ind){if(lOil.id == oil.id) calc_oils.list[ind] = oil;});
                break;
            case 'insert': calc_oils.list.push(oil);
                break;
            case 'delete': calc_oils.list.splice(oil,1); //TODO: its WRONG! only stub!
            
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
            calc_oils.get_oil($(this).attr('btn-data'), function(oil){
                $('#co_oil_group').val(oil.o_group);
                $("#co_oil_name").val(oil.name);
                $("#co_oil_name").data('oil_id',oil.id);
                $("#co_oil_iodine").val(oil.iodine);
                $.each(oil.acids,function(ind,o_acid){
                    $('#co_acids_table input#acid-id-'+o_acid.id).val(parseFloat(o_acid.percent*100).toFixed(2));
                })
                $('#co_oils_modal').modal('toggle');
            });            
        });
    });
    $('#add-oil').on('click',function(){
        $("#co_oil_name").data('oil_id',-1);      
        $('#co_oils_modal').modal('toggle');
    })
    //modal clear & save 
    $('#co_oils_modal').on('hidden.bs.modal', function (e) {
        $('#co_oil_group').val('');
        $("#co_oil_name").val('');
        $("#co_oil_name").removeData('oil_id');
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
        //prepare data       
        var data = {
            'action':'update_oil',
            'oil': JSON.stringify({
                'id': $("#co_oil_name").data('oil_id'),
                'name':$("#co_oil_name").val(),
                'o_group':$('#co_oil_group').val(),
                'iodine': $("#co_oil_iodine").val(),
                'acids':$.map($('#co_acids_table input'),function(inp){if(parseFloat($(inp).val())) return {'id':$(inp).data('acid_id'),'percent':(parseFloat($(inp).val())/100).toFixed(4)}})
                }),
            'nonce': calc_oils.nonce
        }
        //send
        $.post(ajaxurl,data,function(result){
            
        },'json').fail(function(xhr,text){
            console.log(text);
        });
    })
});
