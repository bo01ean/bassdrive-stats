<?php 
 header('Content-type: text/html; charset=utf-8'); 
?>
<!DOCTYPE html>
<html>
  <head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="http://code.jquery.com/ui/1.8.2/jquery-ui.js"></script>
    <script src="js/Three.js"></script>    
    <script src="js/raf.js"></script>	
	<style type="text/css">
      body {
        margin: 0px;
        padding: 0px;
      }

      #container {
        position:absolute;
        left:0px;
        top:0px;
        width:100%;
        height:100%;
        margin: 0px;
        padding: 0px;
      }
    </style>
  </head>
  <body>
	 <!-- let's parse audio data to animate graph later ...
	<!--audio controls src="http://czech1.serverhostingcenter.streams.bassdrive.com:8200/;"></audio-->
	  <script>
      // <!--	  
      THREE.LeftAlign = 1;
      THREE.CenterAlign = 0;
      THREE.RightAlign = -1;
      THREE.TopAlign = -1;
      THREE.BottomAlign = 1;

      var renderer = new THREE.WebGLRenderer({antialias: true});
      var w = window.innerWidth;
      var h = window.innerHeight;
      renderer.setSize(w, h);
      document.body.appendChild(renderer.domElement);

	var radius = 100;
	var theta = 0;	  
	var meshes = [];
	var meshInc = 0;	
	var gui = {};
		
	var userOpts	= {
		range		: 800,
		duration	: 2500,
		delay		: 200,
		easing		: 'Elastic.EaseInOut',
		theta		: 0
	};

	  
      renderer.setClearColorHex(0xEEEEEE, 1.0);
      renderer.shadowMapEnabled = true;
      renderer.shadowMapWidth = 1024;
      renderer.shadowMapHeight = 1024;
      renderer.shadowCameraFov = 35;

      var camera = new THREE.PerspectiveCamera( 45, w/h, 1, 10000 );
      camera.position.z = 200;
      camera.position.x = -100;
      camera.position.y = 150;

      var scene = new THREE.Scene();

      var light = new THREE.SpotLight();
      light.castShadow = true;
      light.position.set( -170, 300, 100 );
      scene.add(light);

      var ambientLight = new THREE.PointLight(0x442255);
      ambientLight.position.set(20, 150, -120);
      scene.add(ambientLight);

	  
	  
      var plane = new THREE.Mesh( new THREE.CubeGeometry(300, 3, 90),  new THREE.MeshLambertMaterial({color: 0xFFFFFF}));
      plane.position.y = -1;
	  plane.position.x = -30;
      plane.receiveShadow = true;
      plane.doubleSided = true;
      scene.add(plane);
      
	  var barSize = 10;
	  var spacing = 1;
	
	  
	  var getWeek = getParameterByName('week');
	  
	  if( getWeek==undefined ){
		getWeek = 39;
	  }
	  
    var barGraph = new THREE.Object3D();
    scene.add(barGraph);	  
	  
	  // make JSON call to data script
		$.getJSON("getData.php?week=" + getWeek,
			function(data){
			  buildGraph( data );
		})
		.error( buildGraph( null ) );	  
	  

	

	  

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	
function buildGraph( json )
{
	
      var grid = [];
      var week = [];
	  var y = 0;
	  var x = 0;
	  
	  var max = 4;

	if( json == null ){
		json = {"Sun 08\/26\/12":{"00":532.58333333333,"01":645.83333333333,"02":697.83333333333,"03":746.33333333333,"04":791.08333333333,"05":831,"06":864.25,"07":894,"08":921.58333333333,"09":962.91666666667,"10":964.58333333333,"11":939.25,"12":968.41666666667,"13":888.08333333333,"14":788.25,"15":658,"16":575.5,"17":535.08333333333,"18":471.83333333333,"19":429.58333333333,"20":421.33333333333,"21":442.25,"22":456.5,"23":525.41666666667},"Mon 08\/27\/12":{"00":625.58333333333,"01":701.5,"02":766.08333333333,"03":825.16666666667,"04":929.58333333333,"05":960.75,"06":1044,"07":1078.8333333333,"08":1111.0833333333,"09":1055.5,"10":1090.1666666667,"11":1109.3333333333,"12":1036.75,"13":943.5,"14":832.41666666667,"15":734.91666666667,"16":624.08333333333,"17":547.25,"18":515.83333333333,"19":501.5,"20":489.91666666667,"21":485.75,"22":490.66666666667,"23":550.5},"Tue 08\/28\/12":{"00":616.75,"01":768.75,"02":871.41666666667,"03":924.33333333333,"04":917.66666666667,"05":953.75,"06":1046,"07":1117.8333333333,"08":1202.0833333333,"09":1221.5833333333,"10":1230.4166666667,"11":1182.6666666667,"12":1145.75,"13":959.66666666667,"14":804.25,"15":723,"16":670.41666666667,"17":579.91666666667,"18":503.75,"19":461.33333333333,"20":452.33333333333,"21":446.16666666667,"22":478.08333333333,"23":539.91666666667},"Wed 08\/29\/12":{"00":627.33333333333,"01":749.75,"02":882.83333333333,"03":942.83333333333,"04":1010.5,"05":1031.4166666667,"06":1166.9166666667,"07":1183.1666666667,"08":1211.6666666667,"09":1197.0833333333,"10":1219.9166666667,"11":1181.1666666667,"12":1210.3333333333,"13":1103.6666666667,"14":945.25,"15":835.66666666667,"16":724.16666666667,"17":646,"18":594.33333333333,"19":549.91666666667,"20":499.41666666667,"21":489.41666666667,"22":499.66666666667,"23":533.58333333333},"Thu 08\/30\/12":{"00":635.25,"01":763,"02":860,"03":969.66666666667,"04":1030,"05":1071.0833333333,"06":1046,"07":1098.0833333333,"08":1171.8333333333,"09":1171.0833333333,"10":1178.8333333333,"11":1199.6666666667,"12":1172.0833333333,"13":1111,"14":972.75,"15":812.66666666667,"16":682.08333333333,"17":624.58333333333,"18":544.25,"19":497.41666666667,"20":472,"21":472.83333333333,"22":490.25,"23":579.91666666667},"Fri 08\/31\/12":{"00":651,"01":823.91666666667,"02":963.66666666667,"03":995.5,"04":1055.8333333333,"05":1119.3333333333,"06":1195.25,"07":1108,"08":1111.9166666667,"09":1095.1666666667,"10":1090.5,"11":1023.9166666667,"12":1003.4166666667,"13":943.16666666667,"14":867.16666666667,"15":777.75,"16":664.5,"17":582.58333333333,"18":507.83333333333,"19":413,"20":356.66666666667,"21":456,"22":465.83333333333,"23":502.75},"Sat 09\/1\/12":{"00":584.5,"01":673.5,"02":732.91666666667,"03":758.58333333333,"04":823,"05":881.58333333333,"06":920.08333333333,"07":936.41666666667,"08":922.58333333333,"09":898.25,"10":958.16666666667,"11":970.83333333333,"12":923.16666666667,"13":826.91666666667,"14":723.25,"15":647.75,"16":587,"17":541.33333333333,"18":494.41666666667,"19":447.66666666667,"20":419.08333333333,"21":404.66666666667,"22":405.75,"23":447.58333333333}}
	}	  
	  
	  
    $.each(json, function( i, item )
	{	
		if(i != undefined )
		{
		   week.push( i );
		   grid[y] = [];
			for( hour in item )
			{		
			  grid[y][x] = item[hour];
			  x++;
			  max++;
			}
			
			if(grid[y].length != 24)
			{
			  while(grid[y].length < 24)
			  {
			    grid[y].push(0);
			  }
			}
			
		   x = 0;
		   y++;
		}
	});
	
	
	
	
	
	
    var max = grid.reduce(function(s,i)
	{ 
      return Math.max(s, Math.max.apply(null, i)); 
    }, grid[0][0]);
	  

	for (var j=0; j<week.length; j++)
	{// weekdays
		var array = grid[j];
		var title = alignPlane(createText2D( week[j] ), -1, THREE.CenterAlign);
		title.scale.set(0.25, 0.25, 0.25);
		title.position.x = (-1-(array.length-1)/2) * barSize;
		title.position.z = -(j-(grid.length-1)/2) * barSize;
		title.position.y = 1;
		title.rotation.x = -Math.PI/2;
		barGraph.add( title );
	}
	  
	var heading = alignPlane( createText2D( "Bassdrive Stats for week: " + getWeek, "purple", "Georgia", 25, 25, 25), THREE.CenterAlign, THREE.CenterAlign);  
		heading.position.y = -25;
		heading.position.x = -30;
		heading.position.z = 40;
		barGraph.add( heading ); 
	  
	  
	for (var j=0; j<grid[0].length; j++)
	{//hour
		var array = grid[0];
		var title = alignPlane( createText2D( pad( j, 2 ) + ":00" ), THREE.CenterAlign, THREE.CenterAlign);
		title.scale.set(0.15, 0.15, 0.15);
		title.position.x = (j-(array.length-1)/2) * barSize;
		title.position.z = -(-1-(grid.length-1)/2) * barSize;
		title.position.y = 1;// + ( j % 2 == 0) ? 5 : 0;
		title.rotation.x = -Math.PI/2;
		barGraph.add(title);		
	}

	

	
	for (var j=0; j<grid.length; j++)
	{// bars
		var array = grid[j];
		for (var i=0; i<array.length; i++) {
		  var mat = new THREE.MeshLambertMaterial({color: 0xFFAA55, opacity: .89 });
		  var barHeight = array[i]/max * 80;
		  mat.color.setHSV(0.2 + 0.8*array[i]/max, 0.8, 1);
		  var geo = new THREE.CubeGeometry( barSize - spacing, barHeight, barSize - spacing );
		  var mesh = new THREE.Mesh(geo, mat);
		 
		  
		  
		  //mesh.grow();// grow a certain percentage		  
		  mesh.position.x = (i-(array.length-1)/2) * barSize;
		  mesh.position.y = barHeight/2;
		  mesh.position.z = -(j-(grid.length-1)/2) * barSize;

		  
		  meshes[meshInc] = mesh;
		  
		  mesh.castShadow = mesh.receiveShadow = true;
		  barGraph.add( mesh );
		  
		  
		  
		  mesh.scale.y = 1;		  
		  //meshes[meshInc].tag = setInterval( function( meshInc ){ meshes[meshInc].grow();}, 30 ); 

		  
		  meshInc++;
		  
		  
		}
	}
}


