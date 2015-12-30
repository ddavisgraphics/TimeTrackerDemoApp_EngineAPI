$(function() {
    var startTime;
    var endTime;

    // Customers Stuff
    getCustomersAjax();
    $('.customerSelect').change(function(){
        var value = $(this).val();
        // reveal the customer specific projects
        if(value == "null" || !isInt(value)){
            $('.projectContainer').addClass('hidden');
        } else {
            $('.projectContainer').removeClass('hidden');
            getCustomerProjects(value);
            // bind it the customer to hidden form
            $('.customerID').val(value);
        }
    });

    $('.projectSelect').change(function(){
        var  value = $(this).val();
        if(value != "null" && isInt(value)){
            $('.projectID').val(value);
            $('.projectTimer').removeClass('hidden');
        }
    });

    $('.startTimer').click(function(){
        var now = getUnixTS();

        $('.startTime').val(now);
        startTime = now;

        alert('Time has started at - ' + humanReadableTime(now));
    });

    $('.endTimer').click(function(){
        var now = getUnixTS();

        $('.endTime').val(now);
        endTime = now;

        alert('Time has ended at - ' + humanReadableTime(now));

        $('.totalHours').val(totalHours(startTime, endTime));
        $('.formInfo').removeClass('hidden');
    });


});

function totalHours(start, end){
    // format times
    var  startHours = getHours(start);
    var  endHours   = getHours(end);
    var  smin       = getMins(start);
    var  emin       = getMins(end);

    // format hrs
    var hours = endHours - startHours;

    // format mins
    var minutes        = emin - smin;
    var decimalMinutes = minutes / 60;
    decimalMinutes     = parseFloat(Math.round(decimalMinutes * 100) / 100).toFixed(2);

    // parse a float from the strings and do some math
    return (parseFloat(hours) + parseFloat(decimalMinutes)).toFixed(2);
}

function getHours(value){
     // create js workable date
     var jsTime = value*1000;
     var time = new Date(jsTime);
     return time.getHours();
}


function getMins(value){
     // create js workable date
     var jsTime = value*1000;
     var time = new Date(jsTime);
     return time.getMinutes();
}

function humanReadableTime(date){
    if(date == "0" || date == "" || date == null){
        return null;
    } else {
        // create js workable date
        var jsTime = date*1000;
        var time = new Date(jsTime);

        // format hrs and mins ampm
        var hours = time.getHours();
        var minutes  = time.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';

        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;

        // return time
        return hours + ':' + minutes + ampm;
    }
}

function isInt(value){
    var regex = /^[-+]?\d+$/;
    return regex.test(value);
}

function getUnixTS(){
    return Math.floor(Date.now() / 1000);
}


function getCustomersAjax(){
    $.ajax({
        dataType:'json',
        url:'/ajax/customers/',
        data: '',
        success:function() {
            console.log('success');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus + ': ' + errorThrown);
        },
        complete: function(data){
            var jsonData = data.responseJSON;
            var option = '<option value="null"> Please select a Customer </option>';

            for (var i = 0; i < jsonData.length; i++) {
                var customer    = jsonData[i];
                var id          = customer.ID;
                var companyName = customer.companyName;

                option += '<option value="'+id+'">'+companyName+'</option>';
            }

            $('.customerSelect').html(option);
        },
    });
}

function getCustomerProjects(customerID){
    $.ajax({
        dataType:'json',
        url:'/ajax/customerProjects/',
        data: { id: customerID},
        success:function() {
            console.log('success');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus + ': ' + errorThrown);
        },
        complete: function(data){
            var jsonData = data.responseJSON;
            option = '<option value="null"> Please select a Project </option>';

            for (var i = 0; i < jsonData.length; i++) {
                var project    = jsonData[i];
                var id          = project.projectID;
                var projectName = project.projectName;

                option += '<option value="'+id+'">'+projectName+'</option>';
            }

            $('.projectSelect').html(option);
        },
    });
}