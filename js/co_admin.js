var calc_oils={
    'list':[],
    'nonce':'',
    'get_oil': function(id, callback){
        if(calc_oils.list.length){
            $.each(calc_oils.list,function(ind,oil){
                if(oil.id == id){
                    if(callback) callback(oil);
                    else return oil;
                }
            });            
        }
    }    
}
jQuery('document').ready(function($){    
    //ajax query for oils object
    
    $.post(ajaxurl,{'action':'get_oils'},function(result){
        calc_oils.list = result.oils;
        calc_oils.acids = result.acids;
        calc_oils.nonce = result.nonce;
        calc_oils.groups = result.groups;
        calc_oils.types = result.types;
        $.each(calc_oils.groups,function(ind,val){
            $('#co_oil_group').append($('<option>').attr("value",val).text(val));
        });
        var grouped = {};
        $.each(calc_oils.acids,function(ind,acid){
            if(typeof(grouped[acid.type]) == "undefined") grouped[acid.type] = new Array();
            grouped[acid.type].push(acid);
            
        });
        $.each(calc_oils.types,function(ind,type){
            var typeLocal = {'polyunsaturated':'полиненасыщенные','monounsaturated':'мононенасыщенные','saturated':'насыщенные'};
            $('#co_acids_table').append($('<tbody>').attr('id',type).append($('<tr>').attr({'class':'clickable','data-toggle':'collapse','data-target':'#group-'+type,'aria-expanded':'false', 'aria-controls':'group-'+type}).append($('<td>').attr({'colspan':3}).text(typeLocal[type]))));
            $('#co_acids_table').append($('<tbody>').attr({'id':'group-'+type,'class':'collapse'}));
            if(typeof(grouped[type]) == "object"){
                $.each(grouped[type],function(ind,gr_acid){
                    $('#co_acids_table tbody#group-'+type).append($('<tr>')
                        .append($('<td>').text(gr_acid.id))
                        .append($('<td>').text(gr_acid.name))
                        .append($('<td>').append($('<input>').attr('type','text'))))
                });     
            }
        });        
    },'json');
    
    //ajax get oil acids list, modal fill & show
    $('.oil-edit').each(function(){
        $(this).on('click',function(){
            calc_oils.get_oil($(this).attr('btn-data'), function(oil){
                $('#co_oil_group').val(oil.o_group);
                $("#co_oil_name").val(oil.name);
                $("#co_oil_iodine").val(oil.iodine);
                
                $('#co_oils_modal').modal('toggle');
            });            
        });
    });
    //modal clear & save 
    $('#co_oils_modal').on('hidden.bs.modal', function (e) {
        
    })
});