// add member function will not work, must use shader.
THREE.Mesh.prototype.grow = function()
{

	
		if( this.scale.y == 1)
		{
			clearInterval( this.tag )
			return;				
		}

	this.scale.y += .01;
			
	
}
		  
		  






      renderer.render(scene, camera);
      var paused = false;
      var last = new Date().getTime();
      var down = false;
      var sx = 0, sy = 0;
      var rot = Math.PI/3;
	  
	  var coef = 300;//140
	  
	  
      camera.position.x = Math.cos(rot)*coef;
      camera.position.z = Math.sin(rot)*coef;
      
	  
	  
	  window.onmousedown = function (ev){
        down = true; sx = ev.clientX; sy = ev.clientY;
      };
	  
	  
	  
      window.onmouseup = function(){ down = false; };
      
	  
	  window.onmousemove = function(ev) {
        if (down) {
          var dx = ev.clientX - sx;
          var dy = ev.clientY - sy;
          rot += dx*0.01;
          camera.position.x = Math.cos(rot)*coef;
          camera.position.z = Math.sin(rot)*coef;
          camera.position.y = Math.max(5, camera.position.y+dy);
          sx += dx;
          sy += dy;
        }
      }
	  
	  
	  
	  
	  
	  
	  
	  
	  





	
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
      function createTextCanvas(text, color, font, size) {
        size = size || 24;
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var fontStr = (size + 'px ') + (font || 'Arial');
        ctx.font = fontStr;
        var w = ctx.measureText(text).width;
        var h = Math.ceil(size*1.25);
        canvas.width = w;
        canvas.height = h;
        ctx.font = fontStr;
        ctx.fillStyle = color || 'black';
        ctx.fillText(text, 0, size);
        return canvas;
      }

      function createText2D(text, color, font, size, segW, segH) {
        var canvas = createTextCanvas(text, color, font, size);
        var plane = new THREE.PlaneGeometry(canvas.width, canvas.height, segW, segH);
        var tex = new THREE.Texture(canvas);
        tex.needsUpdate = true;
        var planeMat = new THREE.MeshBasicMaterial({
          map:tex, color: 0xffffff, transparent:true
        });
        var mesh = new THREE.Mesh(plane, planeMat);
        mesh.doubleSided = true;
        return mesh;
      }


      function alignPlane(plane, horizontalAlign, verticalAlign) {
        var obj = new THREE.Object3D();
        var u = plane.geometry.vertices[0].position;
        var v = plane.geometry.vertices[plane.geometry.vertices.length-1].position;
        var width = Math.abs(u.x - v.x);
        var height = Math.abs(u.y - v.y);
        plane.position.x = (width/2) * horizontalAlign;
        plane.position.y = (height/2) * verticalAlign;
        obj.add(plane);
        return obj;
      }


	  
      function animate(t) {
        if (!paused) {
          last = t;
          renderer.clear();
          camera.lookAt(scene.position);
          renderer.render(scene, camera);
        }
        window.requestAnimationFrame(animate, renderer.domElement);
		
		if(!down)
		{
			render();
		}else{
			//theta = 0;
		}
      };
	  
	  
	  
      animate(new Date().getTime());
      onmessage = function(ev) {
        paused = (ev.data == 'pause');
      };

	
	function render()
	{

		userOpts.theta += 0.5;
		//camera.position.x = radius * Math.sin( userOpts.theta * Math.PI / 360 );
		//camera.position.y = radius * Math.sin( userOpts.theta * Math.PI / 360 );
		camera.position.z = radius * Math.cos( userOpts.theta * Math.PI / 360 );
		camera.lookAt( scene.position );
		renderer.render( scene, camera );
	}
	
	function pad(number, length) {   
		var str = '' + number;
		while (str.length < length) {
			str = '0' + str;
		}	   
		return str;
	}
	
	
	// http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values  
	function getParameterByName(name)
	{
	  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
	  var regexS = "[\\?&]" + name + "=([^&#]*)";
	  var regex = new RegExp(regexS);
	  var results = regex.exec(window.location.search);
	  if(results == null)
		return "";
	  else
		return decodeURIComponent(results[1].replace(/\+/g, " "));
	}
      // -->
    </script>
  </body>
</html>