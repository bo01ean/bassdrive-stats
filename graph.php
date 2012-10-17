<?php 
 header('Content-type: text/html; charset=utf-8'); 
?>
<!DOCTYPE html>
<html>
  <head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="http://code.jquery.com/ui/1.8.2/jquery-ui.js"></script>
    <script src="js/Three.js"></script>    
    <script src="js/datGui.js"></script>    
    <script src="js/tween.js"></script>    
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
	

	//var gui = new DAT.GUI({ height	: 4 * 32 - 1 });	
	
	var userOpts	= {
		range		: 800,
		duration	: 2500,
		delay		: 200,
		easing		: 'Elastic.EaseInOut',
		theta		: 0
	};
	
	
	// build the GUI 
	buildGui(userOpts, function(){
		console.log( "userOpts", userOpts )
		//setupTween();
		render();
	});
	

	function setupTween()
	{
		// 
		var update	= function(){
			cube.position.x = current.x;
		}
		var current	= { x: -userOpts.range };

		// remove previous tweens if needed
		TWEEN.removeAll();
		
		// convert the string from dat-gui into tween.js functions 
		var easing	= TWEEN.Easing[userOpts.easing.split('.')[0]][userOpts.easing.split('.')[1]];
		// build the tween to go ahead
		var tweenHead	= new TWEEN.Tween(current)
			.to({x: +userOpts.range}, userOpts.duration)
			.delay(userOpts.delay)
			.easing(easing)
			.onUpdate(update);
		// build the tween to go backward
		var tweenBack	= new TWEEN.Tween(current)
			.to({x: -userOpts.range}, userOpts.duration)
			.delay(userOpts.delay)
			.easing(easing)
			.onUpdate(update);

		// after tweenHead do tweenBack
		tweenHead.chain(tweenBack);
		// after tweenBack do tweenHead, so it is cycling
		tweenBack.chain(tweenHead);

		// start the first
		tweenHead.start();
	}

	

// # Build gui with dat.gui
function buildGui(options, callback)
{
	// collect all available easing in TWEEN library
	var easings	= {};
	Object.keys(TWEEN.Easing).forEach(function(family){
		Object.keys(TWEEN.Easing[family]).forEach(function(direction){
			var name	= family+'.'+direction;
			easings[name]	= name;
		});
	});
	// the callback notified on UI change
	var change	= function(){
		callback(options)
	}
	// create and initialize the UI
	var gui = new DAT.GUI({ height	: 4 * 32 - 1 });
	gui.add(options, 'theta').name('Theta').min(0).max(1)	.onChange(change);
	gui.add(options, 'duration').name('Duration (ms)').min(100).max(4000)	.onChange(change);
	gui.add(options, 'delay').name('Delay (ms)').min(0).max(1000)		.onChange(change);
	gui.add(options, 'easing').name('Easing Curve').options(easings)	.onChange(change);
}
	
	
	
	
	  
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
	  
	  // make JSON call to data script
		$.getJSON("getData.php?week=" + getWeek,
			function(data){
			  buildGraph( data );
		});	  
	  
    var barGraph = new THREE.Object3D();
    scene.add(barGraph);
	

	  

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
function buildGraph( json )
{
	
      var grid = [];
      var week = [];
	  var y = 0;
	  var x = 0;
	  
	  var max = 4;
	  
	  
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