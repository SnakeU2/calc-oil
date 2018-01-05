jQuery('document').ready(function($){
    $('.oil-edit').each(function(){
        $(this).on('click',function(){
            $.post(ajaxurl,{
                'action':'get_oil',
                'nonce':co_admin_ajax.nonce,
                'oil_id':$(this).attr('btn-data')
            },false,'json')
            .done(function(result){
                if(result.length){
                    order = {'poly':[],'mono':[],'sat':[]};
                    //result.each(function(acid){
                        
                    //})
                    $('#co_oils_modal').modal('toggle');
                }
            });
        })
    });
});
