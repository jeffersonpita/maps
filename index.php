<?
$type = 1;

$max = 0;
if(isset($_GET['t']) && $_GET['t']!=null)
  $type = $_GET['t']*1;

$color = "green";

if($type==1){
  $filename = "data/expenditureEducationExp.json";
  $title = "% of Public Expenses in Education";
  $max = 15;
  $source = "http://data.un.org/_Docs/SYB/PDFs/SYB62_T07_Education.pdf";

} else if($type==2){
  $filename = "data/expenditureEducationGDP.json";
  $title = "% of GDP in Public Expenses in Education";
  $max = 5;
  $color = "purple";
  $source = "http://data.un.org/_Docs/SYB/PDFs/SYB62_T07_Education.pdf";
}
else if($type==3){
  $filename = "data/activefires.json";
  $title = "Active Fires 2019-08-24";
  $source = "https://firms.modaps.eosdis.nasa.gov/active_fire/viirs/text/VNP14IMGTDL_NRT_Global_24h.csv";
}

?>
<html>
  <head>
    <title>Maps</title>
    <meta charset="UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <style type="text/css">
        body {
            color: #5d5d5d;
            font-family: Helvetica, Arial, sans-serif;
        }

        h1 {
            font-size: 30px;
            margin: auto;
            margin-top: 50px;
        }

        .mapcontainer {
            mmax-width: 800px;
            mmargin: auto;
            width: 98%;
            position: relative;
        }

        /* Specific mapael css class are below
         * 'mapael' class is added by plugin
        */

        .mapael .map {
            position: relative;
        }

        .mapael .mapTooltip {
            position: absolute;
            background-color: #fff;
            moz-opacity: 0.70;
            opacity: 0.70;
            filter: alpha(opacity=70);
            border-radius: 10px;
            padding: 10px;
            z-index: 1000;
            max-width: 200px;
            display: none;
            color: #343434;
        }
        .areaLegend {
          position: absolute;
          bottom:0px;
        }
        .mappoint {
          fill: red;
        }
    </style>
  </head>

  </head>
  <body>
    <select id="seltype" onchange="document.location='?t='+this.value;">
      <option value="1">% of Public Expenses in Education</option>
      <option value="2">% of GDP in Public Expenses in Education</option>
      <option value="3">Active Fires 2019-08-24</option>
    </select>
    <div class="mapcontainer">
        <div class="map">
            <span>&nbsp;</span>
        </div>
        <div class="areaLegend">
            <span>&nbsp;</span>
        </div>
    </div>
    <br>
    <div class="source"><a href="<?=$source?>" target="new"><?=$source?></a><br>jeffersonpita@yahoo.com.br</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"> </script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mapael/2.2.0/js/jquery.mapael.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mapael/2.2.0/js/maps/world_countries_miller.min.js"></script>
<script src="countries.js"></script>
<script type="text/javascript">
$('#seltype').val("<?=$type?>");
/*
zoom: {
 enabled: true,
 touch: true
},,
maxLevel: 450,
"animDuration": 1000,
init: {
  level: 10,
  latitude: 48.76,
  longitude: -1.6
}*/
var objmap = {
    map : {
        name : "world_countries_miller",

        defaultArea:{
          attrs:{
            fill: "#DDDDDD"
          }
        }
    },
    legend: {
      area: {
        title: "<?=$title?>",
      }
    },
    areas: {
    }
};
var colors = [ "#EEFFBA", "#D6FA8C", "#BEED53", "#A5D721", "#82B300", "#5D8700"];
if("<?=$color?>"=="purple"){
  colors = ["#E1BEE7", "#CE93D8", "#BA68C8", "#AB47BC", "#9C27B0", "#7B1FA2"];
}

function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
  var R = 6371; // Radius of the earth in km
  var dLat = deg2rad(lat2-lat1);  // deg2rad below
  var dLon = deg2rad(lon2-lon1);
  var a =
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
    Math.sin(dLon/2) * Math.sin(dLon/2)
    ;
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  var d = R * c; // Distance in km
  return d;
}

function deg2rad(deg) {
  return deg * (Math.PI/180)
}

function drawPointsMap( data ){
  //console.log(data);
  let points = [];
  for(var i in data.latitude)
    if(data.frp[i]>100){
    let add = true;
    for(var j in points)
      if(getDistanceFromLatLonInKm(points[j].latitude, points[j].longitude, data.latitude[i], data.longitude[i])<30)
      {
        points[j].count += data.frp[i];
        add = false;
      }
    if(add)
      points.push({ latitude: data.latitude[i], longitude: data.longitude[i], count: data.frp[i]});
  }

  var max = 0;
  var min = 0;
  for(var i in points){
    if(points[i].count>max)
      max = points[i].count;
    if(points[i].count || min==0)
      min = points[i].count;
  }
  var coef = max/10;

  let plots = [];
  var ct = 0;
  for(var i in points){
    ct++;
    plots[i] = {
      latitude: points[i].latitude,
      longitude: points[i].longitude,
      cssClass: 'mappoint',
      size: Math.floor(points[i].count/coef)+5
    };
  }

  objmap.plots = plots;
  objmap.map.zoom = {
   enabled: true,
   touch: true
  }
  $(".mapcontainer").mapael(objmap);
}

function drawColoredMap( data ){

  var areas = {};
  var min = 0;
  var max = 0;
  for(var i in data['Country']){
    var val = (data['%'][i]*1);
    var arr = { value: val, href:'', tooltip:{content: "<span style=\"font-weight:bold;\">"+data['Country'][i]+"</span><br />"+val+'%'} };
    areas[getCountryCode(data['Country'][i])] = arr;

    if(max==0 || val>max)
      max = val;
    if(min==0 || val<min)
      min = val;
  }

  max -= "<?=$max?>";
  var sl = (max-min)/colors.length;
  var y = min;
  var slices = [];
  for(var i=0;i<colors.length; i++){
    var slice = { attrs:{fill: colors[i]} };
    slice.min = y;
    slice.max = y+sl;
    slice.label = 'Between '+Math.round(slice.min)+'% and '+Math.round(slice.max)+'%';
    if(i==0){
      delete slice['min'];
      slice.label = 'More than '+Math.round(slice.max)+'%';
    }
    if(i==colors.length-1){
      delete slice['max'];
      slice.label = 'At least '+Math.round(slice.min)+'%';
    }

    slices.push(slice);
    y+=sl;
  }

  objmap.legend.area.slices = slices;
  objmap.areas = areas;
  $(".mapcontainer").mapael(objmap);
}

$.ajax({
  type: "GET",
  url: "<?=$filename?>",
  dataType: "text",
  success: function(response)
  {
      let data = JSON.parse(response);
      let t = "<?=$type?>";
      if(t==1 || t==2)
    	 drawColoredMap( data );
      else if(t==3)
        drawPointsMap( data );
  }
});
</script>
  </body>
</html>
