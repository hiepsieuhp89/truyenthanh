var a = document.getElementsByClassName('container-refresh');
for (var i = 0; i < a.length; i++) {
    a[i].addEventListener('click', function() {
        location.reload(true);
    });
}
$(document).on('click', '.validate-schedule-submit', function(){
    var fd = new FormData(document.getElementById($(this).attr('target')));
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
    });
    // url: 'http://localhost:8000/truyenthanh/public/program/deactivate',
    if($(this).attr('target') == 'merge-program') 
        $.ajax({
            type: 'post',
            url: 'program/deactivate',
            processData: false,
            contentType: false,
            data : fd,
            success: function(res) {
                if(res.status){
                    $('input.status[type="checkbox"]').prop( "checked", true );

                    $('input.status[type="hidden"]').val("on");

                    $('.btn.btn-primary[type="submit"]').click();
                }
                else  
                    $.admin.toastr.error('Lỗi không xác định, hãy thử lại', '', {positionClass:"toast-top-right"});;
            }
        });

    else{

        let time = $(this).attr('data-time');
        $('input[name="time"]').val(time);
        
        $('input.status[type="checkbox"]').prop( "checked", true );

        $('input.status[type="hidden"]').val("on");

        $('.btn.btn-primary[type="submit"]').click();
    }
    // $('input.status[type="checkbox"]').prop( "checked", true ), $('input.status[type="hidden"]').val("on")
});
var e;
e = setInterval(function() {
    if (window.location.href.indexOf("/devicedata") > -1 || window.location.href.indexOf("/devices") > -1) {
        $.ajax({
            type: 'get',
            url: '/devices-status',
            success: function(res) {

                $.each(res.Data, function(i, n) {

                    let device_row = $('[data-content="' + n.DeviceID + '"]').parent().parent();

                    device_row.find('.column-device-name span.label-danger').removeClass('label-danger').addClass('label-success');
                    //device_row.find('.column-turn_off_time').html('');

                    if (n.DeviceData.Data.AudioOutState != 0)
                        device_row.find('.column-device-name').find('i').removeClass('hidden');
                    else
                        device_row.find('.column-device-name').find('i').addClass('hidden');
                });
                let deviceCodes = $.map(res.Data, function(n) {
                    return n.DeviceID;
                });
                $.map($('tbody tr'), function(n) {
                    if (jQuery.inArray(($(n).find('.column-deviceCode a')).attr('data-content'), deviceCodes) == -1) {
                        $(n).find('.column-device-name').find('span').removeClass('label-success').addClass('label-danger');
                        $(n).find('.column-device-name').find('i').addClass('hidden');
                        $(n).find('.column-status').html('<b class="text-danger">Không hoạt động</b>');
                    }
                });
            }
        });
    }
}, 5000);