<?
$type = 1;

$max = 0;
if(isset($_GET['t']) && $_GET['t']!=null)
  $type = $_GET['t']*1;

$color = "green";

if($type==1){
  $filename = "expenditureEducationExp.json";
  $title = "% of Public Expenses in Education";
  $max = 15;
  $source = "http://data.un.org/_Docs/SYB/PDFs/SYB62_T07_Education.pdf";

} else if($type==2){
  $filename = "expenditureEducationGDP.json";
  $title = "% of GDP in Public Expenses in Education";
  $max = 5;
  $color = "purple";
  $source = "http://data.un.org/_Docs/SYB/PDFs/SYB62_T07_Education.pdf";
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
    </style>
  </head>

  <body>
    <select id="seltype" onchange="document.location='?t='+this.value;">
      <option value="1">% of Public Expenses in Education</option>
      <option value="2">% of GDP in Public Expenses in Education</option>
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

$.ajax({
  type: "GET",
  url: "<?=$filename?>",
  dataType: "text",
  success: function(response)
  {
    	var data = JSON.parse(response);
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
        if(getCountryCode(data['Country'][i])==data['Country'][i])
          console.log(getCountryCode(data['Country'][i]));
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
});
</script>
  </body>
</html>
