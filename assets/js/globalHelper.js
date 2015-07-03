$().ready(function() {
    /**
    * checking name exist or not in database
    */
    $.validator.addMethod("checkNameExist",function(value,element){
        var data_input = $('#name').val();
        var data = { 'name' : data_input };
        var check = $.ajax({
                        url: baseUrl+"onebyone/check_Name",
                        type: "POST",
                        async: false,
                        data: data,
                        success: function(output) {}
                    }).responseText;
        if(check == false){
            return false;
        }else{
            return true;
        }
    }," Nama sudah terdaftar");
    
    /**
    * checking email exist or not in database
    */
    $.validator.addMethod("checkEmailExist",function(value,element){
        var data_input = $('#email').val();
        var data = { 'email' : data_input };
        var check = $.ajax({
                        url: baseUrl+"onebyone/check_Email",
                        type: "POST",
                        async: false,
                        data: data,
                        success: function(output) {}
                    }).responseText;
        if(check == false){
            return false;
        }else{
            return true;
        }
    }," Email sudah terdaftar");
    
    /**
    * checking email exist or not in database
    */
    $.validator.addMethod("checkEmailNotExist",function(value,element){
        var data_input = $('#email').val();
        var data = { 'email' : data_input };
        var check = $.ajax({
                        url: baseUrl+"onebyone/check_Email",
                        type: "POST",
                        async: false,
                        data: data,
                        success: function(output) {}
                    }).responseText;
        if(check){
            return false;
        }else{
            return true;
        }
    }," Email tidak ditemukan");
    
    /**
    * checking twitter exist or not in database
    */
    $.validator.addMethod("checkTwitterExist",function(value,element){
        var data_input = $('#twitter').val();
        var data = { 'twitter' : data_input };
        var check = $.ajax({
                        url: baseUrl+"onebyone/check_Twitter",
                        type: "POST",
                        async: false,
                        data: data,
                        success: function(output) {}
                    }).responseText;
        return check;
    }," Nama twitter sudah terdaftar");
    
    /**
    * checking username exist or not in database
    */
    $.validator.addMethod("checkUsernameExist",function(value,element){
        var data_input = $('#username').val();
        var data = { 'username' : data_input };
        var check = $.ajax({
                        url: baseUrl+"user/checkUsername",
                        type: "POST",
                        async: false,
                        data: data,
                        success: function(output) {}
                    }).responseText;
        return check;
    }," Nama pengguna sudah terdaftar");
    
    /**
    * checking alphanumeric
    */
    $.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[A-Za-z\d_]+$/.test(value);
    }, "Hanya dapat berisi huruf, angka, atau garis bawah.");
    
    /**
    * checking password
    */
    $.validator.addMethod("checkPassword",function(value,element){
        var data_input = $('#password').val();
        var data = { 'password' : data_input };
        var check = $.ajax({
                        url: baseUrl+"user/checkPassword",
                        type: "POST",
                        async: false,
                        data: data,
                        success: function(output) {}
                    }).responseText;
        return check;
    }," Sandi tidak valid atau sandi salah");
    
    /**
    * checking decimal input
    */
    $.validator.addMethod('Decimal', function(value, element) {
        return this.optional(element) || /^\d+(\.\d{0,3})?$/.test(value); 
    }, "Harap masukkan angka yang valid, hanya titik yang diijinkan");
    
    $('#nextGenus').on('click', function(e) {
        $('#inputGenus').show();
        $('#nextGenus').hide();
    });
    
    $('#nextSpecies').on('click', function(e) {
        $('#inputSpecies').show();
        $('#nextSpecies').hide();
    });
    
    $('#resetTaxon').on('click', function(e) {
        $('#inputTaxon').hide();
        $('#autoCompleteTaxon').show();
        clear_input('#autoCompleteTaxon');
        clear_input('#inputTaxon');
    });
    
    // taxon auto complete
    /*$('#autoTaxon').on('keyup', function(e) {
        var value = $(this).val();
        if(value.length % 5 == 0){
        //if(value.length == 3){
            $(this).autocomplete({
                source: JSON.parse($.ajax({
                            url: baseUrl+"onebyone/autoTaxon",
                            type: "POST",
                            async: false,
                            data: {'autoTaxon' : $(this).val()},
                            success: function(output) {}
                        }).responseText),
                select: function (event, ui) {
                    $(this).val(ui.item.label);
                    $('#taxonID').val(ui.item.id);
                    return false;
                },
                change: function(event, ui) {
                    if(this.value){
                        if (!ui.item) {
                            this.value = '';
                            alert('Please select one of the options');
                        }
                    }
                }
            });
        }
    });*/
    //end auto complete
    
    
    // auto complete family
    $('#autoFamily').on('keyup', function(e) {
        autoCompleteTaxon('#autoFamily', 'autoFamily');
    });
    // end auto complete family
    
    // auto complete genus
    $('#autoGenus').on('keyup', function(e) {
        autoCompleteTaxon('#autoGenus', 'autoGenus');
    });
    // end auto complete genus
    
    // auto complete species
    $('#autoSpecies').on('keyup', function(e) {
        autoCompleteTaxon('#autoSpecies', 'autoSpecies');
    });
    // end auto complete species               
                
});


