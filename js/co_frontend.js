var calc_oils={
    'list':[],
    'nonce':'',
    'groups':[],
    'acids':[],    
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

var choosed_oils = {
    list:[],
    'recalc':function(){

    },
    'rebuild':function(){        
        $.each(calc_oils.groups,function(ind,val){ $('#co-caltab-gr-'+val).empty();});
        if(this.list.length){
            $.each(this.list,function(id,oil){
                $('#co-caltab-gr-'+oil.o_group).append($('<tr>').attr('data-oil',oil.id)
                    .append($('<td>').text(oil.name))
                    .append($('<td>')
                        .append($('<input>').attr({'class':'form-control form-control-sm','type':'text'}).val(oil.count))
                            .on('change',function(){
                                
                            })
                    )
                    .append($('<td>').on('click',function(){
                            choosed_oils.remove($(this).parent().data('oil'));
                        })
                        .append($('<div>').attr({'class':'co-calc-oil-tab-remove'}).text("X"))
                    )
                );

            });
        }
    },
    'add':function(oil){
        if(typeof(oil) !="object") oil = calc_oils.get_oil(oil);
        if(this.list.length){
            var exists = $.grep(this.list, function(lOil){ return lOil.id == oil.id });
            if(exists.length) return false;
        }
        if(!oil.count) oil.count = 0;
        this.list.push(oil);        
        this.rebuild();
    },
    'remove':function(oil){
        if(typeof(oil) == "object") oil = oil.id;
        if(this.list.length){
            var exists = -1;
            $.each(this.list,function(ind,lOil){if(lOil.id == oil) exists = ind});
            if(exists != -1) this.list.splice(exists,1);            
            this.rebuild();
        }
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
            $('#co_calc_oil_table').append($('<tbody>').attr({'id':'co-caltab-gr-'+val}));
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
            $('#co_choise_oil_table tbody').append($('<tr>').attr('data-oil',oil.id).on('click',function(){
                    var acids = calc_oils.get_oil_acids($(this).data('oil'),true);
                    $('#choise-acids').empty();
                    if(acids.length){                        
                        $.each(acids,function(ind,acid){
                            $('#choise-acids').append($('<div>').attr({'class':'m-1 p-1 oil-acid-info'}).text(acid.name).append($('<span>').html(" - "+parseInt(acid.percent*100) + "%; &nbsp;")));
                        });
                    }
                    $('#co_choise_oil_table tbody tr').each(function(){$(this).removeClass('choise-selected')});
                    $(this).addClass('choise-selected');                    
                })
                .append($('<td>').text(oil.id))
                .append($('<td>').text(oil.name))
                .append($('<td>').text(oil.iodine)));
            });

        }
    });

    $('#btn-choose-oil').on('click',function(){
        if($('#co_choise_oil_table tbody tr.choise-selected').length){
            choosed_oils.add($('#co_choise_oil_table tbody tr.choise-selected').data('oil'));
        }
    });
    
    
});
