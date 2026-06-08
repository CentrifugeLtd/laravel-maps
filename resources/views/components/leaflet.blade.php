<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
      integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
      crossorigin=""/>

<style>
    #{{$mapId}} {
    @if(! isset($attributes['style']))
        height: 100vh;
    @else
        {{ $attributes['style'] }}
    @endif
    }
</style>

<div id="{{$mapId}}" @if(isset($attributes['class']))
 class='{{ $attributes["class"] }}'
@endif
></div>

<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="{{'https://unpkg.com/leaflet@' . $leafletVersion . '/dist/leaflet.js'}}"
        crossorigin=""></script>
<script>

    var mymap = L.map('{{$mapId}}').setView([{{$centerPoint['lat'] ?? $centerPoint['latitude'] ?? $centerPoint[0]}}, {{$centerPoint['long'] ?? $centerPoint['longitude'] ?? $centerPoint[1]}}]@if($zoomLevel>=0), {{$zoomLevel}}@endif);
    @if(count($bounds)==2)
        mymap.fitBounds([
            [{{$bounds[0]['lat']??$bounds[0]['latitude']??$bounds[0][0]}},{{$bounds[0]['long']??$bounds[0]['longitude']??$bounds[0][1]}}],
            [{{$bounds[1]['lat']??$bounds[1]['latitude']??$bounds[1][0]}},{{$bounds[1]['long']??$bounds[1]['longitude']??$bounds[1][1]}}]
        ]);
    @endif
    @foreach($markers as $marker)
     @if(isset($marker['icon']))
       var icon = L.icon({
        iconUrl: '{{ $marker['icon'] }}',
        iconSize: [{{$marker['iconSizeX'] ?? 32}} , {{ $marker['iconSizeY'] ?? 32 }}],
       });
     @endif
    var marker = L.marker([{{$marker['lat'] ?? $marker['latitude'] ?? $marker[0]}}, {{$marker['long'] ?? $marker['longitude'] ?? $marker[1]}}]
    @if(isset($marker['icon']))
     , {icon: icon}
    @endif
    );
    marker.addTo(mymap);
    @if(isset($marker['info']))
        popupContent='<p class="font-bold">{{ $marker['info'] }}</p>';
        @if(isset($marker['image']))
            popupContent+='<p><img src="{{ $marker['image'] }}"></p>';
        @endif
        @if(isset($marker['additional_data']))
        @php
            ray($marker['additional_data']);
        @endphp
        @foreach($marker['additional_data'] as $key=>$value)
            popupContent+='<p><strong class="font-bold">{{ $key }}</strong>: {{ $value }}</p>';
        @endforeach
        @endif
        marker.bindPopup(popupContent);
    @endif
    @endforeach

    @foreach($polygons as $polygon)
        var polygon = L.polygon([
        @foreach($polygon['points'] as $point)
            [{{$point['lat']??$point['latitude']}}, {{$point['long']??$point['longitude']}}]
            @if(!$loop->last) , @endif
        @endforeach
        ], {color:'{{ $polygon['color']??'gray' }}'}
        );
        polygon.addTo(mymap);
        @if(isset($polygon['name']) && $polygon['name']!='')
        polygon.bindTooltip("{{ $polygon['name']??'' }}");
        @endif
    @endforeach

    @if($tileHost === 'mapbox')
        let url{{$mapId}} = 'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={{config('maps.mapbox.access_token', null)}}';
    @elseif($tileHost === 'openstreetmap')
        let url{{$mapId}} = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    @else
        let url{{$mapId}} = '{{$tileHost}}';
    @endif
    L.tileLayer(url{{$mapId}}, {
        maxZoom: {{$maxZoomLevel}},
        attribution: '{!! $attribution !!}',
        id: 'mapbox/streets-v11',
        tileSize: 512,
        zoomOffset: -1
    }).addTo(mymap);


setTimeout(function(){ mymap.invalidateSize()}, 100);

</script>
