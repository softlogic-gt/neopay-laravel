<!DOCTYPE html>
<html>
<body>
<script>
window.parent.postMessage({
  type: 'NEOPAY_CHALLENGE_RESULT',
  result: @json($params)
}, '*');
</script>
</body>
</html>