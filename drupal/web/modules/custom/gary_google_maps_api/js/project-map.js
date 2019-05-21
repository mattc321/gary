/**
 * @file
 * Get fieldnames and append to select
 */

       var customLabel = {
         restaurant: {
           label: 'R'
         },
         bar: {
           label: 'B'
         }
       };

       var xmlstuff = drupalSettings.gary_google_maps_api.project_xml_list;
       var key = drupalSettings.gary_google_maps_api.key;
       var center = drupalSettings.gary_google_maps_api.center;

       var script = document.createElement('script');
       script.type = 'text/javascript';
       script.src = 'https://maps.googleapis.com/maps/api/js?key='+key+'&callback=initMap';
       document.body.appendChild(script);


         function initMap() {
           var map = new google.maps.Map(document.getElementById('map'), {
             center: new google.maps.LatLng(center.lat, center.lng),
             zoom: Number(center.zoom)
           });

           var infoWindow = new google.maps.InfoWindow;

            var parser = new DOMParser();
            var xml = parser.parseFromString(xmlstuff,"text/xml");

             var markers = xml.documentElement.getElementsByTagName('marker');
             Array.prototype.forEach.call(markers, function(markerElem) {
               var id = markerElem.getAttribute('id');
               var name = markerElem.getAttribute('name');
               var address = markerElem.getAttribute('address');
               var type = markerElem.getAttribute('type');
               var status = markerElem.getAttribute('status');
               var point = new google.maps.LatLng(
                   parseFloat(markerElem.getAttribute('lat')),
                   parseFloat(markerElem.getAttribute('lng')));

               var infowincontent = document.createElement('div');
               infowincontent.className = 'project-map-info-popup';
               var strong = document.createElement('strong');
               strong.textContent = address
               infowincontent.appendChild(strong);
               infowincontent.appendChild(document.createElement('br'));

               var text = document.createElement('text');
               text.textContent = name
               infowincontent.appendChild(text);
               infowincontent.appendChild(document.createElement('br'));

               var statustext = document.createElement('text');
               statustext.textContent = status
               infowincontent.appendChild(statustext);


               var icon = customLabel[type] || {};
               var marker = new google.maps.Marker({
                 map: map,
                 position: point,
                 label: icon.label
               });
               marker.addListener('click', function() {
                 infoWindow.setContent(infowincontent);
                 infoWindow.open(map, marker);
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
