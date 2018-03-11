var map;
$(document).ready(function(){
    prettyPrint();
    map = new GMaps({
        div: '#map',
        lat: 49.222297,
        lng: 28.423343,
        zoomControl : false,
        panControl :  false,
        mapTypeControl: false,
        streetViewControl: false,
        zoom: 19
    });

    map.addMarker({
       lat: 49.222297,
       lng: 28.423343,
       title: 'prostosait.com.ua',
       infoWindow: {
           content: '<p>Адрес: Винница, улица 600-летия, 66-A<br/> Tel: (063)-857-63-92<br/> E-mail: admin@prostosait.com.ua<br/> Icq: 560-809-361<br/> Skype: shapovala</p>'
      }
   });

   // map.drawOverlay({
    //    lat: map.getCenter().lat(),
    //    lng: map.getCenter().lng(),
     //   layer: 'overlayLayer',
     //   content: '<div class="overlay"><div class="overlay_arrow above"></div></div>',
     //   verticalAlign: 'top',
     //   horizontalAlign: 'center'
   // });
});


