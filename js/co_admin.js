jQuery('document').ready(function($){
    $('#do-ajax').on('click',function(){
        $.post(ajaxurl,{
            action: 'restruct_tabs',
            nonce: co_admin_ajax.nonce
        },false,'json')
        .done(function(result){
            console.log(result);
        })
        .fail(function(responce){
            console.log(responce);
        })
    });
});
