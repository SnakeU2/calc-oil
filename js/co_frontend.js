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
    'get_group':function(gName,callback){
        if(calc_oils.list.length){
            var group = $.grep(calc_oils.list,function(oil,ind){
                return oil.o_group == gName;
            });
            if(group.length){
                if(callback) callback(group);
                return group;
            }
        }
        return null;
    },
    'get_oil_acids':function(oil,percents=false){
        if(typeof(oil) != "object") oil = this.get_oil(oil);
        if(oil.acids.length){
            var acidsIds = [];
            $.each(oil.acids,function(ind,acid){acidsIds.push(acid.id)});
            var oilAcids =  $.grep(this.acids,function(acid){
                return $.inArray(acid.id,acidsIds) != -1;
            });
            if(percents){
                $.each(oilAcids,function(ind,acid){
                    $.each(oil.acids,function(ind,oilAcid){
                        if(oilAcid.id == acid.id) acid.percent = oilAcid.percent;
                    });
                });
            }
            return oilAcids;
        }
        return [];        
    }        
}

//jQuery.noConflict();
jQuery('document').ready(function($){    
    //ajax query for oils object, prepare modal
    
    $.post(co_ajax.url,{'action':'get_oils'},function(result){
        calc_oils.list = result.oils;
        calc_oils.acids = result.acids;
        calc_oils.nonce = result.nonce;
        calc_oils.groups = result.groups;
        calc_oils.types = result.types;
        $.each(calc_oils.groups,function(ind,val){
            $('#co_oil_group').append($('<option>').attr("value",val).text(val));
        });
        $('#co_oil_group').val('');
    },'json');

    $('#co_open_choise').on('click',function(){
        $('#co_oils_modal').modal('show');        
    });

    $('#co_oil_group').on('change',function(){
        $('#co_choise_oil_table tbody').empty();
        $('#choise-acids').empty();
        var group = calc_oils.get_group($(this).val());
        if(group.length){
            $.each(group,function(ind,oil){
            $('#co_choise_oil_table tbody').append($('<tr>').data('oil',oil).on('click',function(){
                    var acids = calc_oils.get_oil_acids($(this).data('oil'),true);
                    $('#choise-acids').empty();
                    if(acids.length){                        
                        $.each(acids,function(ind,acid){
                            $('#choise-acids').append($('<div>').attr({'class':'p-1 oil-acid-info'}).text(acid.name).append($('<span>').html(" - "+parseInt(acid.percent*100) + "%; &nbsp;")));
                        });
                    }
                    $('#co_choise_oil_table tbody tr').each(function(){$(this).removeClass('choise-selected')});
                    $(this).addClass('choise-selected');
                    $('#btn-choose-oil').data('choosen',$(this).data('oil'));
                })
                .append($('<td>').text(oil.id))
                .append($('<td>').text(oil.name))
                .append($('<td>').text(oil.iodine)));
            });

        }
    });
});
