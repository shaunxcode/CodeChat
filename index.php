<?php

echo dirname(__FILE__); 
die();
	$file = isset($_GET['file']) ? $_GET['file'] : false;
	if(!$file) {
		header('location:index.php?file=' . uniqid());
		die();
	}
?>
<html>
<head>
	<title>Code Chat</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/blueprint/screen.css" type="text/css" media="all" />
	<style>
		ul { margin: 0; padding: 0;}
		li { list-style-type: none; }
		h3 { text-align: left; margin: 0; margin-bottom: 5px;}
		.userList, .environment { background-color: #fff; padding-bottom: 5px; overflow: hidden; }		
		.userList, .environment li { padding: 5px; padding-bottom: 0;} 

		.rounded { 
			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			border-radius: 10px;
			border: 1px solid #333;
		}

		.messages, .message { 
			margin: 0; 
			padding: .5em; 
			font: 1em/1.5 'andale mono','lucida console',monospace;
		}

		.messages { background: #fff; text-align: left; height: 300px; overflow: auto;}
		.message { width: 100%; margin-top: .5em; height: 200px;}

		.center-col { text-align: right; } 
		.center-col button { margin-top: .5em; }
	</style>
	<script>
		var out = function(r) { 
			if(r) 
			{
				CC.messagesView.append((!isNaN(r) ? (r) : (typeof(r)=='string' ? r : r.toSource())) + '\n');
				CC.messagesView.scrollTop(document.getElementById('messagesPre').scrollHeight);
			}
			return r;
		};
		var CC = { 
			file: <?php echo json_encode($file) ?>,
			user: false,
			users: {},
			messages: {},
	    	timestamp: 0,
	    	initvars: {},
		    url: './backend.php?file=<?php echo $file; ?>',
		    noerror: true,
	    
		    connect: function() {
		        $.get(
			    	CC.url,
		        	{timestamp: CC.timestamp},
		        	function(response) {
		          		// handle the server respons

		          		$.each(response.messages, function(i, item){
		          			if(!CC.messages[item.id]) {
		          				if(!CC.users[item.user]) {
		          					CC.users[item.user] = [];
		          					CC.usersView.append($('<li />').text(item.user));
		          				}
		          				CC.users[item.user].push(item.msg);

		          				CC.messages[item.id] = item.msg;
		          				out(item.user + ': ' + item.msg);

		          				try { 
		          				  out(eval(item.msg));
		          				} catch(e) {
		          					out(e.message);
		          				}

		          				for(var i in window) {
		          					if(!CC.initvars[i]) {
		          						CC.environmentView.append($('<li />').text(i)); 
		          						CC.initvars[i] = true;
		          					}
		          				}
		          			}
		          		});

		          		CC.timestamp = response.timestamp;
		          		
		    			//$('.messages').html(response.msg);
		          		CC.noerror = true;
		        	},
		       		'JSON')
		        .complete(function() {
		          	// send a new ajax request when this request is finished
		          if (!CC.noerror) {
		            // if a connection problem occurs, try to reconnect each 5 seconds
		            setTimeout(function(){ CC.connect() }, 5000); 
		          }
		          else {
		            CC.connect();
		          }

		          CC.noerror = false;
		        });
		    },
	  	}

		$(function() {
			if(!CC.user) { 
				CC.user = prompt('username');
				//$.post('setUser.php', {user: CC.user});
			}

			CC.usersView = $('.userList');
			CC.messagesView = $('.messages');
			CC.environmentView = $('.environment');

			$('.sendMessageButton').button().click(function(){
				$.post(CC.url, {
					msg: $('.message').val(),
					user: CC.user
				});
			    $('.message').val('');
			});

			for(var i in window) { 
		    	CC.initvars[i] = true;
		    }

			CC.connect();
		});
	</script>
</head>
<body>
	<div class="container">
		<div class="span-24 first last"><h1>Code Chat</h1></div>
		<div class="first span-5">
			<h3>Users</h3>
			<ul class="userList rounded"></ul>
		</div>
		<div class="span-15 center-col">
			<h3>Transcript</h3>
			<pre id="messagesPre" class="messages rounded"></pre>
			<textarea class="message rounded"></textarea>
			<button class="sendMessageButton">Send</button>
		</div>
		<div class="span-4 last">
			<h3>Environment</h3>
			<ul class="environment rounded"></ul>
		</div>
	</div>
</body>
</html>