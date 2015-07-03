$(document).ready(function() {

$('#formSignup').submit(function(event) {
    event.preventDefault();

    $.ajax({
        url: baseUrl+"login/doSignup",
        type: 'POST',
        beforeSend: function( xhr ) {
            $('a#btn-message').trigger('click');
            $('#myModal').modal();
        },
        data: $(this).serialize(),
        success: function(data) {
            
            

            var result = $.parseJSON(data);            
            if(result.statusEmail == 'exist' || result.statusUsername == 'exist' || result.statusTwitter == 'exist'){
                $('#signup-password,#signup-re_password').val('');
                if(result.statusEmail == 'exist'){
                    $('#emailGroup').before('<span class="florakb-error">'+result.msgEmail+'</span>');
                    $('#signup-email').css("border","1px dotted #FF0000");
                } 
                if(result.statusUsername == 'exist'){
                    $('#usernameGroup').before('<span class="florakb-error">'+result.msgUsername+'</span>');
                    $('#signup-username').css("border","1px dotted #FF0000");
                }
                if(result.statusTwitter == 'exist'){
                    $('#twitterGroup').before('<span class="florakb-error">'+result.msgTwitter+'</span>');
                    $('#signup-twitter').css("border","1px dotted #FF0000");
                }
            }
            else if(result.result == 'error'){
                var html = 'Maaf, ada yang tidak beres';
                $('.message-body').html(html);
                console.log('something went wrong');
            }
            else{
                // alert('User created, do login for enter the site.');
                var html = 'Akun telah dibuat, periksa email Anda untuk memverifikasi akun.';

                // $('a#btn-message').trigger('click');
                $('.messageRegister').html(html);
                setTimeout(function() {
                  window.location.href = basedomain;
                }, 2000);
                // alert('Account created, check your email to verified your account.');
                //location.reload();
                //window.location.href=basedomain;
            } 
        },
        error: function(xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
          alert(err.Message);
        }
    });
    $('#signup-name').keyup(function(){
       $('#nameGroup > span').remove();
    });
    $('#signup-email').keyup(function(){
       $('#emailGroup > span').remove();
    });
    $('#signup-username').keyup(function(){
       $('#usernameGroup > span').remove();
    });
    $('#signup-twitter').keyup(function(){
       $('#twitterGroup > span').remove();
    });
});

$('#formLogin').submit(function(event){
    event.preventDefault();
    
    $.ajax({
        url: baseUrl+"login/doLogin",
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            var returnedData = JSON.parse(response);
            if(returnedData == 'success'){
                // location.reload();
                window.location.href=basedomain;
            }
            else{
                alert('Anda telah memasukkan username atau password yang tidak benar');
                $('#login-password').val('');
            }
        }
    });
});

});