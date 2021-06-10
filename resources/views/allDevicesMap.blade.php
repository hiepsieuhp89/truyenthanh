<style type="text/css">
      /* Set the size of the div element that contains the map */
      #map {
        height: 600px;
        /* The height is 400 pixels */
        width: 100%;
        /* The width is the width of the web page */
      }
</style>

{{-- <div class="form-group">

    <div class="field">

        <div class="row">
            <div class="col-md-3">
                <input id="lat" name="lat" class="form-control" value="" placeholder="latitude" />
            </div>
            <div class="col-md-3">
                <input id="lng" name="lng" class="form-control" value="" placeholder="longitude" />
            </div>

            <div class="col-md-12 col-md-offset-0">
                <div class="input-group">
                    <input type="text" class="form-control" id="search" placeholder="Vị trí">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-info btn-flat"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </div>

        </div>

        <br>

        <div id="map"></div>
    </div>
</div> --}}
<div id="map"></div>
<script>
    function initMap() {
    var la = {{ $area->lat }};
    var lo = {{ $area->lon }};
    var map = new google.maps.Map(document.getElementById('map'), {
        center: new google.maps.LatLng(la, lo),
        zoom: {{ env('GOOGLE_MAP_ZOOM') }},
        mapTypeId: 'roadmap'
    });
    const image = '{{ env("APP_URL")."/images/icon_map.png" }}';
    const image_off = '{{ env("APP_URL")."/images/icon_map_off.png" }}';

    var customImg = {
        1: { // Trạng thái bật
            image: image
        },
        0: { // Trạng thái tắt
            image: image_off
        }
    };

    var infoWindow = new google.maps.InfoWindow;

        // Change this depending on the name of your PHP or XML file
        downloadUrl('{{env('APP_URL').'/admin/xml/map'}}', function(data) {

            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');

            Array.prototype.forEach.call(markers, function(markerElem) {
                var id = markerElem.getAttribute('id');
                var name = markerElem.getAttribute('name');
                var address = markerElem.getAttribute('address');
                var type = markerElem.getAttribute('type');
                var point = new google.maps.LatLng(

                    parseFloat(markerElem.getAttribute('lat')),
                    parseFloat(markerElem.getAttribute('lng')));

                var infowincontent = document.createElement('div');
                var strong = document.createElement('strong');
                strong.textContent = name
                infowincontent.appendChild(strong);
                infowincontent.appendChild(document.createElement('br'));

                var text = document.createElement('text');
                text.textContent = address;

                infowincontent.appendChild(text);

                var icon = customImg[type] || {};

                var marker = new google.maps.Marker({
                    map: map,
                    position: point,
                    icon : icon.image
                    // label: icon.label
                });
                marker.addListener('click', function() {
                    infoWindow.setContent(infowincontent);
                    infoWindow.open(map, marker);
                });
            });
        });
    }



    function downloadUrl(url, callback) {
    var request = window.ActiveXObject ?
        new ActiveXObject('Microsoft.XMLHTTP') :
        new XMLHttpRequest;

    request.onreadystatechange = function() {
        if (request.readyState == 4) {
        request.onreadystatechange = doNothing;
        callback(request, request.status);
        }
    };

    request.open('GET', url, true);
    request.send(null);
    }
    function doNothing() {}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2nGDxgbOEzdU-cS6FHGYUtwGKUAx0B5s&callback=initMap">
</script>

{{-- <div id="map"></div> --}}