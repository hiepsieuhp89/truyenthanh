var a = document.getElementsByClassName('container-refresh');
for (var i = 0; i < a.length; i++) {
    a[i].addEventListener('click', function() {
        location.reload(true);
    });
}
$.ajax({
    type: 'get',
    crossDomain: true,
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    url: 'https://truyenthanh.org.vn/admin/devices-status',
    success: function(data) {
        let infor = data;
        console.log(infor);
    }
});
// setInterval(function() {
//     $.ajax({
//         type: 'get',
//         crossDomain: true,
//         headers: {
//             'Content-Type': 'application/x-www-form-urlencoded'
//         },
//         url: 'http://103.130.213.161:906/eyJEYXRhVHlwZSI6MjAsIkRhdGEiOiJHRVRfQUxMX0RFVklDRV9TVEFUVVMifQ==',
//         success: function(data) {
//             let infor = data.replace(':"{', ":{").replace(':"[{', ":[{").replace('"}"', "}").replace('"{"', "{").replace(']"}', "]}");
//             console.log(infor.DataType);
//         }
//     });
// }, 5000);