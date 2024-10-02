$('#forms').hide("linear");
$('#form-closed').hide("linear");
$('#created-form').hide("linear");
$('#edit-form').hide("linear");

function MostrarFormBlog(num){
    $('#forms').hide("linear");
    $('#form-closed').hide("linear");
    $('#created-form').hide("linear");
    $('#edit-form').hide("linear");
    $('#form-open').hide("linear");
    $('#form-closed').hide("linear");
    if(num == 2){
        $('#created-form').show("linear");
        $('#form-closed').show("linear");
    }else if(num == 1){
        $('#form-open').show("linear");
    }else if(num == 3){
        $('#edit-form').show("linear");
        $('#form-closed').show("linear");
    }
}