/**
* auto complete taxon
* @param keyupID = id of input autoTaxon
* @param funcName = name of php function to get data auto complete
* */
function autoCompleteTaxon(keyupID,funcName){
    var value = $(keyupID).val();
    var dataTaxon = {'family' : $('#autoFamily').val(),'genus' : $('#autoGenus').val(),'species' : $('#autoSpecies').val()};
    //console.log(dataTaxon);        
    if(value.length % 2 == 0){
    //if(value.length == 3){
        $(keyupID).autocomplete({
            source: JSON.parse($.ajax({
                        url: baseUrl+"onebyone/"+funcName,
                        type: "POST",
                        async: false,
                        data: dataTaxon,
                        success: function(output) {}
                    }).responseText),
            response: function(event, ui) {
                // ui.content is the array that's about to be sent to the response callback.
                if (ui.content.length === 0) {
                    alert("Hasil tidak ditemukan");
                }
            },
            select: function (event, ui) {
                clear_input('#inputTaxon');
                $(keyupID).val(ui.item.label);
                $('#kewid').val(ui.item.id);
                return false;
            },
            change: function(event, ui) {
                if(this.value){
                    if (!ui.item) {
                        this.value = '';
                        alert('Harap pilih salah satu dari opsi atau tinggalkan kosong.');
                    }
                }
            }
        });
    }
    
}

/**
* checking extention from input file
* @param inputID = id of input type file
* @param exts = array extention allowed => ['.zip', '.rar']
* */
function hasExtension(inputID, exts) {
    var fileName = document.getElementById(inputID).value;
    return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
}

/*
clear input by id
param groupID = id of a group that want to reset
*/
function clear_input(groupID){
    $(groupID).find('input').each(function(){
        if ((this.type === 'radio') || (this.type === 'checkbox'))
        {
            this.checked = false;
        }
        else
        {
            this.value = '';
        }
    });
}

/*
sending a form in ajax way
param form = form
param formID = id of a form
param modalID = popup id to open and close the form
param msgFormat = title of the form, to be include in message (failed or success)
*/
function do_ajax(form, formID, modalID, msgFormat){
    var first_error = '<div class="messages erroren"><a href="#" class="closeMessage"></a><p>';
    var first_info = '<div class="messages info"><a href="#" class="closeMessage"></a><p>';
    var first_success = '<div class="messages success"><a href="#" class="closeMessage"></a><p>';
    var first_warning = '<div class="messages warning"><a href="#" class="closeMessage"></a><p>';
    var end = '</p></div>';
    var msg = ".msg";
    $(modalID + ":input").attr('readonly', true);
    $(form).ajaxSubmit(function(output, status, xhr){
        var ct = xhr.getResponseHeader("content-type") || "";
        
        $(".messages").remove();
        $(modalID).modal('hide');
        
        try {
            var data = JSON.parse(output);
            //console.log(data);
            
            if(data.status == 'success'){

                $(msg).html(first_success + 'Sukses ' + 'Memperbarui ' + msgFormat + end);
                
                if(formID == 'formLocation'){
                    $("#locnID").find('option').removeAttr('selected');
                    $("#locnID").removeClass('error');
                    $("#locnID").append('<option value="'+ data.id +'" selected>'+ data.locality +'</option>');
                }
                
                if(formID == 'formPerson'){
                    $("#personID").find('option').removeAttr('selected');
                    $("#personID").removeClass('error');
                    $("#personID").append('<option value="'+ data.id +'" selected>'+ data.name + ', ' + data.email +'</option>');
                }
                
                if(formID == 'formTaxon'){
                    //$("#taxonID").find('option').removeAttr('selected');
                    clear_input('#autoCompleteTaxon');
                    $("#autoCompleteTaxon").hide();
                    $("#inputTaxon").show();                                
                    $("#taxonID").removeClass('error');
                    
                    if(data.gen != ''){
                        if(data.fam){
                            $("#taxonID").val(data.id);
                            $("#labelTaxon").val('(' + data.fam + ') ' + data.gen + ' ' + data.sp);
                        }else{
                            $("#taxonID").val(data.id);
                            $("#labelTaxon").val(data.gen + ' ' + data.sp);
                        }
                    }else{
                        $("#taxonID").val(data.id);
                        $("#labelTaxon").val(data.morphotype);
                    }
                }
                
            }else{
                if(data.msg){
                    $(msg).html(first_error + 'Gagal ' + 'Memperbarui ' + msgFormat + ' ' + data.msg + end);
                }else{
                    $(msg).html(first_error + 'Gagal ' + 'Memperbarui ' + msgFormat + end);
                }
            }
        }catch(error){
            $(msg).html(first_error + 'Kesalahan sistem atau gagal untuk terhubung ke server' + end);
        }
        
    });
}

$('a#btn-message').click(function(){
   $('div#modal-message').fadeToggle("linear");
   $('div.modal-overlay').show();

});

$('.close,.modal-overlay').click(function(){
   $('div#modal-message').fadeOut(); 
});


