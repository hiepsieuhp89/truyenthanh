<script data-exec-on-popstate>
$(function () {
	@foreach($script as $s) {!! $s !!} @endforeach
	$('#lg_selection').on('change',function(){
		let lang = $(this).val();
		$.ajax({
			method : 'post',
			url : '{{	route('admin-change-language')	}}',
			data : {
				'_token' : '{{ csrf_token() }}',
				'lang' : lang,
			},
			success: function(res){
				location.reload(true);
			}
		});
	})
});
</script>
