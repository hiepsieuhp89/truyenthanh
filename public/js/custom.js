var a = document.getElementsByClassName('container-refresh');
for (var i = 0; i < a.length; i++) {
    a[i].addEventListener('click',function(){
    	location.reload(true);
    });
}