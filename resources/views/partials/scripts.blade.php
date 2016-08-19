<script>

    function noticeOff ( event ){
        $.post('/notified', {'event': event, '_token': '{{ csrf_token() }}'});
    }

</script>