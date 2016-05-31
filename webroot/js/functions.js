var STORE_URL = 'https://www.commanderstore.com/';
//var SITE_URL = 'http://commander_registration.local/';
var SITE_URL = 'http://membership.commanderstore.com/';

$(document).ready(function()
{
	$('#registration-form').validate({
		rules : {
            password : {
                minlength : 5,
                required: true
            },
            password_confirmation : {
                minlength : 5,
                equalTo : "#password"
            },
            email : {
                required: true,
      			email: true
            },
            first_name : {
                required: true,
      			minlength : 2
            },
            last_name : {
                required: true,
      			minlength : 2
            },
            terms : {
                required: true
            }
        }
	});

    $(".main-navigation li").on('mouseover', function(){
        $(this).children( ".sub-navigation-menu" ).css( "display", "block" );
    });

    $(".main-navigation li").on('mouseout', function(){
        $(this).children( ".sub-navigation-menu" ).css( "display", "none" );
    });

    $("#verify-payment").on('click', function(){

        $(".verify-btn").hide();
        $(".verify-payment-message").show();
        $(".verify-text-fail").hide();

        $.ajax({
            url: "/registration/verifySnapScan",        // url to send data to
            type: "POST",                     // parse data as type
            data: '',                    // data object to parse
            dataType:"json",                 // return type
            success: function(response){
                if(response['member_status'] == 'active')
                    document.location = STORE_URL+'/account/login';
                else
                {
                    $(".verify-btn").show();
                    $(".verify-payment-message").hide();
                    $(".verify-text-fail").show();
                }
            },   // callback function
            error: function(){

            },
            async: true
        });
    })

    $( ".postnet-btn" ).on('click', function() {

        if($('.postnet-btn').html() == 'Back to Store')
        {
            $(".postnet-btn").hide();
            $(".postnet-loading").hide();
            $(".postnet-results").hide();            
            document.location = STORE_URL;
        }
            

        $(".postnet-btn").hide();
        $(".postnet-loading").show();
        
        var _params = {
            suburb : $('#suburb-search').val()
        }

        $.ajax({
            url: "/postnet/findPostNet",        // url to send data to
            type: "POST",                     // parse data as type
            data: _params,                    // data object to parse
            dataType:"json",                 // return type
            success: function(response){
                if(response['status'] != false)
                {
                    $(".postnet-loading").hide();
                    $('.postnet-results').html(response['data']);
                }
                else
                {
                    $(".postnet-loading").hide();
                    $(".postnet-btn").show();
                    $('.postnet-results').html(response['data']);
                }
            },   // callback function
            error: function(){

            },
            async: true
        });

    });
});

function submitFormData(formName, renew)
{
    if($('#registration-form').valid())
    {
        var _params = {
            first_name : $('#first-name').val(),
            email : $('#email').val(),
            last_name : $('#last-name').val(),
            password : $('#password').val(),
            renew : renew
        }

        $.ajax({
            url: "/registration/saveUserDetails",        // url to send data to
            type: "POST",                     // parse data as type
            data: _params,                    // data object to parse
            dataType:"json",                 // return type
            success: function(response){
                // submit cashlog form

                //console.log(response);

                if(response['status'])
                {
                    if(response['redirect'])
                        document.location = SITE_URL+'/registration/organic';
                    else
                    {
                        if(response['already_member'])
                            document.location = STORE_URL+'/account/login';
                        else
                            document.forms[formName+'PaymentForm'].submit();
                    }
                }
                else
                    alert('Something seems to have gone wrong, please try again later.');

            },   // callback function
            error: function(){

            },
            async: true
        });       
    }
}

function submitFormDataSnapScan(renew)
{
    if($('#registration-form').valid())
    {
        var _params = {
            first_name : $('#first-name').val(),
            email : $('#email').val(),
            last_name : $('#last-name').val(),
            password : $('#password').val(),
            renew : renew
        }

        $.ajax({
            url: "/registration/saveUserDetails",        // url to send data to
            type: "POST",                     // parse data as type
            data: _params,                    // data object to parse
            dataType:"json",                 // return type
            success: function(response){
                // submit cashlog form

                console.log(response);

                if(response['status'])
                {
                    if(response['redirect'])
                        document.location = SITE_URL+'/registration/organic';
                    else
                    {
                        if(response['already_member'])
                            document.location = STORE_URL+'/account/login';
                        else
                            document.location = SITE_URL+'/registration/snapscan';
                    }
                }
                else
                    alert('Something seems to have gone wrong, please try again later.');
            },   // callback function
            error: function(){

            },
            async: true
        });       
    }
}

function updateUserProfile(obj)
{

    $(".postnet-results").hide();
    $(".postnet-loading").show();

    var _params = {
        notes : $(obj).children( ".hiddenContent" ).html()
    }

    $.ajax({
        url: "/postnet/updateUser",        // url to send data to
        type: "POST",                     // parse data as type
        data: _params,                    // data object to parse
        dataType:"json",                 // return type
        success: function(response){
            
            if(response['updated'] != 'false')
            {
                $(".postnet-btn").html("Back to Store");
                $(".postnet-btn").show();
                $(".postnet-loading").hide();
                $('.postnet-results').html('<p>Thank you, your customer profile has been updated. Please click on the button above to return to the Commander HQ store.</p>');
                $('.postnet-results').show();
            }
            else
            {
                $(".postnet-btn").html("Find Postnet Branch");
                $(".postnet-btn").show();
                $(".postnet-loading").hide();
                $('.postnet-results').html('<p>There was an error updating your details, if the problem persists please contact our support team.</p>');
                $('.postnet-results').show();
            }

        },   // callback function
        error: function(){
            //$('.postnet-results').html('<p>Thank you, your customer profile has been updated. Please click on the link below to be redirected to the <a href="'+STORE_URL+'">CommanderHQ Store</a>.</p>');
        },
        async: true
    });
}