var a = document.getElementsByClassName('container-refresh');
for (var i = 0; i < a.length; i++) {
    a[i].addEventListener('click', function() {
        location.reload(true);
    });
}
var e;
e = setInterval(function() {
    console.log(window.location.href.indexOf("devicedata"));
    console.log(window.location.href.indexOf("devices"));
    if (window.location.href.indexOf("devicedata") > -1 || window.location.href.indexOf("devices") > -1) {
        $.ajax({
            type: 'get',
            url: 'https://truyenthanh.org.vn/admin/devices-status',
            success: function(res) {
                console.log(res);
                $.each(res.Data, function(i, n) {

                    let device_row = $('[data-content="' + n.DeviceID + '"]').parent().parent();

                    if (n.DeviceData.Data.AudioOutState != 0)
                        device_row.find('.column-device-name').find('i').removeClass('hidden');
                    else
                        device_row.find('.column-device-name').find('i').addClass('hidden');

                    device_row.find('.column-status').html('<b class="text-success">Đang hoạt động</b>');
                    device_row.find('.column-turn_off_time').html('');
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