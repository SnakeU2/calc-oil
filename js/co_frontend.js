'use strict';

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
var choosed_oils = {};


//jQuery.noConflict();
jQuery('document').ready(function($){
    
    choosed_oils = {
        list:[],
        calc:{
            'percents':0, //percent oils
            'iodine_stable':0, //iodine stability
            'iodine_count':0,
            'iodine_liquid':{'nodry':0,'semidry':0,'dry':0}
        },
        'recalc':function(){
            //clear calc
            $.each(this.calc,function(ind,el){
                if(typeof(el)=='object') $.each(el,function(elInd,elEl){el[elInd] = 0});
                else choosed_oils.calc[ind]=0;
            });
            $.each(this.list,function(ind,oil){
                //calc percent oils
                choosed_oils.calc.percents+=oil.count;
                //calc iodine_stable
                var iodine = parseInt(oil.iodine); 
                choosed_oils.calc.iodine_stable += (iodine > 0)? parseFloat( (iodine / 100 * oil.count).toFixed(2)):0;
                choosed_oils.calc.iodine_count+=(iodine > 0)?1:0;
                //calc iodine_liquid
                if(iodine <= 100)  choosed_oils.calc.iodine_liquid.nodry += oil.count;
                else if( iodine > 100 && iodine <= 140) choosed_oils.calc.iodine_liquid.semidry += oil.count;
                else if( iodine > 140 ) choosed_oils.calc.iodine_liquid.dry += oil.count;
                //calc oils
                                
            });
            $(this).trigger('oils:recalc',this);
        },
        'rebuild':function(){        
            $.each(calc_oils.groups,function(ind,val){ $('#co-caltab-gr-'+val).empty();});
            if(this.list.length){
                $.each(this.list,function(id,oil){
                    $('#co-caltab-gr-'+oil.o_group).append($('<tr>').attr('data-oil',oil.id)
                        .append($('<td>').text(oil.name))
                        .append($('<td>')
                            .append($('<input>').attr({'class':'form-control form-control-sm','type':'number'}).val(oil.count)
                                .on('change',function(){
                                    calc_oils.get_oil($(this).closest('tr').data('oil')).count=parseInt($(this).val());
                                    choosed_oils.recalc();                               
                                })
                            )
                        )
                        .append($('<td>').on('click',function(){
                                choosed_oils.remove($(this).parent().data('oil'));                            
                            })
                            .append($('<div>').attr({'class':'co-calc-oil-tab-remove'}).text("X"))
                        )
                    );

                });
            }
            this.recalc();
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
                if(exists != -1) {
                    this.list[exists].count=0;
                    this.list.splice(exists,1);
                }
                this.rebuild();            
            }
        }    
    }

       
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

    $('.btn-choose-oil').each(function(){
        $(this).on('click',function(){
            if($('#co_choise_oil_table tbody tr.choise-selected').length){
                choosed_oils.add($('#co_choise_oil_table tbody tr.choise-selected').data('oil'));
            }
        });
    });

    $(choosed_oils).on('oils:recalc',function(){
        var calc = choosed_oils.calc;
        //update info
        var text = "";
        var addClass = "";
        var tooltip="";
        var tooltipClass = "d-none";

        //percents        
        if(calc.percents < 100){
            addClass = 'pink';
            text = ""+calc.percents+"% - Нужно добавить масел!";                                                
        }
        else if (calc.percents == 100){
            addClass = 'success';
            text = ""+calc.percents+"% - Отлично!";
        }
        else {
            addClass = 'orange';
            text = ""+calc.percents+"% - Нужно удалить масла";            
        }
        $('#info-count div.status').removeClass('pink success orange').text("");
        $('#info-count div.status').addClass(addClass).text(text);
        //iodine_stable
        addClass = "";
        text = "";
        $('#info-acid-potencial div.status').removeClass('pink success orange');
        if(calc.iodine_stable > 0 && calc.iodine_stable < 120){
            addClass = 'success';
            text = ""+calc.iodine_stable.toFixed(2)+" - Достаточно стабильная смесь";
        }
        else if(calc.iodine_stable > 120 && calc.iodine_stable < 150){
            addClass = 'orange';
            text = ""+calc.iodine_stable.toFixed(2)+" - Средний окислительный потенциал";            
        }
        else if(calc.iodine_stable > 0){
            addClass = 'pink';
            text = ""+calc.iodine_stable.toFixed(2)+" - Крайне высокий окислительный потенциал";            
        }
        $('#info-acid-potencial div.status').addClass(addClass).text(text);
        
        if(choosed_oils.list.length){
            tooltip = (choosed_oils.list.length == calc.iodine_count)?"Расчет верный."+String.fromCharCode(0xA)+"Йодное число указано для всех масел":"Расчет приблизительный,"+String.fromCharCode(0xA)+"не для всех масел известно йодное число.";
            tooltipClass = (choosed_oils.list.length == calc.iodine_count)?"t-blue":"t-pink";
        }
        $('#info-acid-potencial h6 span').attr('tooltip',tooltip);
        $('#info-acid-potencial h6 span').removeClass('t-pink t-success t-orange t-red t-blue d-none').addClass(tooltipClass);
        //iodine_liquid
        addClass = (calc.iodine_liquid.nodry == 50 && calc.iodine_liquid.semidry == 35 && calc.iodine_liquid.dry == 15)?'success':'pink'; 
        tooltipClass = (calc.iodine_liquid.nodry == 50 && calc.iodine_liquid.semidry == 35 && calc.iodine_liquid.dry == 15)?'t-blue':'t-pink'; 
        $('#info-liquid div.status').removeClass('pink success orange').addClass(addClass).html('Невысыхающие: '+calc.iodine_liquid.nodry+'<br><br>Полувысыхающие: '+calc.iodine_liquid.semidry+'<br><br>Высыхающие: '+calc.iodine_liquid.dry);
        $('#info-liquid h6 span').removeClass('t-pink t-success t-orange t-red t-blue d-none').addClass(tooltipClass);
        
    });
    
});
