function completeNamecheck(component, reqseq, encode) {
    var $ = jQuery;
    $('input[name="namecheck_component"]', '#wpmem_reg form').val(component);
    $('input[name="namecheck_reqseq"]', '#wpmem_reg form').val(reqseq);
    $('input[name="namecheck_encode"]', '#wpmem_reg form').val(encode);
    $('ul.namecheck-list', '#wpmem_reg').addClass('namecheck-success');
}
