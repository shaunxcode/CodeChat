<?php
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
	<script type="text/javascript" src="highlight.js"></script>
	<script type="text/javascript" src="javascript.js"></script>


	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/blueprint/screen.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/sunburst.css" type="text/css" media="all" />
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

		pre { background-color: #000; color: #fff;}
		pre i { padding: 0.5em; }
		pre strong { float: left; display: block; padding: 0.5em; padding-right: 0;}
		pre code { float: left; }

		.messages {text-align: left; height: 300px; overflow: auto;}
		.message { width: 100%; margin-top: .5em; height: 200px;}

		.center-col { text-align: right; } 
		.center-col button { margin-top: .5em; }
	</style>
	<script>
		var out = function(r) { 
			if(r) 
			{
				CC.messagesView.append($('<i />').text(!isNaN(r) || typeof(r)=='string' ? r : r.toSource()));
				CC.messagesView.scrollTop(document.getElementById('transcript').scrollHeight);
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

		          				CC.messagesView.append(
			          				$('<div />')
			          					.append($('<strong />').text(item.user + ':'))
			          					.append($('<code />').text(item.msg).addClass('javascript').attr('id', 'msg-' + item.id))
			          					.append($('<div />').css('clear', 'both')));
		          				
		          				hljs.highlightBlock(document.getElementById('msg-' + item.id), '    ');

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
		        	},
		       		'JSON')
		        .complete(function() {
		          	// send a new ajax request when this request is finished
		            setTimeout(function(){ CC.connect() }, 1000); 
		        });
		    },

		    sendMessage: function(){
				$.post(CC.url, {
					msg: $('.message').val(),
					user: CC.user
				});
			    $('.message').val('');
			}, 

			historyPrev: function() {
				
			},

			historyNext: function() {
				
			},

		    messageHandlers: {
		    	38: 'historyPrev', //up
		    	40: 'historyNext', //down
		    	13: 'sendMessage'   //enter
		    }
	  	}

		$(function() {
			if(!CC.user) { 
				CC.user = prompt('username');
			}

			CC.usersView = $('.userList');
			CC.messagesView = $('#transcript');
			CC.environmentView = $('.environment');

			$('.message').keypress(function(e) {
				if(e.ctrlKey && CC.messageHandlers[e.keyCode]) {
					CC[CC.messageHandlers[e.keyCode]]();
				}
			});

			$('.sendMessageButton').button().click(CC.sendMessage);

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
			<pre id="transcript" class="messages rounded"></pre>
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